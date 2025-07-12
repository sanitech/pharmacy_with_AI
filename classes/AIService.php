<?php
require_once __DIR__ . '/../config/database.php';

class AIService {
    private $conn;
    private $ai_service_url;
    private $ai_suggest_url;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ai_service_url = 'https://pharmacy-with-ai.onrender.com/api/ai/recommend'; // Legacy route
        $this->ai_suggest_url = 'https://pharmacy-with-ai.onrender.com/suggest'; // New main route
    }
    
    public function getRecommendations($input_text, $user_id = null) {
        try {
            // Try the new suggest route first
            $response = $this->callAIService($input_text, $this->ai_suggest_url);
            
            // Log the AI interaction
            $this->logAIInteraction($user_id, $input_text, $response);
            
            return $response;
        } catch (Exception $e) {
            // Fallback to local drug database
            return $this->getFallbackRecommendations($input_text, $user_id);
        }
    }
    
    public function getSuggestions($symptoms, $user_id = null) {
        try {
            // Use the new suggest route
            $response = $this->callAIService($symptoms, $this->ai_suggest_url);
            
            // Log the AI interaction
            $this->logAIInteraction($user_id, $symptoms, $response);
            
            return $response;
        } catch (Exception $e) {
            // Fallback to local drug database
            return $this->getFallbackRecommendations($symptoms, $user_id);
        }
    }
    
    private function callAIService($input_text, $url = null) {
        $service_url = $url ?: $this->ai_suggest_url;
        
        $data = [
            'input' => $input_text,
            'symptoms' => $input_text
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => 10 // Reduced timeout
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($service_url, false, $context);
        
        if ($result === false) {
            throw new Exception('AI service not available');
        }
        
        $response = json_decode($result, true);
        
        if (!$response) {
            throw new Exception('Invalid response from AI service');
        }
        
        return $response;
    }
    
    private function getFallbackRecommendations($input_text, $user_id = null) {
        // Local fallback drug recommendations based on symptoms
        $symptoms = strtolower($input_text);
        $recommendations = [];
        $available_drugs = [];
        
        // Simple symptom-to-drug mapping
        $symptom_drugs = [
            'headache' => ['Paracetamol', 'Ibuprofen', 'Aspirin'],
            'fever' => ['Paracetamol', 'Ibuprofen'],
            'cough' => ['Dextromethorphan', 'Guaifenesin'],
            'cold' => ['Pseudoephedrine', 'Cetirizine'],
            'pain' => ['Paracetamol', 'Ibuprofen'],
            'allergy' => ['Cetirizine', 'Loratadine'],
            'vitamin' => ['Vitamin C'],
            'antibiotic' => ['Amoxicillin']
        ];
        
        // Find matching symptoms and drugs
        foreach ($symptom_drugs as $symptom => $drugs) {
            if (strpos($symptoms, $symptom) !== false) {
                foreach ($drugs as $drug) {
                    $recommendations[] = $drug;
                }
            }
        }
        
        // If no specific matches, suggest common drugs
        if (empty($recommendations)) {
            $recommendations = ['Paracetamol', 'Ibuprofen', 'Vitamin C'];
        }
        
        // Get available drugs from database
        $available_drugs = $this->searchDrugsByName($recommendations);
        
        // Create fallback response
        $fallback_text = "Based on your symptoms: '$input_text'\n\n";
        $fallback_text .= "Recommended medications:\n";
        foreach ($recommendations as $index => $drug) {
            $fallback_text .= ($index + 1) . ". $drug\n";
        }
        $fallback_text .= "\nNote: This is a general recommendation. Please consult with a pharmacist for proper dosage and usage instructions.";
        
        // Log the fallback interaction
        $this->logAIInteraction($user_id, $input_text, [
            'success' => true,
            'suggestions' => $fallback_text,
            'source' => 'fallback'
        ]);
        
        return [
            'success' => true,
            'suggestions' => $fallback_text,
            'available_drugs' => $available_drugs,
            'source' => 'fallback'
        ];
    }
    
    private function logAIInteraction($user_id, $input_text, $response) {
        try {
            $query = "INSERT INTO ai_recommendations (user_id, input_text, ai_response) 
                      VALUES (:user_id, :input_text, :ai_response)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':input_text', $input_text);
            $ai_response_json = json_encode($response);
            $stmt->bindParam(':ai_response', $ai_response_json);
            $stmt->execute();
        } catch (PDOException $e) {
            // Log error but don't fail the main request
            error_log('Failed to log AI interaction: ' . $e->getMessage());
        }
    }
    
    public function getAIUsageStats($user_id = null, $start_date = null, $end_date = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($user_id) {
                $where_conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($start_date) {
                $where_conditions[] = "created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $where_conditions[] = "created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT 
                        COUNT(*) as total_requests,
                        COUNT(DISTINCT user_id) as unique_users,
                        DATE(created_at) as date,
                        COUNT(*) as daily_requests
                      FROM ai_recommendations 
                      $where_clause 
                      GROUP BY DATE(created_at) 
                      ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPopularSymptoms($limit = 10) {
        try {
            $query = "SELECT input_text, COUNT(*) as frequency 
                      FROM ai_recommendations 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                      GROUP BY input_text 
                      ORDER BY frequency DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getRecentRecommendations($limit = 20) {
        try {
            $query = "SELECT ar.*, u.full_name as user_name 
                      FROM ai_recommendations ar 
                      LEFT JOIN users u ON ar.user_id = u.id 
                      ORDER BY ar.created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function suggestDrugsFromSymptoms($symptoms) {
        try {
            // Get AI recommendations using the new suggest method
            $ai_response = $this->getSuggestions($symptoms);
            
            if (!$ai_response['success']) {
                return $ai_response;
            }
            
            // Extract drug names from AI response and search in database
            $drug_names = $this->extractDrugNames($ai_response['suggestions']);
            $available_drugs = $this->searchDrugsByName($drug_names);
            
            return [
                'success' => true,
                'ai_suggestions' => $ai_response['suggestions'],
                'available_drugs' => $available_drugs,
                'source' => $ai_response['source'] ?? 'ai'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function extractDrugNames($ai_text) {
        // Simple extraction - in a real system, you might use NLP
        $common_drugs = [
            'paracetamol', 'acetaminophen', 'ibuprofen', 'aspirin', 'amoxicillin',
            'cetirizine', 'loratadine', 'pseudoephedrine', 'dextromethorphan',
            'guaifenesin', 'vitamin c', 'ascorbic acid'
        ];
        
        $found_drugs = [];
        $text_lower = strtolower($ai_text);
        
        foreach ($common_drugs as $drug) {
            if (strpos($text_lower, $drug) !== false) {
                $found_drugs[] = $drug;
            }
        }
        
        return $found_drugs;
    }
    
    private function searchDrugsByName($drug_names) {
        if (empty($drug_names)) {
            return [];
        }
        
        try {
            // Create placeholders for the IN clause
            $placeholders = str_repeat('?,', count($drug_names) - 1) . '?';
            $query = "SELECT * FROM drugs 
                      WHERE (LOWER(name) IN ($placeholders) 
                      OR LOWER(generic_name) IN ($placeholders)) 
                      AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            
            // Create parameters array manually to avoid reference issues
            $params = [];
            foreach ($drug_names as $drug) {
                $params[] = strtolower($drug);
            }
            foreach ($drug_names as $drug) {
                $params[] = strtolower($drug);
            }
            
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function isAIServiceAvailable() {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]);
            
            $result = @file_get_contents('http://https://pharmacy-with-ai.onrender.com/health', false, $context);
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getAIUsageReport($start_date = null, $end_date = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($start_date) {
                $where_conditions[] = "ar.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $where_conditions[] = "ar.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT 
                        DATE(ar.created_at) as date,
                        COUNT(*) as total_requests,
                        COUNT(DISTINCT ar.user_id) as unique_users,
                        AVG(LENGTH(ar.input_text)) as avg_input_length,
                        COUNT(CASE WHEN JSON_EXTRACT(ar.ai_response, '$.source') = 'fallback' THEN 1 END) as fallback_requests,
                        COUNT(CASE WHEN JSON_EXTRACT(ar.ai_response, '$.source') != 'fallback' THEN 1 END) as ai_requests
                      FROM ai_recommendations ar 
                      $where_clause 
                      GROUP BY DATE(ar.created_at) 
                      ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?> 