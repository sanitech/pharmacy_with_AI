<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Prescription.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$drug = new Drug();
$prescription = new Prescription();
$order = new Order();

$error = '';
$success = '';

// Handle report generation
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'generate_report') {
    $report_type = $_POST['report_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    // Generate report data based on type
    switch ($report_type) {
        case 'inventory':
            $report_data = $drug->getAllDrugs();
            break;
        case 'prescriptions':
            $report_data = $prescription->getAllPrescriptions();
            break;
        case 'sales':
            $report_data = $order->getSalesReport($start_date, $end_date);
            break;
        default:
            $report_data = [];
    }
}

$custom_report = null;
$custom_report_type = '';
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'custom_report') {
    $custom_report_type = $_POST['custom_report_type'] ?? '';
    $custom_start = $_POST['custom_start'] ?? '';
    $custom_end = $_POST['custom_end'] ?? '';
    switch ($custom_report_type) {
        case 'sales':
            $custom_report = $order->getSalesReport($custom_start, $custom_end);
            break;
        case 'inventory':
            $custom_report = $drug->getAllDrugs('', null, 1000);
            break;
        case 'prescriptions':
            $custom_report = $prescription->getAllPrescriptions($custom_start, $custom_end);
            break;
        case 'top_drugs':
            $custom_report = $order->getTopSellingDrugs(20, $custom_start, $custom_end);
            break;
        default:
            $custom_report = [];
    }
}

// Get report data
$total_drugs = $drug->getTotalDrugs();
$low_stock_drugs = $drug->getLowStockDrugs();
$expiring_drugs = $drug->getExpiringDrugs(30);
$prescription_stats = $prescription->getPrescriptionStats();
$today_sales = $order->getSalesReport(date('Y-m-d'), date('Y-m-d'));
$monthly_sales = $order->getSalesReport(date('Y-m-01'), date('Y-m-t'));
$top_drugs = $order->getTopSellingDrugs(10, date('Y-m-01'), date('Y-m-t'));
$recent_orders = $order->getRecentOrders(10);
$order_status_counts = [
    'pending' => count($order->getAllOrders('pending', 1000)),
    'approved' => count($order->getAllOrders('approved', 1000)),
    'completed' => count($order->getAllOrders('completed', 1000)),
    'rejected' => count($order->getAllOrders('rejected', 1000)),
];

