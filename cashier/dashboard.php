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

// Get today's sales
$today_sales = $order->getSalesReport(date('Y-m-d'), date('Y-m-d'));

// Get recent orders
$recent_orders = $order->getAllOrders(null, 10);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Cashier Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link active">
                Dashboard
            </a>
            <a href="process_orders.php" class="nav-link">
                Process Orders
            </a>
            <a href="sales_report.php" class="nav-link">
                Sales Report
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
                    <h4 class="mb-0">Cashier Dashboard</h4>
                    <p class="mb-0 text-muted">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?></p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo count($pending_orders); ?></div>
                            <div class="stat-label">Pending Orders</div>
                        </div>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $today_sales['total_sales'] ?? 0; ?></div>
                            <div class="stat-label">Today's Sales</div>
                        </div>
                        <i class="fas fa-chart-line stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">ETB <?php echo number_format($today_sales['total_revenue'] ?? 0, 2); ?></div>
                            <div class="stat-label">Today's Revenue</div>
                        </div>
                        <i class="fas fa-money-bill-wave stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">ETB <?php echo number_format($today_sales['average_sale'] ?? 0, 2); ?></div>
                            <div class="stat-label">Average Sale</div>
                        </div>
                        <i class="fas fa-calculator stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <a href="process_orders.php" class="btn btn-primary w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Process Orders
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="print_receipts.php" class="btn btn-primary w-100">
                                    <i class="fas fa-print me-2"></i>Print Receipts
                                </a>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="sales_report.php" class="btn btn-primary w-100">
                                    <i class="fas fa-chart-bar me-2"></i>Sales Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_orders)): ?>
                            <p class="text-muted">No pending orders.</p>
                        <?php else: ?>
                            <?php foreach ($pending_orders as $order_item): ?>
                                <div class="card order-card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="card-title"><?php echo htmlspecialchars($order_item['order_number']); ?></h6>
                                                <p class="card-text">
                                                    <strong>Customer:</strong> <?php echo htmlspecialchars($order_item['customer_name']); ?><br>
                                                    <strong>Phone:</strong> <?php echo htmlspecialchars($order_item['customer_phone']); ?><br>
                                                    <strong>Items:</strong> <?php echo $order_item['item_count']; ?> items<br>
                                                    <strong>Total:</strong> ETB <?php echo number_format($order_item['total_amount'], 2); ?><br>
                                                    <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-center justify-content-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary me-2"
                                                        onclick="window.location.href='process_orders.php?order_id=<?php echo $order_item['id']; ?>'">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-success"
                                                        onclick="window.location.href='process_orders.php?order_id=<?php echo $order_item['id']; ?>'">
                                                        <i class="fas fa-check me-1"></i>Process Payment
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Process Payment Modal -->
                                <div class="modal fade" id="processModal<?php echo $order_item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Process Payment - <?php echo htmlspecialchars($order_item['order_number']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="process_payment">
                                                    <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="payment_method" class="form-label">Payment Method</label>
                                                                <select class="form-select" name="payment_method" required>
                                                                    <option value="cash">Cash</option>
                                                                    <option value="card">Card</option>
                                                                    <option value="mobile_money">Mobile Money</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="final_amount" class="form-label">Final Amount</label>
                                                                <input type="number" class="form-control" name="final_amount"
                                                                    value="<?php echo $order_item['total_amount']; ?>" step="0.01" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="tax_amount" class="form-label">Tax Amount</label>
                                                                <input type="number" class="form-control" name="tax_amount" value="0" step="0.01">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="discount_amount" class="form-label">Discount Amount</label>
                                                                <input type="number" class="form-control" name="discount_amount" value="0" step="0.01">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Process Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Details Modal -->
                                <div class="modal fade" id="viewModal<?php echo $order_item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Order Details - <?php echo htmlspecialchars($order_item['order_number']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                $order_items = $order->getOrderItems($order_item['id']);
                                                if (!empty($order_items)):
                                                ?>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Drug</th>
                                                                    <th>Strength</th>
                                                                    <th>Quantity</th>
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
                                                        </table>
                                                    </div>
                                                    <div class="text-end">
                                                        <h5>Total: ETB <?php echo number_format($order_item['total_amount'], 2); ?></h5>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">No items found for this order.</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        <!-- Recent Orders -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <p class="text-muted">No recent orders.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order_item['order_number']); ?></td>
                                                <td><?php echo htmlspecialchars($order_item['customer_name']); ?></td>
                                                <td><?php echo $order_item['item_count']; ?> items</td>
                                                <td>ETB <?php echo number_format($order_item['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="status-badge badge bg-<?php
                                                                                        echo $order_item['status'] === 'pending' ? 'warning' : ($order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'cancelled' ? 'danger' : 'info'));
                                                                                        ?>">
                                                        <?php echo ucfirst($order_item['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge badge bg-<?php
                                                                                        echo $order_item['payment_status'] === 'paid' ? 'success' : ($order_item['payment_status'] === 'failed' ? 'danger' : 'warning');
                                                                                        ?>">
                                                        <?php echo ucfirst($order_item['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary me-2"
                                                        onclick="window.location.href='process_orders.php?order_id=<?php echo $order_item['id']; ?>'">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                </td>
                                            </tr>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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