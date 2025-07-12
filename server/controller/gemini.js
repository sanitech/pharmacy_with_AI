require("dotenv").config();
const axios = require("axios");

const drugSuggestionApi = async (req, res) => {
  // Handle both query parameters and body parameters
  let symptoms = "";
  
  // Check if req.query exists and has symptoms
  if (req.query && req.query.symptoms) {
    symptoms = decodeURIComponent(req.query.symptoms).trim();
  } 
  // Check if req.body exists and has input or symptoms
  else if (req.body) {
    if (req.body.input) {
      symptoms = req.body.input.trim();
    } else if (req.body.symptoms) {
      symptoms = req.body.symptoms.trim();
    }
  }

  if (!symptoms) {
    return res.status(400).json({
      success: false,
      message: "Symptoms parameter is required (query or body).",
    });
  }

  const apiKey = process.env.API_KEY;
  if (!apiKey) {
    return res.status(500).json({ success: false, message: "Missing API_KEY in environment." });
  }

  const prompt = `Symptoms include cough, sore throat, and mild ${symptoms}. Suggest medications or remedies with:
💊 Drug name  
💡 Dosage  
📝 How to take  
⚠️ Warnings  
🔴 Side effects

Format the response in a clean and readable list with emojis.`;

  // Try Gemini API
  try {
    const response = await axios.post(
      `https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=${apiKey}`,
      {
        contents: [
          {
            parts: [{ text: prompt }],
          },
        ],
      },
      {
        headers: { "Content-Type": "application/json" },
        timeout: 15000, // 15 second timeout
      }
    );

    const data = response.data;
    
    // Debug: Log the response structure
    console.log("Gemini API Response:", JSON.stringify(data, null, 2));

    // Check for API errors
    if (data.error) {
      throw new Error(`Gemini API Error: ${data.error.message || 'Unknown error'}`);
    }

    const aiText =
      data?.candidates?.[0]?.content?.parts?.[0]?.text || null;

    if (!aiText) {
      console.error("Invalid response structure:", data);
      throw new Error("Gemini API returned invalid format - no text found");
    }

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "gemini-api",
    });
  } catch (error) {
    console.warn("Gemini API failed. Using fallback:", error.message);

    const fallbackText = getManualSuggestion(symptoms);

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: fallbackText,
      source: "fallback",
    });
  }
};

// Manual fallback data
function getManualSuggestion(symptoms) {
  const s = symptoms.toLowerCase();

  const db = {
    headache: [
      {
        name: "Paracetamol",
        dosage: "500mg every 6 hours",
        how: "Take after meals with water",
        precautions: "Avoid alcohol or liver issues",
        sideEffects: "Nausea, rash (rare)",
      },
      {
        name: "Ibuprofen",
        dosage: "200mg every 8 hours",
        how: "Take with food to protect stomach",
        precautions: "Avoid ulcers or kidney problems",
        sideEffects: "Stomach pain, dizziness",
      },
    ],
    fever: [
      {
        name: "Paracetamol",
        dosage: "500mg every 4-6 hours",
        how: "Take with water, rest well",
        precautions: "Liver patients avoid high doses",
        sideEffects: "Skin rash (rare)",
      },
    ],
    cough: [
      {
        name: "Dextromethorphan",
        dosage: "30mg every 6-8 hours",
        how: "Take with or without food",
        precautions: "Avoid in asthma",
        sideEffects: "Sleepiness, dizziness",
      },
    ],
  };

  let text = "";
  for (let key in db) {
    if (s.includes(key)) {
      text += `Drug suggestions for "${symptoms}":\n\n`;
      db[key].forEach((d, i) => {
        text += `${i + 1}. ${d.name}\n`;
        text += `   Dosage: ${d.dosage}\n`;
        text += `   How to take: ${d.how}\n`;
        text += `   Precautions: ${d.precautions}\n`;
        text += `   Side Effects: ${d.sideEffects}\n\n`;
      });
      text += "⚠️ Always consult a doctor before taking medicine.";
      return text;
    }
  }

  return `No specific match found for "${symptoms}". You may try:
1. Paracetamol 500mg every 6 hours
2. Ibuprofen 200mg with food

⚠️ Please consult a healthcare provider.`;
}

module.exports = { drugSuggestionApi };
