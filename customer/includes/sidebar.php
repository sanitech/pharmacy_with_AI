<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>Sky Pharmacy</h4>
        <p>Customer Panel</p>
    </div>
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="search.php" class="nav-link <?php echo $current_page === 'search.php' ? 'active' : ''; ?>">
            <i class="fas fa-search me-2"></i>Search Drugs
        </a>
        <a href="orders.php" class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart me-2"></i>My Orders
        </a>
        <a href="prescriptions.php" class="nav-link <?php echo $current_page === 'prescriptions.php' ? 'active' : ''; ?>">
            <i class="fas fa-prescription me-2"></i>My Prescriptions
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