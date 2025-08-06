<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>Sky Pharmacy</h4>
        <p>Admin Panel</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="users.php" class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users me-2"></i>Users Management
        </a>
        <a href="inventory.php" class="nav-link <?php echo $current_page === 'inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-pills me-2"></i>Inventory
        </a>
        <a href="orders.php" class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart me-2"></i>Orders
        </a>
        <a href="categories.php" class="nav-link <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags me-2"></i>Categories
        </a>
        <a href="reports.php" class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar me-2"></i>Reports
        </a>
        <a href="ai_suggest.php" class="nav-link <?php echo $current_page === 'ai_suggest.php' ? 'active' : ''; ?>">
            <i class="fas fa-robot me-2"></i>AI Drug Suggestion
        </a>
        <a href="settings.php" class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog me-2"></i>Settings
        </a>

        <hr class="my-3">
        <a href="../logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </nav>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>