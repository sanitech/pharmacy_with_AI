<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$drug = new Drug();

$error = '';
$success = '';

// Handle form submissions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_drug':
            $drug_data = [
                'name' => $_POST['name'],
                'generic_name' => $_POST['generic_name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'],
                'dosage_form' => $_POST['dosage_form'],
                'strength' => $_POST['strength'],
                'manufacturer' => $_POST['manufacturer'],
                'price' => $_POST['price'],
                'cost_price' => $_POST['cost_price'],
                'stock_quantity' => $_POST['stock_quantity'],
                'reorder_level' => $_POST['reorder_level'],
                'is_prescription_required' => isset($_POST['is_prescription_required']) ? 1 : 0
            ];
            
            $result = $drug->addDrug($drug_data);
            if ($result['success']) {
                $success = 'Drug added successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'update_drug':
            $drug_id = $_POST['drug_id'];
            $drug_data = [
                'name' => $_POST['name'],
                'generic_name' => $_POST['generic_name'],
                'category_id' => $_POST['category_id'],
                'description' => $_POST['description'],
                'dosage_form' => $_POST['dosage_form'],
                'strength' => $_POST['strength'],
                'manufacturer' => $_POST['manufacturer'],
                'price' => $_POST['price'],
                'cost_price' => $_POST['cost_price'],
                'stock_quantity' => $_POST['stock_quantity'],
                'reorder_level' => $_POST['reorder_level'],
                'is_prescription_required' => isset($_POST['is_prescription_required']) ? 1 : 0
            ];
            
            $result = $drug->updateDrug($drug_id, $drug_data);
            if ($result['success']) {
                $success = 'Drug updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'update_stock':
            $drug_id = $_POST['drug_id'];
            $quantity = $_POST['quantity'];
            $operation = $_POST['operation'];
            
            $result = $drug->updateStock($drug_id, $quantity, $operation);
            if ($result['success']) {
                $success = 'Stock updated successfully!';
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';

// Get drugs with filters
$drugs = $drug->getAllDrugs($search, $category_id, 100);
$categories = $drug->getAllCategories();
$low_stock_drugs = $drug->getLowStockDrugs(10);
$expiring_drugs = $drug->getExpiringDrugs(30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        .stock-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .search-box {
            background: white;
            border-radius: 25px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .modal-header {
            background: #2c3e50;
            color: white;
        }
        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .alert-card {
            border-left: 4px solid #dc3545;
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
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
            <a href="manage_inventory.php" class="nav-link active">
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
                    <h4 class="mb-0">Inventory Management</h4>
                    <p class="mb-0 text-muted">Manage drug inventory, stock levels, and product information</p>
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

            <!-- Add New Drug Button -->
            <div class="row mb-4">
                <div class="col-12 text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDrugModal">
                        <i class="fas fa-plus me-2"></i>Add New Drug
                    </button>
                </div>
            </div>

            <!-- Alerts Section -->
            <?php if (!empty($low_stock_drugs) || !empty($expiring_drugs)): ?>
                <div class="row mb-4">
                    <?php if (!empty($low_stock_drugs)): ?>
                        <div class="col-md-6">
                            <div class="alert-card">
                                <h6><i class="fas fa-exclamation-triangle text-danger me-2"></i>Low Stock Alert</h6>
                                <p class="mb-2">The following drugs are running low on stock:</p>
                                <ul class="mb-0">
                                    <?php foreach ($low_stock_drugs as $drug_item): ?>
                                        <li><?php echo htmlspecialchars($drug_item['name']); ?> - <?php echo $drug_item['stock_quantity']; ?> units</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($expiring_drugs)): ?>
                        <div class="col-md-6">
                            <div class="alert-card">
                                <h6><i class="fas fa-clock text-warning me-2"></i>Expiring Soon</h6>
                                <p class="mb-2">The following drugs are expiring soon:</p>
                                <ul class="mb-0">
                                    <?php foreach ($expiring_drugs as $drug_item): ?>
                                        <li><?php echo htmlspecialchars($drug_item['name']); ?> - Expires: <?php echo date('M d, Y', strtotime($drug_item['expiry_date'])); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="search-box mb-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">
                            <i class="fas fa-search me-2"></i>Search Drugs
                        </label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name, generic name, or manufacturer...">
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label">
                            <i class="fas fa-filter me-2"></i>Category
                        </label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Drugs Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Drug Inventory</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($drugs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <h5>No Drugs Found</h5>
                            <p class="text-muted">No drugs match your search criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Drug Name</th>
                                        <th>Generic Name</th>
                                        <th>Category</th>
                                        <th>Strength</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($drugs as $drug_item): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($drug_item['name']); ?></strong>
                                                <?php if ($drug_item['is_prescription_required']): ?>
                                                    <span class="badge bg-warning ms-2">Rx Required</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($drug_item['generic_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($drug_item['category_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($drug_item['strength'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php 
                                                $stock_class = $drug_item['stock_quantity'] <= $drug_item['reorder_level'] ? 'danger' : 
                                                    ($drug_item['stock_quantity'] <= $drug_item['reorder_level'] * 2 ? 'warning' : 'success');
                                                ?>
                                                <span class="stock-badge badge bg-<?php echo $stock_class; ?>">
                                                    <?php echo $drug_item['stock_quantity']; ?> units
                                                </span>
                                            </td>
                                            <td>ETB <?php echo number_format($drug_item['price'], 2); ?></td>
                                            <td>
                                                <?php if ($drug_item['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewDrugModal<?php echo $drug_item['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editDrugModal<?php echo $drug_item['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateStockModal<?php echo $drug_item['id']; ?>">
                                                        <i class="fas fa-boxes"></i>
                                                    </button>
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