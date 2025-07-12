<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('cashier');

$user = $auth->getCurrentUser();
$order = new Order();

$error = '';
$success = '';

// Date filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get sales transactions
$sales = $order->getSalesTransactions($start_date, $end_date);

// Get summary data
$summary = $order->getSalesReport($start_date, $end_date);

// Handle errors
if (isset($sales['error'])) {
    $error = $sales['error'];
    $sales = [];
}

if (isset($summary['error'])) {
    $error = $summary['error'];
    $summary = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Sky Pharmacy</title>
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
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="process_orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>Process Orders
            </a>
            <a href="print_receipts.php" class="nav-link">
                <i class="fas fa-print"></i>Print Receipts
            </a>
            <a href="sales_report.php" class="nav-link active">
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
                    <h4 class="mb-0">Sales Report</h4>
                    <p class="mb-0 text-muted">View and analyze sales performance</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>
        <!-- Sales Report Content (replace this with your actual report table/chart) -->
        <div class="container mt-4">
            <div class="card">
                <div class="card-header" style="background: var(--primary-color); color: var(--white-color);">
                    <h5 class="mb-0">Sales Summary</h5>
                </div>
                <div class="card-body">
                    <!-- Example Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Total Orders</th>
                                    <th>Total Sales (ETB)</th>
                                    <th>Cash</th>
                                    <th>Card</th>
                                    <th>Mobile Money</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example data, replace with PHP loop -->
                                <tr>
                                    <td>2025-06-29</td>
                                    <td>12</td>
                                    <td>2,500.00</td>
                                    <td>1,200.00</td>
                                    <td>800.00</td>
                                    <td>500.00</td>
                                </tr>
                                <tr>
                                    <td>2025-06-28</td>
                                    <td>9</td>
                                    <td>1,900.00</td>
                                    <td>1,000.00</td>
                                    <td>600.00</td>
                                    <td>300.00</td>
                                </tr>
                                <!-- End example data -->
                            </tbody>
                        </table>
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