<?php
header('Content-Type: application/json');
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$drug = new Drug();

// Check if user is logged in and has pharmacist role
if (!$auth->isLoggedIn() || !$auth->hasRole('pharmacist')) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $drug_id = $input['drug_id'] ?? null;
    $quantity = $input['quantity'] ?? null;
    $operation = $input['operation'] ?? 'add'; // add or subtract
    
    if (!$drug_id || $quantity === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Drug ID and quantity are required']);
        exit();
    }
    
    if (!is_numeric($quantity) || $quantity < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Quantity must be a positive number']);
        exit();
    }
    
    $result = $drug->updateStock($drug_id, $quantity, $operation);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $result['message']]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 