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

// Set page title for header
$page_title = 'Dashboard';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
            <div class="text-muted">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_drugs; ?></h4>
                                <p class="mb-0">Total Drugs</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pills fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_orders; ?></h4>
                                <p class="mb-0">Total Orders</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>ETB <?php echo number_format($total_sales, 2); ?></h4>
                                <p class="mb-0">Total Sales</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count($low_stock_drugs); ?></h4>
                                <p class="mb-0">Low Stock</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="inventory.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add Drug
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="users.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add User
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-chart-bar me-2"></i>View Reports
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="ai_suggest.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-robot me-2"></i>AI Suggestions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Orders</h5>
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
                                        <span class="badge bg-primary"><?php echo ucfirst($order_item['status']); ?></span>
                                        <br><small class="text-muted">ETB <?php echo number_format($order_item['total_amount'], 2); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_drugs)): ?>
                            <p class="text-muted">No low stock alerts.</p>
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
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>