<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Prescription.php';
require_once '../classes/AIService.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$drug = new Drug();
$prescription = new Prescription();
$ai_service = new AIService();
$order = new Order();

$error = '';
$success = '';

// Handle order approval/rejection
if (
    $_POST && isset($_POST['action']) && isset($_POST['order_id']) &&
    in_array($_POST['action'], ['approve_order', 'reject_order'])
) {
    $order_id = $_POST['order_id'];
    if ($_POST['action'] === 'approve_order') {
        $result = $order->updateOrderStatus($order_id, 'approved');
        if ($result['success']) {
            $success = 'Order approved!';
        } else {
            $error = $result['message'] ?? 'Failed to approve order.';
        }
    } elseif ($_POST['action'] === 'reject_order') {
        $result = $order->updateOrderStatus($order_id, 'rejected');
        if ($result['success']) {
            $success = 'Order rejected.';
        } else {
            $error = $result['message'] ?? 'Failed to reject order.';
        }
    }
}

// Handle prescription approval/rejection
if ($_POST && isset($_POST['action'])) {
    $prescription_id = $_POST['prescription_id'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if ($_POST['action'] === 'approve') {
        $result = $prescription->approvePrescription($prescription_id, $user['id'], $diagnosis, $notes);
        if ($result['success']) {
            $success = 'Prescription approved successfully!';
        } else {
            $error = $result['message'];
        }
    } elseif ($_POST['action'] === 'reject') {
        $result = $prescription->rejectPrescription($prescription_id, $user['id'], $notes);
        if ($result['success']) {
            $success = 'Prescription rejected.';
        } else {
            $error = $result['message'];
        }
    }
}

// Get pending prescriptions
$pending_prescriptions = $prescription->getPendingPrescriptions();

// Get low stock drugs
$low_stock_drugs = $drug->getLowStockDrugs();

// Get prescription statistics
$prescription_stats = $prescription->getPrescriptionStats();

// Get AI usage statistics
$ai_usage_stats = $ai_service->getAIUsageStats();

// Get pending orders
$pending_orders = $order->getAllOrders('pending', 5);

// Set page title for header
$page_title = 'Dashboard';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt me-2"></i>Pharmacist Dashboard</h2>
            <div class="text-muted">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $prescription_stats['total_prescriptions'] ?? 0; ?></h4>
                                <p class="mb-0">Total Prescriptions</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-prescription fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $prescription_stats['pending_prescriptions'] ?? 0; ?></h4>
                                <p class="mb-0">Pending Reviews</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $prescription_stats['approved_prescriptions'] ?? 0; ?></h4>
                                <p class="mb-0">Approved</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count($low_stock_drugs); ?></h4>
                                <p class="mb-0">Low Stock Alerts</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="prescriptions.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-prescription me-2"></i>Review Prescriptions
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="orders.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-clipboard-check me-2"></i>Approve Orders
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="ai_suggest.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-robot me-2"></i>AI Drug Suggestions
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="manage_inventory.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-pills me-2"></i>Manage Inventory
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders Section -->
        <?php if (!empty($pending_orders)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pending Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($pending_orders as $order_item): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-warning">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="card-title">Order #<?php echo $order_item['id']; ?></h6>
                                                        <p class="card-text">
                                                            <strong>Customer:</strong> <?php echo htmlspecialchars($order_item['customer_name']); ?><br>
                                                            <strong>Phone:</strong> <?php echo htmlspecialchars($order_item['customer_phone']); ?><br>
                                                            <strong>Total:</strong> ETB <?php echo number_format($order_item['total_amount'], 2); ?>
                                                        </p>
                                                    </div>
                                                    <div class="text-end">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="approve_order">
                                                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm mb-1">
                                                                <i class="fas fa-check me-1"></i>Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="reject_order">
                                                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times me-1"></i>Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center">
                                <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-prescription me-2"></i>Recent Prescriptions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_prescriptions)): ?>
                            <p class="text-muted">No pending prescriptions.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($pending_prescriptions, 0, 5) as $prescription_item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Prescription #<?php echo $prescription_item['id']; ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($prescription_item['customer_name']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning">Pending</span>
                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($prescription_item['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="prescriptions.php" class="btn btn-sm btn-outline-primary">View All Prescriptions</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_drugs)): ?>
                            <p class="text-muted">No low stock alerts.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($low_stock_drugs, 0, 5) as $drug_item): ?>
                                <div class="alert alert-warning mb-2">
                                    <strong><?php echo htmlspecialchars($drug_item['name']); ?></strong>
                                    <br>Stock: <?php echo $drug_item['stock_quantity']; ?> units
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($low_stock_drugs) > 5): ?>
                                <a href="manage_inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>