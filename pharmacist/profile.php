<?php
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (!empty($full_name) && !empty($email)) {
        $result = $auth->updateUserProfile($user['id'], $full_name, $email, $phone, $address);
        if ($result['success']) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = $auth->getCurrentUser();
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Full name and email are required';
    }
}

// Handle password change
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $result = $auth->changePassword($user['id'], $current_password, $new_password);
            if ($result['success']) {
                $success = 'Password changed successfully!';
            } else {
                $error = $result['message'];
            }
        } else {
            $error = 'New passwords do not match';
        }
    } else {
        $error = 'All password fields are required';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .profile-header { background: #2c3e50; color: white; border-radius: 15px 15px 0 0; }
        .nav-tabs .nav-link { border-radius: 10px 10px 0 0; margin-right: 5px; }
        .nav-tabs .nav-link.active { background: #3498db; color: white; border: none; }
        .tab-content { padding-top: 20px; }
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
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>Reports
            </a>
            <a href="profile.php" class="nav-link active">
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
                    <h4 class="mb-0">My Profile</h4>
                    <p class="mb-0 text-muted">Manage your account information and settings</p>
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

            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Card -->
                    <div class="card">
                        <div class="profile-header p-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-md fa-4x"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <p class="mb-0"><?php echo ucfirst($user['role']); ?></p>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-muted">Member Since</h6>
                                    <p class="mb-0"><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted">Status</h6>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Profile Tabs -->
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                        <i class="fas fa-user-edit me-2"></i>Profile Information
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                        <i class="fas fa-lock me-2"></i>Change Password
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="profileTabsContent">
                                <!-- Profile Information Tab -->
                                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                    <form method="POST" class="mt-4">
                                        <input type="hidden" name="action" value="update_profile">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="full_name" class="form-label">
                                                    <i class="fas fa-user me-2"></i>Full Name
                                                </label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="username" class="form-label">
                                                    <i class="fas fa-at me-2"></i>Username
                                                </label>
                                                <input type="text" class="form-control" id="username" 
                                                       value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                                <small class="text-muted">Username cannot be changed</small>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">
                                                    <i class="fas fa-envelope me-2"></i>Email Address
                                                </label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label">
                                                    <i class="fas fa-phone me-2"></i>Phone Number
                                                </label>
                                                <input type="tel" class="form-control" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Address
                                            </label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Change Password Tab -->
                                <div class="tab-pane fade" id="password" role="tabpanel">
                                    <form method="POST" class="mt-4">
                                        <input type="hidden" name="action" value="change_password">
                                        
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">
                                                <i class="fas fa-key me-2"></i>Current Password
                                            </label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="new_password" class="form-label">
                                                    <i class="fas fa-lock me-2"></i>New Password
                                                </label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="confirm_password" class="form-label">
                                                    <i class="fas fa-lock me-2"></i>Confirm New Password
                                                </label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                        </div>
                                        

                                        
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-key me-2"></i>Change Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
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