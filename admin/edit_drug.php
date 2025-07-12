<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$drug = new Drug();

$error = '';
$success = '';

// Get drug ID from URL
$drug_id = $_GET['id'] ?? null;

if (!$drug_id) {
    header('Location: inventory.php?error=invalid_drug');
    exit();
}

// Get drug details
$drug_details = $drug->getDrugById($drug_id);

if (!$drug_details || isset($drug_details['error'])) {
    header('Location: inventory.php?error=drug_not_found');
    exit();
}

// Handle form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_drug') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'generic_name' => trim($_POST['generic_name'] ?? ''),
        'category_id' => $_POST['category_id'] ?? null,
        'description' => trim($_POST['description'] ?? ''),
        'dosage_form' => trim($_POST['dosage_form'] ?? ''),
        'strength' => trim($_POST['strength'] ?? ''),
        'manufacturer' => trim($_POST['manufacturer'] ?? ''),
        'price' => $_POST['price'] ?? 0,
        'cost_price' => $_POST['cost_price'] ?? 0,
        'stock_quantity' => $_POST['stock_quantity'] ?? 0,
        'reorder_level' => $_POST['reorder_level'] ?? 10,
        'is_prescription_required' => isset($_POST['is_prescription_required']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    if (!empty($data['name']) && $data['category_id']) {
        $result = $drug->updateDrug($drug_id, $data);
        if ($result['success']) {
            $success = 'Drug updated successfully!';
            // Refresh drug details
            $drug_details = $drug->getDrugById($drug_id);
        } else {
            $error = $result['message'] ?? 'Failed to update drug.';
        }
    } else {
        $error = 'Drug name and category are required.';
    }
}

$categories = $drug->getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Drug - Sky Pharmacy</title>
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
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>Users Management
            </a>
            <a href="inventory.php" class="nav-link active">
                <i class="fas fa-pills"></i>Inventory
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
                    <h4 class="mb-0">Edit Drug</h4>
                    <p class="mb-0 text-muted">Update drug information</p>
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

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="inventory.php">Inventory</a></li>
                    <li class="breadcrumb-item active">Edit Drug</li>
                </ol>
            </nav>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Drug - <?php echo htmlspecialchars($drug_details['name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_drug">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-pills me-2"></i>Drug Name
                                        </label>
                                        <input type="text" class="form-control" name="name" 
                                               value="<?php echo htmlspecialchars($drug_details['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tag me-2"></i>Generic Name
                                        </label>
                                        <input type="text" class="form-control" name="generic_name" 
                                               value="<?php echo htmlspecialchars($drug_details['generic_name']); ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-tags me-2"></i>Category
                                        </label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php if ($drug_details['category_id'] == $cat['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-industry me-2"></i>Manufacturer
                                        </label>
                                        <input type="text" class="form-control" name="manufacturer" 
                                               value="<?php echo htmlspecialchars($drug_details['manufacturer']); ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-capsules me-2"></i>Dosage Form
                                        </label>
                                        <input type="text" class="form-control" name="dosage_form" 
                                               value="<?php echo htmlspecialchars($drug_details['dosage_form']); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-weight-hanging me-2"></i>Strength
                                        </label>
                                        <input type="text" class="form-control" name="strength" 
                                               value="<?php echo htmlspecialchars($drug_details['strength']); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-boxes me-2"></i>Stock Quantity
                                        </label>
                                        <input type="number" class="form-control" name="stock_quantity" 
                                               value="<?php echo $drug_details['stock_quantity']; ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-dollar-sign me-2"></i>Selling Price
                                        </label>
                                        <input type="number" class="form-control" name="price" step="0.01" 
                                               value="<?php echo $drug_details['price']; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-coins me-2"></i>Cost Price
                                        </label>
                                        <input type="number" class="form-control" name="cost_price" step="0.01" 
                                               value="<?php echo $drug_details['cost_price']; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Reorder Level
                                        </label>
                                        <input type="number" class="form-control" name="reorder_level" 
                                               value="<?php echo $drug_details['reorder_level']; ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-2"></i>Description
                                    </label>
                                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($drug_details['description']); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="is_prescription_required" value="1" 
                                                   <?php if ($drug_details['is_prescription_required']) echo 'checked'; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-prescription me-2"></i>Requires Prescription
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                   <?php if ($drug_details['is_active']) echo 'checked'; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-check-circle me-2"></i>Active
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="inventory.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Drug
                                    </button>
                                </div>
                            </form>
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