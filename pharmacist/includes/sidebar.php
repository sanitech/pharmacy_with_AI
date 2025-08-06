<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>Sky Pharmacy</h4>
        <p>Pharmacist Panel</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="prescriptions.php" class="nav-link <?php echo $current_page === 'prescriptions.php' ? 'active' : ''; ?>">
            <i class="fas fa-prescription me-2"></i>Prescriptions
        </a>
        <a href="orders.php" class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check me-2"></i>Approve Orders
        </a>
        <a href="manage_inventory.php" class="nav-link <?php echo $current_page === 'manage_inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-pills me-2"></i>Manage Inventory
        </a>
        <a href="ai_suggest.php" class="nav-link <?php echo $current_page === 'ai_suggest.php' ? 'active' : ''; ?>">
            <i class="fas fa-robot me-2"></i>AI Drug Suggestion
        </a>
        <a href="ai_insights.php" class="nav-link <?php echo $current_page === 'ai_insights.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line me-2"></i>AI Insights
        </a>
        <a href="reports.php" class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar me-2"></i>Reports
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