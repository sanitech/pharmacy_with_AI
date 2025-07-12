// Load environment variables
require("dotenv").config();

const express = require("express");
const cors = require("cors");
const app = express();

const userRouter = require("./routes/appRoute");

const PORT = process.env.PORT || 3000;

// CORS configuration for localhost
const corsOptions = {
  origin: [
    'http://localhost',
  ],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
};

// Middleware
app.use(cors(corsOptions));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Request logging middleware
app.use((req, res, next) => {
  console.log(`${new Date().toISOString()} - ${req.method} ${req.path}`);
  next();
});

app.use("/", userRouter);

// Global error handler
app.use((err, req, res, next) => {
  console.error('Error:', err.stack);
  res.status(500).json({
    success: false,
    error: 'Internal server error',
    message: err.message
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    error: 'Route not found',
    message: `The route ${req.method} ${req.path} does not exist`
  });
});

app.listen(PORT, () => {
  console.log(`🚀 Sky Pharmacy AI Service running on http://localhost:${PORT}`);
  console.log(`📊 Health check: http://localhost:${PORT}/health`);
  console.log(`🤖 AI Suggestions: http://localhost:${PORT}/suggest`);
  console.log(`🔍 Drug Search: http://localhost:${PORT}/search`);
  console.log(`⏰ Started at: ${new Date().toISOString()}`);
});
