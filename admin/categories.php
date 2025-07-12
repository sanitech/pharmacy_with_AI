<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$drug = new Drug();

$error = '';
$success = '';

// Handle category actions
if ($_POST && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_category':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($name)) {
                    $result = $drug->addCategory($name, $description);
                    if ($result['success']) {
                        $success = 'Category added successfully!';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'Category name is required.';
                }
                break;
            case 'update_category':
                $category_id = $_POST['category_id'] ?? '';
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($category_id) && !empty($name)) {
                    $result = $drug->updateCategory($category_id, $name, $description);
                    if ($result['success']) {
                        $success = 'Category updated successfully!';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'Category ID and name are required.';
                }
                break;
            case 'delete_category':
                $category_id = $_POST['category_id'] ?? '';
                if (!empty($category_id)) {
                    $result = $drug->deleteCategory($category_id);
                    if ($result['success']) {
                        $success = 'Category deleted successfully!';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'Category ID is required.';
                }
                break;
        }
    } catch (Exception $e) {
        $error = 'Error processing request: ' . $e->getMessage();
    }
}

// Get categories with error handling
try {
    $categories = $drug->getAllCategoriesWithCount();
} catch (Exception $e) {
    $error = 'Error loading categories: ' . $e->getMessage();
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Categories - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .category-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .category-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .category-count {
            background: #2c3e50;
            color: white;
            border-radius: 20px;
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
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
            <a href="categories.php" class="nav-link active">
                <i class="fas fa-tags"></i>Categories
            </a>
            <a href="reports.php" class="nav-link">
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
                    <h4 class="mb-0">Drug Categories Management</h4>
                    <p class="mb-0 text-muted">Create and manage drug categories for better organization</p>
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

            <!-- Add Category Button -->
            <div class="row mb-4">
                <div class="col-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-2"></i>Add New Category
                    </button>
                </div>
            </div>

            <!-- Categories List -->
            <div class="row">
                <div class="col-12">
                    <?php if (empty($categories) || isset($categories['error'])): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No categories found</h5>
                            <p class="text-muted">Start by adding your first drug category.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="card category-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h6 class="mb-1">
                                                <i class="fas fa-tag me-2"></i>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="category-count">
                                                <i class="fas fa-pills me-1"></i>
                                                <?php echo $category['drugs_count'] ?? 0; ?> drugs
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                Created: <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                                    data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['id']; ?>"
                                                    title="Edit Category">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['id']; ?>"
                                                    title="Delete Category">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Category Modal -->
                            <div class="modal fade" id="editCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>
                                                Edit Category
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update_category">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Category Name</label>
                                                    <input type="text" class="form-control" name="name" 
                                                           value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="3" 
                                                              placeholder="Optional description for this category"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Category</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Category Modal -->
                            <div class="modal fade" id="deleteCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Delete Category
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                
                                                <div class="alert alert-danger">
                                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Warning</h6>
                                                    <p class="mb-0">Are you sure you want to delete the category <strong><?php echo htmlspecialchars($category['name']); ?></strong>?</p>
                                                    <small class="text-muted">This action cannot be undone.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete Category</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter category name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Optional description for this category"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
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