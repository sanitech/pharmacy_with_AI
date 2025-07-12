<?php
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$auth_class = new Auth();

$error = '';
$success = '';

// Handle user actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_user':
            $user_data = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'full_name' => $_POST['full_name'],
                'role' => $_POST['role'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];

            $result = $auth_class->createUser($user_data);
            if ($result['success']) {
                $success = 'User created successfully!';
            } else {
                $error = $result['message'];
            }
            break;

        case 'update_user':
            $user_id = $_POST['user_id'];
            $user_data = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'full_name' => $_POST['full_name'],
                'role' => $_POST['role'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address']
            ];

            $result = $auth_class->updateUser($user_id, $user_data);
            if ($result['success']) {
                $success = 'User updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;

        case 'toggle_status':
            $user_id = $_POST['user_id'];
            $result = $auth_class->toggleUserStatus($user_id);
            if ($result['success']) {
                $success = 'User status updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;

        case 'reset_password':
            $user_id = $_POST['user_id'];
            $new_password = $_POST['new_password'];

            if (!empty($new_password) && strlen($new_password) >= 6) {
                $result = $auth_class->resetPassword($user_id, $new_password);
                if ($result['success']) {
                    $success = 'Password reset successfully!';
                } else {
                    $error = $result['message'];
                }
            } else {
                $error = 'Password must be at least 6 characters long';
            }
            break;
    }
}

// Get all users
$users = $auth_class->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                Dashboard
            </a>
            <a href="users.php" class="nav-link active">
                Users Management
            </a>
            <a href="inventory.php" class="nav-link">
                Inventory
            </a>
            <a href="orders.php" class="nav-link">
                Orders
            </a>
            <a href="categories.php" class="nav-link">
                Categories
            </a>
            <a href="reports.php" class="nav-link">
                Reports
            </a>
            <a href="settings.php" class="nav-link">
                Settings
            </a>
           
            <hr class="my-3">
            <a href="../logout.php" class="nav-link text-danger">
                Logout
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
                    <h4 class="mb-0">User Management</h4>
                    <p class="mb-0 text-muted">Manage system users and their roles</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>User Management</h2>
                <p class="text-muted">Manage system users and their roles</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    Add New User
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>All Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <p class="text-muted">No users found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user_item['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php
                                                                                echo $user_item['role'] === 'admin' ? 'danger' : ($user_item['role'] === 'pharmacist' ? 'primary' : ($user_item['role'] === 'cashier' ? 'success' : 'info'));
                                                                                ?>">
                                                        <?php echo ucfirst($user_item['role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($user_item['phone']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $user_item['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $user_item['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user_item['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editUserModal<?php echo $user_item['id']; ?>">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#resetPasswordModal<?php echo $user_item['id']; ?>">
                                                            Reset Password
                                                        </button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-<?php echo $user_item['is_active'] ? 'danger' : 'success'; ?>"
                                                                onclick="return confirm('Are you sure you want to <?php echo $user_item['is_active'] ? 'deactivate' : 'activate'; ?> this user?')">
                                                                <?php echo $user_item['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="customer">Customer</option>
                                <option value="cashier">Cashier</option>
                                <option value="pharmacist">Pharmacist</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modals -->
    <?php foreach ($users as $user_item): ?>
    <div class="modal fade" id="editUserModal<?php echo $user_item['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User - <?php echo htmlspecialchars($user_item['full_name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user_item['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_item['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user_item['full_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="customer" <?php if ($user_item['role'] === 'customer') echo 'selected'; ?>>Customer</option>
                                <option value="cashier" <?php if ($user_item['role'] === 'cashier') echo 'selected'; ?>>Cashier</option>
                                <option value="pharmacist" <?php if ($user_item['role'] === 'pharmacist') echo 'selected'; ?>>Pharmacist</option>
                                <option value="admin" <?php if ($user_item['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user_item['phone'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user_item['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal<?php echo $user_item['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password - <?php echo htmlspecialchars($user_item['full_name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="user_id" value="<?php echo $user_item['id']; ?>">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

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