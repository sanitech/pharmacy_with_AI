<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('customer');

$user = $auth->getCurrentUser();
$order = new Order();

$error = '';
$success = '';

// Get user's orders
$user_orders = $order->getOrdersByCustomer($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .order-card { border-left: 4px solid #3498db; margin-bottom: 15px; transition: transform 0.2s; }
        .order-card:hover { transform: translateY(-2px); }
        .modal-header { background: #2c3e50; color: white; }
        .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        .status-badge { font-size: 0.8rem; padding: 5px 10px; border-radius: 20px; }
        .tab-content { padding-top: 20px; }
        .nav-tabs .nav-link { border-radius: 10px 10px 0 0; margin-right: 5px; }
        .nav-tabs .nav-link.active { background: #3498db; color: white; border: none; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Customer Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="search.php" class="nav-link">
                <i class="fas fa-search"></i>Search Drugs
            </a>
            <a href="orders.php" class="nav-link active">
                <i class="fas fa-shopping-bag"></i>My Orders
            </a>
            <a href="prescriptions.php" class="nav-link">
                <i class="fas fa-prescription"></i>My Prescriptions
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
                    <h4 class="mb-0">My Orders</h4>
                    <p class="mb-0 text-muted">View your order history</p>
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

            <?php if (empty($user_orders)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h5>No Orders Yet</h5>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Order Tabs -->
                <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>All Orders
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                            <i class="fas fa-check-circle me-2"></i>Completed
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                            <i class="fas fa-clock me-2"></i>Pending
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="orderTabsContent">
                    <!-- All Orders Tab -->
                    <div class="tab-pane fade show active" id="all" role="tabpanel">
                        <div class="row">
                            <?php foreach ($user_orders as $order_item): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card order-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($order_item['order_number']); ?></h6>
                                                    <p class="text-muted mb-0"><?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></p>
                                                </div>
                                                <span class="status-badge badge bg-<?php echo $order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($order_item['status']); ?>
                                                </span>
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
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewOrderModal<?php echo $order_item['id']; ?>">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Order Modal -->
                                <div class="modal fade" id="viewOrderModal<?php echo $order_item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-eye me-2"></i>
                                                    Order Details - <?php echo htmlspecialchars($order_item['order_number']); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-info-circle me-2"></i>Order Information</h6>
                                                        <p><strong>Order #:</strong> <?php echo htmlspecialchars($order_item['order_number']); ?></p>
                                                        <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></p>
                                                        <p><strong>Status:</strong> 
                                                            <span class="badge bg-<?php echo $order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                                <?php echo ucfirst($order_item['status']); ?>
                                                            </span>
                                                        </p>
                                                        <p><strong>Payment:</strong> 
                                                            <span class="badge bg-<?php echo $order_item['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                                                <?php echo ucfirst($order_item['payment_status']); ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-credit-card me-2"></i>Payment Details</h6>
                                                        <p><strong>Total Amount:</strong> ETB <?php echo number_format($order_item['total_amount'], 2); ?></p>
                                                        <?php if ($order_item['payment_method']): ?>
                                                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order_item['payment_method']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <?php 
                                                $order_items = $order->getOrderItems($order_item['id']);
                                                if (!empty($order_items)): 
                                                ?>
                                                    <h6><i class="fas fa-pills me-2"></i>Order Items</h6>
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
                                                            <tbody>
                                                                <?php foreach ($order_items as $item): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($item['drug_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($item['strength']); ?></td>
                                                                        <td><?php echo $item['quantity']; ?></td>
                                                                        <td>ETB <?php echo number_format($item['unit_price'], 2); ?></td>
                                                                        <td>ETB <?php echo number_format($item['total_price'], 2); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                                    <td><strong>ETB <?php echo number_format($order_item['total_amount'], 2); ?></strong></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Completed Orders Tab -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <div class="row">
                            <?php 
                            $completed_orders = array_filter($user_orders, function($order) {
                                return $order['status'] === 'completed';
                            });
                            ?>
                            <?php if (empty($completed_orders)): ?>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                            <h5>No Completed Orders</h5>
                                            <p class="text-muted">You don't have any completed orders yet.</p>
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
                                                        <p class="text-muted mb-0"><?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></p>
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
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewOrderModal<?php echo $order_item['id']; ?>">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pending Orders Tab -->
                    <div class="tab-pane fade" id="pending" role="tabpanel">
                        <div class="row">
                            <?php 
                            $pending_orders = array_filter($user_orders, function($order) {
                                return $order['status'] === 'pending';
                            });
                            ?>
                            <?php if (empty($pending_orders)): ?>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h5>No Pending Orders</h5>
                                            <p class="text-muted">You don't have any pending orders.</p>
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
                                                        <p class="text-muted mb-0"><?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></p>
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
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewOrderModal<?php echo $order_item['id']; ?>">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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