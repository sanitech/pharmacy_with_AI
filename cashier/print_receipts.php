<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('cashier');

$user = $auth->getCurrentUser();
$order = new Order();

$error = '';
$success = '';

// Handle receipt printing
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'print_receipt') {
    $order_id = $_POST['order_id'] ?? '';
    if (!empty($order_id)) {
        $order_data = $order->getOrderById($order_id);
        $order_items = $order->getOrderItems($order_id);
        
        if ($order_data && !isset($order_data['error'])) {
            // Generate receipt content
            $receipt_content = generateReceipt($order_data, $order_items);
            $success = 'Receipt generated successfully!';
        } else {
            $error = 'Order not found';
        }
    }
}

function generateReceipt($order_data, $order_items) {
    global $user;
    $receipt = "
    ========================================
              SKY PHARMACY RECEIPT
    ========================================
    
    Receipt #: " . $order_data['order_number'] . "
    Date: " . date('Y-m-d H:i:s', strtotime($order_data['created_at'])) . "
    Cashier: " . $user['full_name'] . "
    
    Customer: " . $order_data['customer_name'] . "
    Phone: " . $order_data['customer_phone'] . "
    
    ========================================
    ITEMS:
    ========================================
    ";
    
    $total = 0;
    foreach ($order_items as $item) {
        $receipt .= sprintf("%-20s %s\n", $item['drug_name'], $item['strength']);
        $receipt .= sprintf("  %d x ETB %.2f = ETB %.2f\n", $item['quantity'], $item['unit_price'], $item['total_price']);
        $total += $item['total_price'];
    }
    
    $receipt .= "
    ========================================
    SUBTOTAL: ETB " . number_format($total, 2) . "
    TAX: ETB " . number_format($order_data['tax_amount'] ?? 0, 2) . "
    DISCOUNT: ETB " . number_format($order_data['discount_amount'] ?? 0, 2) . "
    ========================================
    TOTAL: ETB " . number_format($order_data['total_amount'], 2) . "
    ========================================
    
    Payment Method: " . ucfirst($order_data['payment_method']) . "
    Status: " . ucfirst($order_data['payment_status']) . "
    
    Thank you for choosing Sky Pharmacy!
    Please keep this receipt for your records.
    
    ========================================
    ";
    
    return $receipt;
}

// Get completed orders for receipt printing
$completed_orders = $order->getAllOrders('completed', 50);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipts - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .receipt-preview {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-line;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Cashier Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="process_orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>Process Orders
            </a>
            <a href="print_receipts.php" class="nav-link active">
                <i class="fas fa-print"></i>Print Receipts
            </a>
            <a href="sales_report.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>Sales Report
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i>Profile
            </a>
            <hr class="my-3">
            <a href="../logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i>Logout
            </a>
        </nav>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Print Receipts</h4>
                    <p class="mb-0 text-muted">Generate and print receipts for completed orders</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-print me-2"></i>Print Receipts
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">Select a completed order to generate and print a receipt.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Completed Orders</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($completed_orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h5>No Completed Orders</h5>
                                    <p class="text-muted">No completed orders found for receipt printing.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Payment Method</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($completed_orders as $order_item): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($order_item['order_number']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($order_item['customer_name']); ?></td>
                                                    <td><?php echo $order_item['item_count']; ?> items</td>
                                                    <td><strong>ETB <?php echo number_format($order_item['total_amount'], 2); ?></strong></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo ucfirst($order_item['payment_method'] ?? 'cash'); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#receiptModal<?php echo $order_item['id']; ?>">
                                                            <i class="fas fa-print me-1"></i>Print Receipt
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Receipt Modal -->
                                                <div class="modal fade" id="receiptModal<?php echo $order_item['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-receipt me-2"></i>
                                                                    Receipt - <?php echo htmlspecialchars($order_item['order_number']); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="receipt-preview">
                                                                    <?php 
                                                                    $order_data = $order->getOrderById($order_item['id']);
                                                                    $order_items = $order->getOrderItems($order_item['id']);
                                                                    if ($order_data && !isset($order_data['error'])) {
                                                                        echo htmlspecialchars(generateReceipt($order_data, $order_items));
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="button" class="btn btn-primary" onclick="window.print()">
                                                                    <i class="fas fa-print me-2"></i>Print Receipt
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            this.classList.remove('show');
        });
    </script>
</body>
</html> 