<?php
header('Content-Type: application/json');
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$drug = new Drug();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $drug_id = $_GET['drug_id'] ?? null;
    
    if (!$drug_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Drug ID is required']);
        exit();
    }
    
    $drug_details = $drug->getDrugById($drug_id);
    
    if ($drug_details) {
        echo json_encode(['success' => true, 'drug' => $drug_details]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Drug not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 