// Set page title for header
$page_title = 'Reports';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

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
                <h4 class="mb-0">Reports & Analytics</h4>
                <p class="mb-0 text-muted">Comprehensive reports on inventory, prescriptions, and sales</p>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Key Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo number_format($total_drugs); ?></div>
                            <div>Total Drugs</div>
                        </div>
                        <i class="fas fa-pills fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo count($low_stock_drugs); ?></div>
                            <div>Low Stock Items</div>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number">ETB <?php echo number_format($today_sales['total_revenue'] ?? 0, 0); ?></div>
                            <div>Today's Sales</div>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['pending'] ?? 0; ?></div>
                            <div>Pending Rx</div>
                        </div>
                        <i class="fas fa-prescription fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Reports -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_drugs)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h6>All Stock Levels Good</h6>
                                <p class="text-muted">No low stock items to report.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($low_stock_drugs, 0, 10) as $drug_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($drug_item['name']); ?></td>
                                                <td><span class="badge bg-danger"><?php echo $drug_item['stock_quantity']; ?></span></td>
                                                <td><?php echo $drug_item['reorder_level']; ?></td>
                                                <td>
                                                    <?php if ($drug_item['stock_quantity'] == 0): ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Low Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($low_stock_drugs) > 10): ?>
                                <div class="text-center mt-3">
                                    <a href="manage_inventory.php" class="btn btn-outline-warning btn-sm">View All</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Expiring Soon (30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($expiring_drugs)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h6>No Expiring Drugs</h6>
                                <p class="text-muted">No drugs are expiring within 30 days.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Batch</th>
                                            <th>Quantity</th>
                                            <th>Expiry Date</th>
                                            <th>Days Left</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($expiring_drugs, 0, 10) as $drug_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($drug_item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($drug_item['batch_number']); ?></td>
                                                <td><?php echo $drug_item['quantity']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($drug_item['expiry_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $days_left = (strtotime($drug_item['expiry_date']) - time()) / (60 * 60 * 24);
                                                    $badge_class = $days_left <= 7 ? 'danger' : ($days_left <= 14 ? 'warning' : 'info');
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo round($days_left); ?> days
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($expiring_drugs) > 10): ?>
                                <div class="text-center mt-3">
                                    <a href="manage_inventory.php" class="btn btn-outline-warning btn-sm">View All</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Reports -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Sales Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h6 class="text-muted">Today's Sales</h6>
                                <h4 class="text-success">ETB <?php echo number_format($today_sales['total_revenue'] ?? 0, 0); ?></h4>
                                <small class="text-muted"><?php echo $today_sales['total_orders'] ?? 0; ?> orders</small>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted">Monthly Sales</h6>
                                <h4 class="text-primary">ETB <?php echo number_format($monthly_sales['total_revenue'] ?? 0, 0); ?></h4>
                                <small class="text-muted"><?php echo $monthly_sales['total_orders'] ?? 0; ?> orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-prescription me-2"></i>Prescription Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h6 class="text-muted">Pending</h6>
                                <h4 class="text-warning"><?php echo $prescription_stats['pending'] ?? 0; ?></h4>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted">Approved</h6>
                                <h4 class="text-success"><?php echo $prescription_stats['approved'] ?? 0; ?></h4>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted">Rejected</h6>
                                <h4 class="text-danger"><?php echo $prescription_stats['rejected'] ?? 0; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Order Status Summary</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Pending <span class="badge bg-warning"> <?php echo $order_status_counts['pending']; ?> </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Approved <span class="badge bg-primary"> <?php echo $order_status_counts['approved']; ?> </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Completed <span class="badge bg-success"> <?php echo $order_status_counts['completed']; ?> </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rejected <span class="badge bg-danger"> <?php echo $order_status_counts['rejected']; ?> </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Selling Drugs (This Month)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($top_drugs)): ?>
                            <p class="text-muted">No sales data for this month.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Drug</th>
                                            <th>Strength</th>
                                            <th>Sold</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_drugs as $drug): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($drug['drug_name']); ?></td>
                                                <td><?php echo htmlspecialchars($drug['strength']); ?></td>
                                                <td><?php echo (int)$drug['total_quantity']; ?></td>
                                                <td>ETB <?php echo number_format($drug['total_revenue'], 2); ?></td>
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
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Generate Custom Report</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="custom_report">
                <div class="col-md-3">
                    <label for="custom_report_type" class="form-label">Report Type</label>
                    <select name="custom_report_type" id="custom_report_type" class="form-select" required>
                        <option value="">Select...</option>
                        <option value="sales" <?php if ($custom_report_type === 'sales') echo 'selected'; ?>>Sales</option>
                        <option value="inventory" <?php if ($custom_report_type === 'inventory') echo 'selected'; ?>>Inventory</option>
                        <option value="prescriptions" <?php if ($custom_report_type === 'prescriptions') echo 'selected'; ?>>Prescriptions</option>
                        <option value="top_drugs" <?php if ($custom_report_type === 'top_drugs') echo 'selected'; ?>>Top Selling Drugs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="custom_start" class="form-label">Start Date</label>
                    <input type="date" name="custom_start" id="custom_start" class="form-control" value="<?php echo htmlspecialchars($_POST['custom_start'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="custom_end" class="form-label">End Date</label>
                    <input type="date" name="custom_end" id="custom_end" class="form-control" value="<?php echo htmlspecialchars($_POST['custom_end'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Generate</button>
                </div>
            </form>
            <?php if (isset($custom_report)): ?>
                <div class="mt-4">
                    <?php if ($custom_report_type === 'sales' && is_array($custom_report)): ?>
                        <h6>Sales Report</h6>
                        <ul>
                            <li><strong>Total Sales:</strong> <?php echo $custom_report['total_sales'] ?? 0; ?></li>
                            <li><strong>Total Revenue:</strong> ETB <?php echo number_format($custom_report['total_revenue'] ?? 0, 2); ?></li>
                            <li><strong>Total Tax:</strong> ETB <?php echo number_format($custom_report['total_tax'] ?? 0, 2); ?></li>
                            <li><strong>Total Discount:</strong> ETB <?php echo number_format($custom_report['total_discount'] ?? 0, 2); ?></li>
                            <li><strong>Average Sale:</strong> ETB <?php echo number_format($custom_report['average_sale'] ?? 0, 2); ?></li>
                        </ul>
                    <?php elseif ($custom_report_type === 'inventory' && is_array($custom_report)): ?>
                        <h6>Inventory Report</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Generic</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($custom_report as $drug): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($drug['name']); ?></td>
                                            <td><?php echo htmlspecialchars($drug['generic_name']); ?></td>
                                            <td><?php echo htmlspecialchars($drug['category_name'] ?? ''); ?></td>
                                            <td><?php echo $drug['stock_quantity']; ?></td>
                                            <td>ETB <?php echo number_format($drug['price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($custom_report_type === 'prescriptions' && is_array($custom_report)): ?>
                        <h6>Prescriptions Report</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Symptoms</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($custom_report as $rx): ?>
                                        <tr>
                                            <td><?php echo $rx['id']; ?></td>
                                            <td><?php echo htmlspecialchars($rx['customer_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($rx['symptoms']); ?></td>
                                            <td><span class="badge bg-<?php
                                                                        switch ($rx['status']) {
                                                                            case 'pending':
                                                                                echo 'warning';
                                                                                break;
                                                                            case 'approved':
                                                                                echo 'success';
                                                                                break;
                                                                            case 'rejected':
                                                                                echo 'danger';
                                                                                break;
                                                                            default:
                                                                                echo 'secondary';
                                                                        }
                                                                        ?>"><?php echo ucfirst($rx['status']); ?></span></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($rx['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($custom_report_type === 'top_drugs' && is_array($custom_report)): ?>
                        <h6>Top Selling Drugs</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Drug</th>
                                        <th>Strength</th>
                                        <th>Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($custom_report as $drug): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($drug['drug_name']); ?></td>
                                            <td><?php echo htmlspecialchars($drug['strength']); ?></td>
                                            <td><?php echo (int)$drug['total_quantity']; ?></td>
                                            <td>ETB <?php echo number_format($drug['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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