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

  // Try Ollama API (completely free, runs locally)
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout for local model

    const response = await fetch(
      `http://localhost:11434/api/generate`,
      {
        method: "POST",
        headers: { 
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          model: "llama2",
          prompt: prompt,
          stream: false,
          options: {
            temperature: 0.7,
            top_p: 0.9,
            max_tokens: 500
          }
        }),
        signal: controller.signal,
      }
    );

    clearTimeout(timeoutId);

    if (!response.ok) {
      const errorData = await response.text();
      console.error("Ollama API HTTP Error:", response.status, errorData);
      throw new Error(`Ollama API HTTP Error: ${response.status}`);
    }

    const data = await response.json();
    
    // Debug: Log the response structure
    console.log("Ollama API Response:", JSON.stringify(data, null, 2));

    const aiText = data?.response || null;

    if (!aiText) {
      console.error("Invalid response structure:", data);
      throw new Error("Ollama API returned invalid format - no text found");
    }

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "ollama-local",
    });
  } catch (error) {
    console.error("Ollama API failed:", error.message);
    
    // If Ollama is not running, provide instructions
    if (error.message.includes("ECONNREFUSED") || error.message.includes("fetch failed")) {
      return res.status(500).json({
        success: false,
        error: "Ollama not running",
        message: "To use free local AI, please install and run Ollama first.",
        instructions: {
          install: "1. Download Ollama from https://ollama.ai/",
          setup: "2. Install and run: ollama serve",
          model: "3. Download model: ollama pull llama2",
          restart: "4. Restart this server"
        }
      });
    }
    
    return res.status(500).json({
      success: false,
      error: "AI service unavailable",
      message: "Unable to get drug suggestions at this time. Please try again later.",
      details: error.message
    });
  }
};

module.exports = { drugSuggestionApi }; 