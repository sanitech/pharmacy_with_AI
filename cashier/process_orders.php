<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('cashier');

$user = $auth->getCurrentUser();
$order = new Order();
$drug = new Drug();

$error = '';
$success = '';

// Handle order processing
if ($_POST && isset($_POST['action'])) {
    $order_id = $_POST['order_id'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $final_amount = $_POST['final_amount'] ?? 0;
    $tax_amount = $_POST['tax_amount'] ?? 0;
    $discount_amount = $_POST['discount_amount'] ?? 0;

    if ($_POST['action'] === 'process_payment') {
        $result = $order->processPayment($order_id, $user['id'], $payment_method, $final_amount, $tax_amount, $discount_amount);
        if ($result['success']) {
            $success = 'Payment processed successfully! Sales ID: ' . $result['sales_id'];
        } else {
            $error = $result['message'];
        }
    } elseif ($_POST['action'] === 'update_status') {
        $status = $_POST['status'] ?? '';
        $result = $order->updateOrderStatus($order_id, $status, $user['id']);
        if ($result['success']) {
            $success = 'Order status updated successfully!';
        } else {
            $error = $result['message'];
        }
    }
}

// Get pending orders
$pending_orders = $order->getAllOrders('pending');
$processing_orders = $order->getAllOrders('processing');
$completed_orders = $order->getAllOrders('completed', 10);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Orders - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .btn-primary {
            background: #3498db;
            border: none;
            border-radius: 10px;
        }

        .order-card {
            border-left: 4px solid #3498db;
            margin-bottom: 15px;
            transition: transform 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
        }

        .modal-header {
            background: #2c3e50;
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .tab-content {
            padding-top: 20px;
        }

        .nav-tabs .nav-link {
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
        }

        .nav-tabs .nav-link.active {
            background: #3498db;
            color: white;
            border: none;
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
            <a href="process_orders.php" class="nav-link active">
                <i class="fas fa-shopping-cart"></i>Process Orders
            </a>
            <a href="print_receipts.php" class="nav-link">
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
                    <h4 class="mb-0">Process Orders</h4>
                    <p class="mb-0 text-muted">Process customer orders and payments</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

           

            <!-- Order Tabs -->
            <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Pending Orders (<?php echo count($pending_orders); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>Processing (<?php echo count($processing_orders); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Completed
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="orderTabsContent">
                <!-- Pending Orders Tab -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="row">
                        <?php if (empty($pending_orders)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5>No Pending Orders</h5>
                                        <p class="text-muted">All orders have been processed.</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pending_orders as $order_item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card order-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($order_item['order_number']); ?></h6>
                                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($order_item['customer_name']); ?></p>
                                                </div>
                                                <span class="status-badge badge bg-warning">Pending</span>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Items:</small><br>
                                                    <strong><?php echo $order_item['item_count']; ?> items</strong>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <small class="text-muted">Total:</small><br>
                                                    <strong>ETB <?php echo number_format($order_item['total_amount'], 2); ?></strong>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?>
                                                </small>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary me-2"
                                                        onclick="viewOrder(<?php echo $order_item['id']; ?>, 'pending')">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-primary"
                                                        onclick="processOrder(<?php echo $order_item['id']; ?>)">
                                                        <i class="fas fa-credit-card me-1"></i>Process
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Processing Orders Tab -->
                <div class="tab-pane fade" id="processing" role="tabpanel">
                    <div class="row">
                        <?php if (empty($processing_orders)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                        <h5>No Orders in Processing</h5>
                                        <p class="text-muted">All orders are either pending or completed.</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($processing_orders as $order_item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card order-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($order_item['order_number']); ?></h6>
                                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($order_item['customer_name']); ?></p>
                                                </div>
                                                <span class="status-badge badge bg-info">Processing</span>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Items:</small><br>
                                                    <strong><?php echo $order_item['item_count']; ?> items</strong>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <small class="text-muted">Total:</small><br>
                                                    <strong>ETB <?php echo number_format($order_item['total_amount'], 2); ?></strong>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?>
                                                </small>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary me-2"
                                                        onclick="viewOrder(<?php echo $order_item['id']; ?>, 'processing')">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-success"
                                                        onclick="completeOrder(<?php echo $order_item['id']; ?>)">
                                                        <i class="fas fa-check me-1"></i>Complete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Completed Orders Tab -->
                <div class="tab-pane fade" id="completed" role="tabpanel">
                    <div class="row">
                        <?php if (empty($completed_orders)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                        <h5>No Completed Orders</h5>
                                        <p class="text-muted">Complete some orders to see them here.</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($completed_orders as $order_item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card order-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($order_item['order_number']); ?></h6>
                                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($order_item['customer_name']); ?></p>
                                                </div>
                                                <span class="status-badge badge bg-success">Completed</span>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted">Items:</small><br>
                                                    <strong><?php echo $order_item['item_count']; ?> items</strong>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <small class="text-muted">Total:</small><br>
                                                    <strong>ETB <?php echo number_format($order_item['total_amount'], 2); ?></strong>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?>
                                                </small>
                                                <div>
                                                    <button class="btn btn-sm btn-outline-primary me-2"
                                                        onclick="viewOrder(<?php echo $order_item['id']; ?>, 'completed')">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <a href="print_receipts.php?order_id=<?php echo $order_item['id']; ?>"
                                                        class="btn btn-sm btn-outline-secondary" target="_blank">
                                                        <i class="fas fa-print me-1"></i>Receipt
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Universal View Order Modal -->
        <div class="modal fade" id="viewOrderModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewOrderModalTitle">Order Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="viewOrderModalBody">
                        <!-- Content will be loaded dynamically -->
                    </div>
                    <div class="modal-footer" id="viewOrderModalFooter">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Universal Process Order Modal -->
        <div class="modal fade" id="processOrderModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="processOrderModalTitle">Process Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="processOrderForm">
                        <div class="modal-body" id="processOrderModalBody">
                            <!-- Content will be loaded dynamically -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-credit-card me-2"></i>Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrder(orderId, status) {
            // Fetch order details via AJAX
            fetch(`../api/get_order_details.php?order_id=${orderId}&api_key=sky_pharmacy_api_2024`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        const items = data.items;

                        // Update modal title
                        document.getElementById('viewOrderModalTitle').textContent = `Order Details - ${order.order_number}`;

                        // Build modal content
                        let content = `
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6>Customer Information</h6>
                                    <p><strong>Name:</strong> ${order.customer_name}</p>
                                    <p><strong>Phone:</strong> ${order.customer_phone}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Order Information</h6>
                                    <p><strong>Order #:</strong> ${order.order_number}</p>
                                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                                    <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(status)}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></p>
                                </div>
                            </div>`;

                        if (items && items.length > 0) {
                            content += `
                                <h6>Order Items</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Drug</th>
                                                <th>Strength</th>
                                                <th>Qty</th>
                                                <th>Unit Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                            items.forEach(item => {
                                content += `
                                    <tr>
                                        <td>${item.drug_name}</td>
                                        <td>${item.strength}</td>
                                        <td>${item.quantity}</td>
                                        <td>ETB ${parseFloat(item.unit_price).toFixed(2)}</td>
                                        <td>ETB ${parseFloat(item.total_price).toFixed(2)}</td>
                                    </tr>`;
                            });

                            content += `
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                <td><strong>ETB ${parseFloat(order.total_amount).toFixed(2)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>`;
                        }

                        document.getElementById('viewOrderModalBody').innerHTML = content;

                        // Update footer based on status
                        let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';

                        if (status === 'pending') {
                            footer += `<button type="button" class="btn btn-primary ms-2" onclick="processOrder(${orderId})" data-bs-dismiss="modal">Process Payment</button>`;
                        } else if (status === 'processing') {
                            footer += `<button type="button" class="btn btn-success ms-2" onclick="completeOrder(${orderId})" data-bs-dismiss="modal">Mark as Complete</button>`;
                        } else if (status === 'completed') {
                            footer += `<a href="print_receipts.php?order_id=${orderId}" class="btn btn-primary ms-2" target="_blank">Print Receipt</a>`;
                        }

                        document.getElementById('viewOrderModalFooter').innerHTML = footer;

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
                        modal.show();
                    } else {
                        alert('Failed to load order details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details. Please check your connection and try again.');
                });
        }

        function processOrder(orderId) {
            // Fetch order details for processing
            fetch(`../api/get_order_details.php?order_id=${orderId}&api_key=sky_pharmacy_api_2024`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;

                        // Update modal title
                        document.getElementById('processOrderModalTitle').textContent = `Process Payment - ${order.order_number}`;

                        // Build form content
                        const content = `
                            <input type="hidden" name="action" value="process_payment">
                            <input type="hidden" name="order_id" value="${orderId}">
                            <input type="hidden" name="final_amount" value="${order.total_amount}">
                            
                            <div class="mb-3">
                                <label class="form-label">Order Total</label>
                                <h4 class="text-primary">ETB ${parseFloat(order.total_amount).toFixed(2)}</h4>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>`;

                        document.getElementById('processOrderModalBody').innerHTML = content;

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('processOrderModal'));
                        modal.show();
                    } else {
                        alert('Failed to load order details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details. Please check your connection and try again.');
                });
        }

        function completeOrder(orderId) {
            if (confirm('Mark this order as completed?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="${orderId}">
                    <input type="hidden" name="status" value="completed">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function getStatusColor(status) {
            switch (status) {
                case 'pending':
                    return 'warning';
                case 'processing':
                    return 'info';
                case 'completed':
                    return 'success';
                default:
                    return 'secondary';
            }
        }

        // Auto-open modal and select correct tab if order_id is in URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('order_id');
            if (orderId) {
                // Try to find the order in all tabs
                let found = false;
                let status = 'pending';
                // Check pending orders
                if (window.pending_orders && window.pending_orders.includes(parseInt(orderId))) {
                    status = 'pending';
                    document.getElementById('pending-tab').click();
                    found = true;
                } else if (window.processing_orders && window.processing_orders.includes(parseInt(orderId))) {
                    status = 'processing';
                    document.getElementById('processing-tab').click();
                    found = true;
                } else if (window.completed_orders && window.completed_orders.includes(parseInt(orderId))) {
                    status = 'completed';
                    document.getElementById('completed-tab').click();
                    found = true;
                }
                // If not found, default to pending
                viewOrder(orderId, status);
            }
        });
    </script>

    <!-- Optional: Expose order IDs for JS -->
    <script>
        window.pending_orders = [<?php echo implode(',', array_map(function ($o) {
                                        return $o['id'];
                                    }, $pending_orders)); ?>];
        window.processing_orders = [<?php echo implode(',', array_map(function ($o) {
                                        return $o['id'];
                                    }, $processing_orders)); ?>];
        window.completed_orders = [<?php echo implode(',', array_map(function ($o) {
                                        return $o['id'];
                                    }, $completed_orders)); ?>];
    </script>

    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        // Set active nav link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar-menu .nav-link');

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>