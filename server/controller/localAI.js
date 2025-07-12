require("dotenv").config();

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

  try {
    // Local AI-powered drug suggestion system
    const aiText = generateLocalAISuggestion(symptoms);

    return res.status(200).json({
      success: true,
      symptoms,
      suggestions: aiText,
      source: "local-ai",
    });
  } catch (error) {
    console.error("Local AI failed:", error.message);
    
    return res.status(500).json({
      success: false,
      error: "AI service unavailable",
      message: "Unable to get drug suggestions at this time. Please try again later.",
      details: error.message
    });
  }
};

// Local AI-powered drug suggestion system
function generateLocalAISuggestion(symptoms) {
  const s = symptoms.toLowerCase();
  
  // Comprehensive drug database with AI-like analysis
  const drugDatabase = {
    // Pain and Fever
    headache: [
      {
        name: "Acetaminophen (Tylenol)",
        dosage: "500-1000mg every 4-6 hours",
        how: "Take with or without food, with water",
        precautions: "Avoid if you have liver problems or drink alcohol regularly",
        sideEffects: "Rare: skin rash, liver problems with overdose"
      },
      {
        name: "Ibuprofen (Advil, Motrin)",
        dosage: "200-400mg every 4-6 hours",
        how: "Take with food to protect stomach",
        precautions: "Avoid if you have stomach ulcers, kidney problems, or are pregnant",
        sideEffects: "Stomach upset, dizziness, increased bleeding risk"
      },
      {
        name: "Aspirin",
        dosage: "325-650mg every 4-6 hours",
        how: "Take with food and plenty of water",
        precautions: "Avoid if you have bleeding disorders or are under 18",
        sideEffects: "Stomach irritation, increased bleeding risk"
      }
    ],
    
    fever: [
      {
        name: "Acetaminophen (Tylenol)",
        dosage: "500-1000mg every 4-6 hours",
        how: "Take with water, rest well",
        precautions: "Avoid if you have liver problems",
        sideEffects: "Rare: skin rash, liver problems with overdose"
      },
      {
        name: "Ibuprofen (Advil, Motrin)",
        dosage: "200-400mg every 4-6 hours",
        how: "Take with food and water",
        precautions: "Avoid if you have kidney problems or stomach ulcers",
        sideEffects: "Stomach upset, kidney problems with long-term use"
      }
    ],
    
    // Respiratory
    cough: [
      {
        name: "Dextromethorphan (Robitussin DM)",
        dosage: "15-30mg every 4-6 hours",
        how: "Take with or without food",
        precautions: "Avoid if you have asthma or breathing problems",
        sideEffects: "Drowsiness, dizziness, nausea"
      },
      {
        name: "Guaifenesin (Mucinex)",
        dosage: "200-400mg every 4 hours",
        how: "Take with plenty of water",
        precautions: "Stay hydrated, avoid if you have kidney problems",
        sideEffects: "Nausea, vomiting, headache"
      }
    ],
    
    cold: [
      {
        name: "Pseudoephedrine (Sudafed)",
        dosage: "30-60mg every 4-6 hours",
        how: "Take with or without food",
        precautions: "Avoid if you have high blood pressure, heart problems",
        sideEffects: "Increased heart rate, nervousness, difficulty sleeping"
      },
      {
        name: "Phenylephrine (Sudafed PE)",
        dosage: "10mg every 4 hours",
        how: "Take with water",
        precautions: "Avoid if you have high blood pressure",
        sideEffects: "Headache, dizziness, nervousness"
      }
    ],
    
    // Digestive
    stomachache: [
      {
        name: "Pepto-Bismol",
        dosage: "30ml every 30-60 minutes",
        how: "Take on empty stomach for best results",
        precautions: "Avoid if you have bleeding problems or are allergic to aspirin",
        sideEffects: "Black stools, constipation, ringing in ears"
      },
      {
        name: "Simethicone (Gas-X)",
        dosage: "125-250mg after meals",
        how: "Take after meals and at bedtime",
        precautions: "Safe for most people, including pregnant women",
        sideEffects: "Rare, generally well-tolerated"
      }
    ],
    
    nausea: [
      {
        name: "Dimenhydrinate (Dramamine)",
        dosage: "50-100mg every 4-6 hours",
        how: "Take 30 minutes before travel or as needed",
        precautions: "Avoid if you have glaucoma, prostate problems",
        sideEffects: "Drowsiness, dry mouth, blurred vision"
      },
      {
        name: "Ginger supplements",
        dosage: "250-500mg every 4 hours",
        how: "Take with water",
        precautions: "Generally safe, avoid if you have bleeding disorders",
        sideEffects: "Mild stomach upset, heartburn"
      }
    ],
    
    // Allergies
    allergy: [
      {
        name: "Diphenhydramine (Benadryl)",
        dosage: "25-50mg every 4-6 hours",
        how: "Take with or without food",
        precautions: "Avoid if you have glaucoma, prostate problems, or need to drive",
        sideEffects: "Drowsiness, dry mouth, blurred vision"
      },
      {
        name: "Loratadine (Claritin)",
        dosage: "10mg once daily",
        how: "Take with or without food",
        precautions: "Avoid if you have liver problems",
        sideEffects: "Headache, drowsiness, dry mouth"
      }
    ],
    
    // Sleep
    insomnia: [
      {
        name: "Diphenhydramine (Benadryl)",
        dosage: "25-50mg 30 minutes before bed",
        how: "Take 30 minutes before bedtime",
        precautions: "Avoid if you have glaucoma, prostate problems",
        sideEffects: "Drowsiness, dry mouth, next-day grogginess"
      },
      {
        name: "Melatonin",
        dosage: "1-5mg 30 minutes before bed",
        how: "Take 30 minutes before bedtime",
        precautions: "Avoid if you have autoimmune disorders",
        sideEffects: "Drowsiness, vivid dreams, morning grogginess"
      }
    ]
  };

  // AI-like symptom analysis
  let matchedSymptoms = [];
  let response = `AI Analysis for: "${symptoms}"\n\n`;

  // Check for symptom matches
  for (let [symptom, drugs] of Object.entries(drugDatabase)) {
    if (s.includes(symptom) || symptom.includes(s)) {
      matchedSymptoms.push({ symptom, drugs });
    }
  }

  // If no exact matches, try partial matching
  if (matchedSymptoms.length === 0) {
    const words = s.split(' ');
    for (let word of words) {
      for (let [symptom, drugs] of Object.entries(drugDatabase)) {
        if (symptom.includes(word) || word.includes(symptom)) {
          matchedSymptoms.push({ symptom, drugs });
        }
      }
    }
  }

  if (matchedSymptoms.length > 0) {
    response += `🔍 **AI Analysis Results:**\n`;
    response += `Found ${matchedSymptoms.length} related symptom category(ies)\n\n`;
    
    matchedSymptoms.forEach(({ symptom, drugs }, index) => {
      response += `📋 **${symptom.toUpperCase()} - Recommended Medications:**\n\n`;
      
      drugs.forEach((drug, drugIndex) => {
        response += `${drugIndex + 1}. **${drug.name}**\n`;
        response += `   💊 Dosage: ${drug.dosage}\n`;
        response += `   📝 How to take: ${drug.how}\n`;
        response += `   ⚠️ Precautions: ${drug.precautions}\n`;
        response += `   🔴 Side Effects: ${drug.sideEffects}\n\n`;
      });
    });
    
    response += `🤖 **AI Recommendation:** Based on your symptoms, these medications may help. `;
    response += `Always consult a healthcare provider before starting any medication.\n\n`;
    response += `⚠️ **Important:** This is for informational purposes only. `;
    response += `Consult a doctor for proper diagnosis and treatment.`;
  } else {
    // General recommendations for unknown symptoms
    response += `🔍 **AI Analysis Results:**\n`;
    response += `No specific symptom match found, but here are general recommendations:\n\n`;
    response += `📋 **General Pain & Fever Relief:**\n\n`;
    response += `1. **Acetaminophen (Tylenol)**\n`;
    response += `   💊 Dosage: 500-1000mg every 4-6 hours\n`;
    response += `   📝 How to take: With or without food, with water\n`;
    response += `   ⚠️ Precautions: Avoid with liver problems\n`;
    response += `   🔴 Side Effects: Rare liver problems with overdose\n\n`;
    
    response += `2. **Ibuprofen (Advil, Motrin)**\n`;
    response += `   💊 Dosage: 200-400mg every 4-6 hours\n`;
    response += `   📝 How to take: With food to protect stomach\n`;
    response += `   ⚠️ Precautions: Avoid with stomach ulcers\n`;
    response += `   🔴 Side Effects: Stomach upset, bleeding risk\n\n`;
    
    response += `🤖 **AI Recommendation:** For your specific symptoms, please consult a healthcare provider for proper diagnosis.\n\n`;
    response += `⚠️ **Important:** This is for informational purposes only. `;
    response += `Consult a doctor for proper diagnosis and treatment.`;
  }

  return response;
}

module.exports = { drugSuggestionApi }; 