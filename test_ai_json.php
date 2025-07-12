<?php
echo "<h2>AI Response JSON Test</h2>";

// Test different scenarios
$test_cases = [
    'headache' => '{"input":"headache"}',
    'fever' => '{"input":"fever"}',
    'cough' => '{"input":"cough"}',
    'cold' => '{"input":"cold"}',
    'unknown' => '{"input":"unknown_symptom"}'
];

foreach ($test_cases as $symptom => $json_data) {
    echo "<h3>Testing: $symptom</h3>";
    
    $test_url = 'https://pharmacy-with-ai.onrender.com/suggest';
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => $json_data,
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($test_url, false, $context);
    
    if ($result === false) {
        echo "<p style='color: red;'>❌ API call failed</p>";
    } else {
        $response = json_decode($result, true);
        
        echo "<h4>Raw JSON Response:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
        echo "</pre>";
        
        echo "<h4>Parsed Response:</h4>";
        echo "<ul>";
        echo "<li><strong>Success:</strong> " . ($response['success'] ? '✅ Yes' : '❌ No') . "</li>";
        echo "<li><strong>Source:</strong> " . ($response['source'] ?? 'unknown') . "</li>";
        echo "<li><strong>Symptoms:</strong> " . htmlspecialchars($response['symptoms'] ?? 'N/A') . "</li>";
        echo "</ul>";
        
        if (isset($response['suggestions'])) {
            echo "<h4>Drug Suggestions:</h4>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;'>";
            echo "<pre style='white-space: pre-wrap;'>" . htmlspecialchars($response['suggestions']) . "</pre>";
            echo "</div>";
        }
    }
    
    echo "<hr style='margin: 30px 0;'>";
}

// Test the legacy endpoint
echo "<h3>Testing Legacy Endpoint: /api/ai/recommend</h3>";
$legacy_url = 'https://pharmacy-with-ai.onrender.com/api/ai/recommend';
$legacy_data = '{"input":"headache"}';

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $legacy_data,
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($legacy_url, false, $context);

if ($result === false) {
    echo "<p style='color: red;'>❌ Legacy API call failed</p>";
} else {
    $response = json_decode($result, true);
    
    echo "<h4>Legacy Endpoint Response:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
    echo "</pre>";
}

// Test GET endpoint
echo "<h3>Testing GET Endpoint: /test?symptoms=headache</h3>";
$get_url = 'https://pharmacy-with-ai.onrender.com/test?symptoms=headache';

$options = [
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($get_url, false, $context);

if ($result === false) {
    echo "<p style='color: red;'>❌ GET API call failed</p>";
} else {
    $response = json_decode($result, true);
    
    echo "<h4>GET Endpoint Response:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    echo htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT));
    echo "</pre>";
}

echo "<h3>JSON Response Structure Summary</h3>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<h4>Standard Response Format:</h4>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd;'>";
echo '{
  "success": true,
  "symptoms": "headache",
  "suggestions": "Drug suggestions for headache:\\n\\n1. Acetaminophen (Tylenol)...",
  "source": "gemini-api" | "fallback"
}';
echo "</pre>";

echo "<h4>Error Response Format:</h4>";
echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd;'>";
echo '{
  "success": false,
  "message": "Error description"
}';
echo "</pre>";
echo "</div>";
?> 