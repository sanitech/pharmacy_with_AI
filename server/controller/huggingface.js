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

  const prompt = `A patient has the following symptoms: "${symptoms}". Suggest medicines or treatments. For each suggestion, include:
1) Drug Name
2) Dosage
3) When and how to take
4) Precautions
5) Common Side Effects.
Format the response in a clean and readable list.`;

  // Try Hugging Face API (completely free)
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout for free API

    const response = await fetch(
      `https://api-inference.huggingface.co/models/facebook/opt-350m`,
      {
        method: "POST",
        headers: { 
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          inputs: prompt,
          parameters: {
            max_length: 300,
            temperature: 0.7,
            do_sample: true,
            return_full_text: false
          }
        }),
        signal: controller.signal,
      }
    );

    clearTimeout(timeoutId);

    if (!response.ok) {
      const errorData = await response.text();
      console.error("Hugging Face API HTTP Error:", response.status, errorData);
      throw new Error(`Hugging Face API HTTP Error: ${response.status}`);
    }

    const data = await response.json();
    
    // Debug: Log the response structure
    console.log("Hugging Face API Response:", JSON.stringify(data, null, 2));

    // Extract text from Hugging Face response
    let aiText = "";
    if (Array.isArray(data)) {
      aiText = data[0]?.generated_text || data[0]?.text || "";
    } else if (typeof data === 'string') {
      aiText = data;
    } else {
      aiText = data?.generated_text || data?.text || "";
    }

    if (!aiText || aiText.trim() === "") {
      console.error("Invalid response structure:", data);
      throw new Error("Hugging Face API returned invalid format - no text found");
    }

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "huggingface-api",
    });
  } catch (error) {
    console.error("Hugging Face API failed:", error.message);
    
    return res.status(500).json({
      success: false,
      error: "AI service unavailable",
      message: "Unable to get drug suggestions at this time. Please try again later.",
      details: error.message
    });
  }
};

module.exports = { drugSuggestionApi }; 