<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>Sky Pharmacy</h4>
        <p>Cashier Panel</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="process_orders.php" class="nav-link <?php echo $current_page === 'process_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card me-2"></i>Process Orders
        </a>
        <a href="sales_report.php" class="nav-link <?php echo $current_page === 'sales_report.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar me-2"></i>Sales Report
        </a>
        <a href="print_receipts.php" class="nav-link <?php echo $current_page === 'print_receipts.php' ? 'active' : ''; ?>">
            <i class="fas fa-print me-2"></i>Print Receipts
        </a>
        <a href="profile.php" class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user me-2"></i>Profile
        </a>

        <hr class="my-3">
        <a href="../logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </nav>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>