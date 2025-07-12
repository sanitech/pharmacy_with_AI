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
    $search_term = $_GET['search'] ?? '';
    $category_id = $_GET['category_id'] ?? null;
    $limit = $_GET['limit'] ?? 20;
    
    $drugs = $drug->getAllDrugs($search_term, $category_id, $limit);
    
    if (is_array($drugs)) {
        echo json_encode(['success' => true, 'drugs' => $drugs]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to search drugs']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 