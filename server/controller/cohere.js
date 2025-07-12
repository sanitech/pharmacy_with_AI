require("dotenv").config();
const fetch = global.fetch || require("node-fetch");

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

  const apiKey = process.env.COHERE_API_KEY;
  if (!apiKey) {
    return res.status(500).json({ success: false, message: "Missing COHERE_API_KEY in environment." });
  }

  const prompt = `A patient has the following symptoms: "${symptoms}". Suggest medicines or treatments. For each suggestion, include:
1) Drug Name
2) Dosage
3) When and how to take
4) Precautions
5) Common Side Effects.
Format the response in a clean and readable list.`;

  // Try Cohere AI API
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout

    const response = await fetch(
      `https://api.cohere.ai/v1/generate`,
      {
        method: "POST",
        headers: { 
          "Content-Type": "application/json",
          "Authorization": `Bearer ${apiKey}`
        },
        body: JSON.stringify({
          model: "command",
          prompt: prompt,
          max_tokens: 500,
          temperature: 0.7,
          k: 0,
          stop_sequences: [],
          return_likelihoods: "NONE"
        }),
        signal: controller.signal,
      }
    );

    clearTimeout(timeoutId);

    if (!response.ok) {
      const errorData = await response.text();
      console.error("Cohere API HTTP Error:", response.status, errorData);
      throw new Error(`Cohere API HTTP Error: ${response.status}`);
    }

    const data = await response.json();
    
    // Debug: Log the response structure
    console.log("Cohere API Response:", JSON.stringify(data, null, 2));

    // Check for API errors
    if (data.error) {
      throw new Error(`Cohere API Error: ${data.error.message || 'Unknown error'}`);
    }

    const aiText = data?.generations?.[0]?.text || null;

    if (!aiText) {
      console.error("Invalid response structure:", data);
      throw new Error("Cohere API returned invalid format - no text found");
    }

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "cohere-api",
    });
  } catch (error) {
    console.error("Cohere API failed:", error.message);
    
    return res.status(500).json({
      success: false,
      error: "AI service unavailable",
      message: "Unable to get drug suggestions at this time. Please try again later.",
      details: error.message
    });
  }
};

module.exports = { drugSuggestionApi }; 