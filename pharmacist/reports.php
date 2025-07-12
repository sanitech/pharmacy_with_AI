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

// Get report data
$total_drugs = $drug->getTotalDrugs();
$low_stock_drugs = $drug->getLowStockDrugs();
$expiring_drugs = $drug->getExpiringDrugs(30);
$prescription_stats = $prescription->getPrescriptionStats();
$today_sales = $order->getSalesReport(date('Y-m-d'), date('Y-m-d'));
$monthly_sales = $order->getSalesReport(date('Y-m-01'), date('Y-m-t'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .stat-card { background: #2c3e50; color: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; }
        .stat-number { font-size: 2.5rem; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Pharmacist Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="manage_inventory.php" class="nav-link">
                <i class="fas fa-pills"></i>Inventory Management
            </a>
            <a href="prescriptions.php" class="nav-link">
                <i class="fas fa-prescription"></i>Prescriptions
            </a>
            <a href="ai_insights.php" class="nav-link">
                <i class="fas fa-brain"></i>AI Insights
            </a>
            <a href="reports.php" class="nav-link active">
                <i class="fas fa-chart-bar"></i>Reports
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

            <!-- Report Generator -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Generate Custom Reports</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="action" value="generate_report">
                                
                                <div class="col-md-4">
                                    <label for="report_type" class="form-label">
                                        <i class="fas fa-chart-bar me-2"></i>Report Type
                                    </label>
                                    <select class="form-select" id="report_type" name="report_type" required>
                                        <option value="">Select Report Type</option>
                                        <option value="inventory">Inventory Report</option>
                                        <option value="prescriptions">Prescription Report</option>
                                        <option value="sales">Sales Report</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Start Date
                                    </label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>End Date
                                    </label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-download me-2"></i>Generate
                                    </button>
                                </div>
                            </form>
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