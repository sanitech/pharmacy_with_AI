<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Order.php';
require_once '../classes/Prescription.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$drug = new Drug();
$order = new Order();
$prescription = new Prescription();
$ai_service = new AIService();

$error = '';
$success = '';
$report_data = null;
$report_type = '';

// Handle report generation
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'generate_report') {
    $report_type = $_POST['report_type'];
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    
    try {
        switch ($report_type) {
            case 'sales_report':
                $report_data = $order->getSalesReport($date_from, $date_to);
                break;
            case 'inventory_report':
                $report_data = $drug->getInventoryReport();
                break;
            case 'prescription_report':
                $report_data = $prescription->getPrescriptionReport($date_from, $date_to);
                break;
            case 'user_activity_report':
                $report_data = $auth->getUserActivityReport($date_from, $date_to);
                break;
            default:
                throw new Exception('Invalid report type');
        }
        
        if ($report_data && !isset($report_data['error'])) {
            $success = 'Report generated successfully!';
        } else {
            $error = 'No data available for the selected criteria.';
        }
    } catch (Exception $e) {
        $error = 'Failed to generate report: ' . $e->getMessage();
    }
}

// Get quick statistics for dashboard
try {
    $total_drugs = $drug->getTotalDrugs();
    $low_stock_drugs = $drug->getLowStockDrugs(10);
    $expiring_drugs = $drug->getExpiringDrugs(30);
    $total_orders = $order->getTotalOrders();
    $total_sales = $order->getTotalSales();
} catch (Exception $e) {
    $error = 'Error loading statistics: ' . $e->getMessage();
    $total_drugs = 0;
    $low_stock_drugs = [];
    $expiring_drugs = [];
    $total_orders = 0;
    $total_sales = 0;
}
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
        .stats-card {
            background: #2c3e50;
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        .report-type-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .report-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .report-type-card.selected {
            border-color: #3498db;
            background-color: #f8f9ff;
        }
        .modal-header {
            background: #2c3e50;
            color: white;
        }
        .modal-header .btn-close {
            filter: invert(1);
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
            <a href="orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>Orders
            </a>
            <a href="categories.php" class="nav-link">
                <i class="fas fa-tags"></i>Categories
            </a>
            <a href="reports.php" class="nav-link active">
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
                    <h4 class="mb-0">Reports & Analytics</h4>
                    <p class="mb-0 text-muted">Generate comprehensive reports and view system analytics</p>
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

            <!-- Quick Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?php echo number_format($total_drugs); ?></h3>
                                <p>Total Drugs</p>
                            </div>
                            <i class="fas fa-pills fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?php echo number_format($total_orders); ?></h3>
                                <p>Total Orders</p>
                            </div>
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>ETB <?php echo number_format($total_sales, 2); ?></h3>
                                <p>Total Sales</p>
                            </div>
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?php echo count($low_stock_drugs); ?></h3>
                                <p>Low Stock Items</p>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Generation -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Generate Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="report-type-card p-3 text-center" onclick="selectReport('sales_report')">
                                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                        <h6>Sales Report</h6>
                                        <small class="text-muted">Revenue and order analysis</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-type-card p-3 text-center" onclick="selectReport('inventory_report')">
                                        <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                                        <h6>Inventory Report</h6>
                                        <small class="text-muted">Stock levels and alerts</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-type-card p-3 text-center" onclick="selectReport('prescription_report')">
                                        <i class="fas fa-prescription fa-3x text-info mb-3"></i>
                                        <h6>Prescription Report</h6>
                                        <small class="text-muted">Prescription analytics</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="report-type-card p-3 text-center" onclick="selectReport('user_activity_report')">
                                        <i class="fas fa-users fa-3x text-warning mb-3"></i>
                                        <h6>User Activity</h6>
                                        <small class="text-muted">User engagement data</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Results -->
            <?php if ($report_data && !isset($report_data['error'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                <?php echo ucwords(str_replace('_', ' ', $report_type)); ?> Report
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <?php foreach (array_keys($report_data[0] ?? []) as $header): ?>
                                                <th><?php echo ucwords(str_replace('_', ' ', $header)); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $value): ?>
                                                    <td><?php echo htmlspecialchars($value); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="generate_report">
                        <input type="hidden" name="report_type" id="selectedReportType">
                        
                        <div class="mb-3">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" id="date_from">
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" id="date_to">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectReport(reportType) {
            document.getElementById('selectedReportType').value = reportType;
            
            // Update visual selection
            document.querySelectorAll('.report-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Show modal
            new bootstrap.Modal(document.getElementById('generateReportModal')).show();
        }

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