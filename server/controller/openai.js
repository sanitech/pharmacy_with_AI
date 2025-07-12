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

  const apiKey = process.env.OPENAI_API_KEY;
  if (!apiKey) {
    return res.status(500).json({ success: false, message: "Missing OPENAI_API_KEY in environment." });
  }

  const prompt = `A patient has the following symptoms: "${symptoms}". Suggest medicines or treatments. For each suggestion, include:
1) Drug Name
2) Dosage
3) When and how to take
4) Precautions
5) Common Side Effects.
Format the response in a clean and readable list.`;

  // Try OpenAI API
  try {
    const response = await axios.post(
      `https://api.openai.com/v1/chat/completions`,
      {
        model: "gpt-3.5-turbo",
        messages: [
          {
            role: "user",
            content: prompt
          }
        ],
        max_tokens: 500,
        temperature: 0.7
      },
      {
        headers: { 
          "Content-Type": "application/json",
          "Authorization": `Bearer ${apiKey}`
        },
        timeout: 15000, // 15 second timeout
      }
    );

    const data = response.data;
    
    // Debug: Log the response structure
    console.log("OpenAI API Response:", JSON.stringify(data, null, 2));

    // Check for API errors
    if (data.error) {
      throw new Error(`OpenAI API Error: ${data.error.message || 'Unknown error'}`);
    }

    const aiText = data?.choices?.[0]?.message?.content || null;

    if (!aiText) {
      console.error("Invalid response structure:", data);
      throw new Error("OpenAI API returned invalid format - no text found");
    }

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "openai-api",
    });
  } catch (error) {
    console.error("OpenAI API failed:", error.message);
    
    return res.status(500).json({
      success: false,
      error: "AI service unavailable",
      message: "Unable to get drug suggestions at this time. Please try again later.",
      details: error.message
    });
  }
};

module.exports = { drugSuggestionApi }; 