<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('customer');

$user = $auth->getCurrentUser();
$drug = new Drug();

$error = '';
$success = '';

$drug_id = $_GET['id'] ?? '';
if (empty($drug_id)) {
    header('Location: search.php');
    exit();
}

$drug_details = $drug->getDrugById($drug_id);
if (!$drug_details) {
    header('Location: search.php?error=drug_not_found');
    exit();
}

// Handle add to cart
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $quantity = $_POST['quantity'] ?? 1;

    if ($quantity > 0 && $quantity <= $drug_details['stock_quantity']) {
        // Add to session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$drug_id])) {
            $_SESSION['cart'][$drug_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$drug_id] = [
                'drug_id' => $drug_id,
                'name' => $drug_details['name'],
                'strength' => $drug_details['strength'],
                'price' => $drug_details['price'],
                'quantity' => $quantity,
                'requires_prescription' => $drug_details['is_prescription_required']
            ];
        }
        $success = 'Drug added to cart successfully!';
    } else {
        $error = 'Invalid quantity or insufficient stock';
    }
}

// Get related drugs
$related_drugs = $drug->getAllDrugs('', $drug_details['category_id'], 4);
$related_drugs = array_filter($related_drugs, function ($item) use ($drug_id) {
    return $item['id'] != $drug_id;
});

// Set page title for header
$page_title = htmlspecialchars($drug_details['name']);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="container mt-4">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="search.php">Search</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($drug_details['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Drug Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="drug-image mb-3">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-1"><?php echo htmlspecialchars($drug_details['name']); ?></h3>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($drug_details['generic_name']); ?></p>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($drug_details['strength']); ?> - <?php echo htmlspecialchars($drug_details['dosage_form']); ?></p>
                                </div>
                                <div class="text-end">
                                    <div class="price-tag mb-2">ETB <?php echo number_format($drug_details['price'], 2); ?></div>
                                    <span class="stock-badge badge bg-<?php echo $drug_details['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $drug_details['stock_quantity']; ?> in stock
                                    </span>
                                </div>
                            </div>

                            <?php if ($drug_details['is_prescription_required']): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Prescription Required:</strong> This medication requires a valid prescription from a healthcare provider.
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <h5>Description</h5>
                                <p><?php echo htmlspecialchars($drug_details['description']); ?></p>
                            </div>

                            <?php if ($drug_details['stock_quantity'] > 0): ?>
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity"
                                                value="1" min="1" max="<?php echo $drug_details['stock_quantity']; ?>">
                                        </div>
                                        <div class="col-md-8 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    This medication is currently out of stock. Please check back later or contact our pharmacy.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Manufacturer</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($drug_details['manufacturer']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Category</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($drug_details['category_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <?php if ($drug_details['is_prescription_required']): ?>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-stethoscope me-2"></i>Prescription Information</h6>
                            <p class="mb-0">This medication requires a prescription. Please upload your prescription or consult with our pharmacist for assistance.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="search.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-search me-2"></i>Search More Drugs
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-robot me-2"></i>Get AI Recommendations
                    </a>
                    <a href="profile.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-upload me-2"></i>Upload Prescription
                    </a>
                </div>
            </div>

            <!-- Related Drugs -->
            <?php if (!empty($related_drugs)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-pills me-2"></i>Related Medications</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach (array_slice($related_drugs, 0, 3) as $related_drug): ?>
                            <div class="related-drug">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($related_drug['name']); ?></h6>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($related_drug['strength']); ?></p>
                                        <small class="text-primary fw-bold">ETB <?php echo number_format($related_drug['price'], 2); ?></small>
                                    </div>
                                    <a href="drug_details.php?id=<?php echo $related_drug['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>