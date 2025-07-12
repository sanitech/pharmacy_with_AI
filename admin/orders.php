<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$orderObj = new Order();

$error = '';
$success = '';

// Handle order status update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if ($order_id && $status) {
        try {
            $result = $orderObj->updateOrderStatus($order_id, $status);
            if ($result['success']) {
                $success = 'Order status updated successfully!';
            } else {
                $error = $result['message'] ?? 'Failed to update order status.';
            }
        } catch (Exception $e) {
            $error = 'Error updating order: ' . $e->getMessage();
        }
    } else {
        $error = 'Order ID and status are required.';
    }
}

// Get orders with error handling
try {
    $orders = $orderObj->getAllOrders();
} catch (Exception $e) {
    $error = 'Error loading orders: ' . $e->getMessage();
    $orders = [];
}

// Status color mapping
$statusColors = [
    'pending' => 'warning',
    'confirmed' => 'primary',
    'processing' => 'info',
    'ready' => 'secondary',
    'completed' => 'success',
    'cancelled' => 'danger'
];

// Search filter
$search = trim($_GET['search'] ?? '');
if ($search && !empty($orders) && !isset($orders['error'])) {
    $orders = array_filter($orders, function ($order) use ($search) {
        return stripos($order['order_number'], $search) !== false
            || stripos($order['customer_name'], $search) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .order-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .order-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.5rem 1rem;
        }
        .search-box:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .action-btn {
            border-radius: 20px;
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Admin Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>Users
            </a>
            <a href="inventory.php" class="nav-link">
                <i class="fas fa-boxes"></i>Inventory
            </a>
            <a href="orders.php" class="nav-link active">
                <i class="fas fa-shopping-cart"></i>Orders
            </a>
            <a href="categories.php" class="nav-link">
                <i class="fas fa-tags"></i>Categories
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>Reports
            </a>
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>Settings
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
                    <h4 class="mb-0">Order Management</h4>
                    <p class="mb-0 text-muted">View and manage all customer orders</p>
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

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="get" class="d-flex">
                        <input type="search" name="search" class="form-control search-box me-2" 
                               placeholder="Search orders by number or customer..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo count($orders); ?> orders found
                    </span>
                </div>
            </div>

            <!-- Orders List -->
            <div class="row">
                <div class="col-12">
                    <?php if (empty($orders) || isset($orders['error'])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No orders found</h5>
                            <p class="text-muted">There are no orders to display at the moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="card order-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <h6 class="mb-1">
                                                <i class="fas fa-hashtag me-1"></i>
                                                <?php echo htmlspecialchars($order['order_number']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($order['customer_name']); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-2">
                                            <strong class="text-primary">
                                                ETB <?php echo number_format($order['total_amount'], 2); ?>
                                            </strong>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-<?php echo $statusColors[$order['status']] ?? 'secondary'; ?> status-badge">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="button" class="btn btn-outline-info action-btn me-1" 
                                                    data-bs-toggle="modal" data-bs-target="#viewOrderModal<?php echo $order['id']; ?>"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-primary action-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#statusOrderModal<?php echo $order['id']; ?>"
                                                    title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View Order Modal -->
                            <div class="modal fade" id="viewOrderModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-eye me-2"></i>
                                                Order Details - <?php echo htmlspecialchars($order['order_number']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="mb-3"><i class="fas fa-user me-2"></i>Customer Information</h6>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                    <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                                                    <p><strong>Status:</strong> 
                                                        <span class="badge bg-<?php echo $statusColors[$order['status']] ?? 'secondary'; ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Order Information</h6>
                                                    <p><strong>Total Amount:</strong> ETB <?php echo number_format($order['total_amount'], 2); ?></p>
                                                    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Not specified'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Update Status Modal -->
                            <div class="modal fade" id="statusOrderModal<?php echo $order['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>
                                                Update Order Status
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                
                                                <p><strong>Order:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">New Status</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="">Select Status</option>
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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