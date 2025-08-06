<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$drug = new Drug();

$error = '';
$success = '';

// Handle drug actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_drug':
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
                'is_active' => 1
            ];
            if (!empty($data['name']) && $data['category_id']) {
                $result = $drug->addDrug($data);
                if ($result['success']) {
                    $success = 'Drug added successfully!';
                } else {
                    $error = $result['message'] ?? 'Failed to add drug.';
                }
            } else {
                $error = 'Drug name and category are required.';
            }
            break;
        case 'delete_drug':
            $id = $_POST['drug_id'] ?? '';
            if (!empty($id)) {
                $result = $drug->deleteDrug($id);
                if ($result['success']) {
                    $success = 'Drug deleted successfully!';
                } else {
                    $error = $result['message'] ?? 'Failed to delete drug.';
                }
            } else {
                $error = 'Drug ID is required.';
            }
            break;
    }
}

$categories = $drug->getAllCategories();
$drugs = $drug->getAllDrugs();

// Set page title for header
$page_title = 'Inventory Management';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-pills me-2"></i>Inventory Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDrugModal">
                <i class="fas fa-plus me-2"></i>Add New Drug
            </button>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Inventory Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Drug Inventory</h5>
            </div>
            <div class="card-body">
                <?php if (empty($drugs)): ?>
                    <p class="text-muted">No drugs found in inventory.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Generic</th>
                                    <th>Category</th>
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
                                                <span class="badge bg-warning ms-1">Rx</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($drug_item['generic_name']); ?></td>
                                        <td><?php echo htmlspecialchars($drug_item['category_name'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    if ($drug_item['stock_quantity'] <= $drug_item['reorder_level']) echo 'danger';
                                                                    elseif ($drug_item['stock_quantity'] <= $drug_item['reorder_level'] * 2) echo 'warning';
                                                                    else echo 'success';
                                                                    ?>">
                                                <?php echo $drug_item['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>ETB <?php echo number_format($drug_item['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $drug_item['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $drug_item['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_drug.php?id=<?php echo $drug_item['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_drug">
                                                    <input type="hidden" name="drug_id" value="<?php echo $drug_item['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($drug_item['name']); ?>?')">
                                                        <i class="fas fa-trash"></i>
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

<!-- Add Drug Modal -->
<div class="modal fade" id="addDrugModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Drug</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_drug">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Drug Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Generic Name</label>
                            <input type="text" class="form-control" name="generic_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" name="manufacturer">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dosage Form</label>
                            <input type="text" class="form-control" name="dosage_form">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Strength</label>
                            <input type="text" class="form-control" name="strength">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock_quantity" value="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Selling Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cost Price</label>
                            <input type="number" class="form-control" name="cost_price" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" class="form-control" name="reorder_level" value="10" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_prescription_required" value="1">
                        <label class="form-check-label">Requires Prescription</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Drug</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>