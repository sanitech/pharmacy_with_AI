<?php
header('Content-Type: application/json');
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

// Simple API key check (in production, use proper authentication)
$api_key = $_GET['api_key'] ?? '';
if ($api_key !== 'sky_pharmacy_api_2024') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = $_GET['order_id'] ?? '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

try {
    $order = new Order();
    $order_data = $order->getOrderById($order_id);
    $order_items = $order->getOrderItems($order_id);
    
    if ($order_data && !isset($order_data['error'])) {
        echo json_encode([
            'success' => true,
            'order' => $order_data,
            'items' => $order_items
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 