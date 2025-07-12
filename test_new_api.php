<?php
echo "<h2>Testing New Drug Suggestion API</h2>";

// Test the new API endpoint
$test_url = 'https://pharmacy-with-ai.onrender.com/suggest';
$test_data = [
    'input' => 'cough'
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($test_data),
        'timeout' => 10
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($test_url, false, $context);

if ($result === false) {
    echo "API call failed<br>";
} else {
    $response = json_decode($result, true);
    echo "<h3>API Response:</h3>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
    if (isset($response['source'])) {
        echo "<h3>Source: " . $response['source'] . "</h3>";
    }
    
    if (isset($response['suggestions'])) {
        echo "<h3>Suggestions:</h3>";
        echo "<pre>" . htmlspecialchars($response['suggestions']) . "</pre>";
    }
}

// Test different symptoms
$symptoms_to_test = ['headache', 'fever', 'cough', 'cold', 'unknown_symptom'];

echo "<h3>Testing Different Symptoms:</h3>";

foreach ($symptoms_to_test as $symptom) {
    $test_data = ['input' => $symptom];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($test_data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($test_url, false, $context);
    
    if ($result !== false) {
        $response = json_decode($result, true);
        echo "<h4>Symptom: $symptom</h4>";
        echo "<p><strong>Source:</strong> " . ($response['source'] ?? 'unknown') . "</p>";
        echo "<p><strong>Success:</strong> " . ($response['success'] ? 'Yes' : 'No') . "</p>";
        echo "<hr>";
    }
}
?> 