const express = require("express");
const router = express.Router();
// const { drugSuggestionApi } = require("../controller/localAI");
const { userController } = require("../controller/user");
const { drugSuggestionApi } = require("../controller/gemini");

// Health check endpoint
router.get("/health", (req, res) => {
  res.json({
    status: "AI Service is running",
    timestamp: new Date().toISOString(),
    message: "Sky Pharmacy AI Service is operational",
  });
});

// AI suggestion endpoint - main route for drug recommendations
router.post("/suggest", async (req, res) => {
  try {
    const { input, symptoms } = req.body;
    const query = input || symptoms;

    if (!query || query.trim() === "") {
      return res.status(400).json({
        success: false,
        error: "Input text or symptoms are required",
      });
    }

    // Call the drug suggestion API function
    await drugSuggestionApi({ body: { input: query } }, res);
    return;
  } catch (error) {
    console.error("Error in AI suggestion:", error);
    res.status(500).json({
      success: false,
      error: "Internal server error",
      message: error.message,
    });
  }
});

// AI recommendation endpoint - legacy route for backward compatibility
router.post("/api/ai/recommend", async (req, res) => {
  try {
    const { input } = req.body;

    if (!input || input.trim() === "") {
      return res.status(400).json({
        success: false,
        error: "Input text is required",
      });
    }

    // Call the drug suggestion API function
    await drugSuggestionApi({ body: { input: input } }, res);
    return;
  } catch (error) {
    console.error("Error in AI recommendation:", error);
    res.status(500).json({
      success: false,
      error: "Internal server error",
      message: error.message,
    });
  }
});

// Drug search endpoint
router.get("/search", async (req, res) => {
  try {
    const { q, category } = req.query;

    if (!q || q.trim() === "") {
      return res.status(400).json({
        success: false,
        error: "Search query is required",
      });
    }

    // For now, return a simple response - you can expand this later
    res.json({
      success: true,
      query: q,
      category: category,
      results: [],
      message: "Drug search functionality coming soon",
    });
  } catch (error) {
    console.error("Error in drug search:", error);
    res.status(500).json({
      success: false,
      error: "Internal server error",
      message: error.message,
    });
  }
});

// Keep existing routes
router.get("/test", drugSuggestionApi);

module.exports = router;
