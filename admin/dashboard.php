<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Order.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$drug = new Drug();
$order = new Order();
$ai_service = new AIService();

$error = '';
$success = '';

// Handle admin actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_system_settings':
            $success = 'System settings updated successfully!';
            break;
        case 'generate_report':
            $success = 'Report generated successfully!';
            break;
    }
}

// Get system statistics
$total_drugs = $drug->getTotalDrugs();
$low_stock_drugs = $drug->getLowStockDrugs(10);
$expiring_drugs = $drug->getExpiringDrugs(30);
$total_orders = $order->getTotalOrders();
$total_sales = $order->getTotalSales();
$ai_usage_stats = $ai_service->getAIUsageStats();
$popular_symptoms = $ai_service->getPopularSymptoms(5);

// Get recent activities
$recent_orders = $order->getRecentOrders(10);
$recent_drugs = $drug->getRecentDrugs(5);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Admin Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link active">
                Dashboard
            </a>
            <a href="users.php" class="nav-link">
                Users Management
            </a>
            <a href="inventory.php" class="nav-link">
                Inventory
            </a>
            <a href="orders.php" class="nav-link">
                Orders
            </a>
            <a href="categories.php" class="nav-link">
                Categories
            </a>
            <a href="reports.php" class="nav-link">
                Reports
            </a>
            <a href="settings.php" class="nav-link">
                Settings
            </a>
          
            <hr class="my-3">
            <a href="../logout.php" class="nav-link text-danger">
                Logout
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
                    <h4 class="mb-0">Admin Dashboard</h4>
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
                            <div class="stat-number"><?php echo number_format($total_drugs); ?></div>
                            <div class="stat-label">Total Drugs</div>
                        </div>
                        <i class="fas fa-pills stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        <i class="fas fa-shopping-cart stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">ETB <?php echo number_format($total_sales, 2); ?></div>
                            <div class="stat-label">Total Sales</div>
                        </div>
                        <i class="fas fa-money-bill-wave stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo isset($ai_usage_stats[0]['total_requests']) ? number_format($ai_usage_stats[0]['total_requests']) : '0'; ?></div>
                            <div class="stat-label">AI Requests</div>
                        </div>
                        <i class="fas fa-robot stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="users.php" class="btn btn-primary w-100">
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="inventory.php" class="btn btn-primary w-100">
                                    Manage Inventory
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="orders.php" class="btn btn-primary w-100">
                                    View Orders
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-primary w-100">
                                    Generate Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts and Recent Activities -->
        <div class="row">
            <!-- Low Stock Alerts -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Low Stock Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_drugs)): ?>
                            <p class="text-muted">No low stock items.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($low_stock_drugs, 0, 5) as $drug_item): ?>
                                <div class="alert alert-warning mb-2">
                                    <strong><?php echo htmlspecialchars($drug_item['name']); ?></strong>
                                    <br>Stock: <?php echo $drug_item['stock_quantity']; ?> units
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($low_stock_drugs) > 5): ?>
                                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Expiring Drugs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Expiring Soon</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($expiring_drugs)): ?>
                            <p class="text-muted">No drugs expiring soon.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($expiring_drugs, 0, 5) as $drug_item): ?>
                                <div class="alert alert-danger mb-2">
                                    <strong><?php echo htmlspecialchars($drug_item['name']); ?></strong>
                                    <br>Expires: <?php echo date('M d, Y', strtotime($drug_item['expiry_date'])); ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($expiring_drugs) > 5): ?>
                                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <p class="text-muted">No recent orders.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($recent_orders, 0, 5) as $order_item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Order #<?php echo $order_item['id']; ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($order_item['customer_name']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge badge-primary"><?php echo ucfirst($order_item['status']); ?></span>
                                        <br><small class="text-muted">ETB <?php echo number_format($order_item['total_amount'], 2); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
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