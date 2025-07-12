# Test Server

A Node.js Express server with OpenAI AI integration for drug suggestions based on symptoms. Uses AI-powered recommendations only.

## Setup

1. Install dependencies:

```bash
npm install
```

2. Create a `.env` file in the root directory with your OpenAI API key:

```env
OPENAI_API_KEY=your_openai_api_key_here
PORT=3000
```

3. Get your OpenAI API key from [OpenAI Platform](https://platform.openai.com/api-keys)

## Running the Server

```bash
npm start
```

The server will run on `http://https://pharmacy-with-ai.onrender.com`

## API Endpoints

### GET /test

Get drug suggestions based on symptoms.

**Query Parameters:**

- `symptoms` (required): The symptoms to get drug suggestions for

**Example:**

```
GET /test?symptoms=headache
```

**Response:**

```json
{
  "success": true,
  "symptoms": "headache",
  "suggestions": "Drug suggestions for headache:\n\n1. Acetaminophen (Tylenol)\n   Dosage: 500-1000mg every 4-6 hours\n   How to take: Take with or without food...",
        "source": "openai-api"
}
```

### GET /health

Check if the server is running.

**Response:**

```json
{
  "status": "Server is running",
  "message": "Use /test?symptoms=your_symptoms to get drug suggestions"
}
```

### GET /user/register

User registration endpoint (placeholder).

## Features

### ✅ **AI-Powered Drug Suggestions**

- Uses OpenAI GPT-3.5-turbo for intelligent drug recommendations
- Provides detailed drug information including: name, dosage, instructions, precautions, and side effects
- Real-time AI analysis of symptoms

### ✅ **Robust Error Handling**

- Connection timeout handling (15-second timeout)
- Comprehensive error messages
- Proper HTTP status codes for API failures

### ✅ **Detailed Drug Information**

- Drug names and common dosages
- How to take medications (with/without food, timing)
- Important precautions
- Common side effects
- Medical disclaimers

## AI-Powered Analysis

The system can analyze any symptoms and provide intelligent drug recommendations using OpenAI's GPT-3.5-turbo model. No predefined symptom database - the AI understands and responds to any medical symptoms you provide.

## Error Handling

The API includes comprehensive error handling for:

- Missing API key
- Missing symptoms parameter
- Network timeouts and connection errors
- OpenAI API errors
- Invalid response formats
- Proper error responses with status codes

## Dependencies

- express: Web framework
- cors: Cross-origin resource sharing
- dotenv: Environment variable management
- nodemon: Development server with auto-restart

## Testing

Test the API with these examples:

```
GET /test?symptoms=headache
GET /test?symptoms=fever
GET /test?symptoms=cough
GET /health
```
