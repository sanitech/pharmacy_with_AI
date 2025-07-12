<?php
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();

$error = '';
$success = '';

// Handle settings updates
if ($_POST && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_profile':
                $full_name = trim($_POST['full_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                if (empty($full_name)) {
                    $error = 'Full name is required!';
                } elseif (empty($email)) {
                    $error = 'Email is required!';
                } else {
                    $result = $auth->updateUserProfile($user['id'], $full_name, $email, $phone);
                    if ($result['success']) {
                        $success = 'Profile updated successfully!';
                        // Refresh user data
                        $user = $auth->getCurrentUser();
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password must be at least 6 characters long!';
                } else {
                    $result = $auth->changePassword($user['id'], $current_password, $new_password);
                    if ($result['success']) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    } catch (Exception $e) {
        $error = 'Error processing request: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .profile-card {
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-primary {
            background: #3498db;
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        .password-strength {
            margin-top: 10px;
        }
        .strength-bar {
            height: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-medium { background-color: #ffc107; width: 50%; }
        .strength-strong { background-color: #28a745; width: 75%; }
        .strength-very-strong { background-color: #20c997; width: 100%; }
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
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>Reports
            </a>
            <a href="settings.php" class="nav-link active">
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
                    <h4 class="mb-0">Account Settings</h4>
                    <p class="mb-0 text-muted">Manage your personal information and security</p>
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
                <!-- Profile Information -->
                <div class="col-md-6">
                    <div class="profile-card">
                        <div class="text-center mb-4">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h5>Profile Information</h5>
                            <p class="text-muted">Update your personal details</p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-user me-2"></i>Full Name
                                </label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       placeholder="Enter phone number">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Role
                                </label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" 
                                       readonly style="background-color: #f8f9fa;">
                                <small class="text-muted">Role cannot be changed</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-6">
                    <div class="profile-card">
                        <div class="text-center mb-4">
                            <div class="profile-avatar">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h5>Change Password</h5>
                            <p class="text-muted">Update your account password</p>
                        </div>
                        
                        <form method="POST" id="passwordForm">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-key me-2"></i>Current Password
                                </label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>New Password
                                </label>
                                <input type="password" class="form-control" name="new_password" 
                                       id="newPassword" required minlength="6">
                                <div class="password-strength">
                                    <div class="strength-bar" id="strengthBar"></div>
                                    <small class="text-muted" id="strengthText">Password strength</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-check-circle me-2"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="profile-card">
                        <h6><i class="fas fa-info-circle me-2"></i>Account Information</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Account Created</small>
                                <p class="mb-0"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Last Login</small>
                                <p class="mb-0"><?php echo date('M d, Y H:i', strtotime($user['last_login'] ?? $user['created_at'])); ?></p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Account Status</small>
                                <p class="mb-0">
                                    <span class="badge bg-success">Active</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'strength-bar';
            
            switch (strength) {
                case 0:
                case 1:
                    strengthBar.classList.add('strength-weak');
                    text = 'Very Weak';
                    break;
                case 2:
                    strengthBar.classList.add('strength-medium');
                    text = 'Weak';
                    break;
                case 3:
                    strengthBar.classList.add('strength-medium');
                    text = 'Medium';
                    break;
                case 4:
                    strengthBar.classList.add('strength-strong');
                    text = 'Strong';
                    break;
                case 5:
                    strengthBar.classList.add('strength-very-strong');
                    text = 'Very Strong';
                    break;
            }
            
            strengthText.textContent = text;
        });

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