<?php
require_once '../classes/Auth.php';
require_once '../classes/Prescription.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$prescription = new Prescription();
$drug = new Drug();

$error = '';
$success = '';

// Handle prescription actions
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
    } elseif ($_POST['action'] === 'dispense') {
        $result = $prescription->markAsDispensed($prescription_id);
        if ($result['success']) {
            $success = 'Prescription marked as dispensed.';
        } else {
            $error = $result['message'];
        }
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get prescriptions based on filters
if ($status) {
    $prescriptions = $prescription->getPrescriptionsByStatus($status);
} else {
    $prescriptions = $prescription->getAllPrescriptions();
}

// Get prescription statistics
$prescription_stats = $prescription->getPrescriptionStats();

// Set page title for header
$page_title = 'Prescriptions Management';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-prescription me-2"></i>Prescriptions Management</h2>
            <div class="text-muted">Manage and review prescriptions</div>
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
                                <p class="mb-0">Total</p>
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
                                <p class="mb-0">Pending</p>
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
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $prescription_stats['dispensed_prescriptions'] ?? 0; ?></h4>
                                <p class="mb-0">Dispensed</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-pills fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="dispensed" <?php echo $status === 'dispensed' ? 'selected' : ''; ?>>Dispensed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by customer name or symptoms..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Prescriptions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Prescriptions</h5>
            </div>
            <div class="card-body">
                <?php if (empty($prescriptions)): ?>
                    <p class="text-muted">No prescriptions found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Symptoms</th>
                                    <th>Status</th>
                                    <th>Pharmacist</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptions as $prescription_item): ?>
                                    <tr>
                                        <td>#<?php echo $prescription_item['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prescription_item['customer_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($prescription_item['customer_phone']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($prescription_item['symptoms']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                                    switch ($prescription_item['status']) {
                                                                        case 'pending':
                                                                            echo 'warning';
                                                                            break;
                                                                        case 'approved':
                                                                            echo 'success';
                                                                            break;
                                                                        case 'rejected':
                                                                            echo 'danger';
                                                                            break;
                                                                        case 'dispensed':
                                                                            echo 'info';
                                                                            break;
                                                                        default:
                                                                            echo 'secondary';
                                                                    }
                                                                    ?>"><?php echo ucfirst($prescription_item['status']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($prescription_item['pharmacist_name'] ?? 'Not assigned'); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#prescriptionModal<?php echo $prescription_item['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($prescription_item['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $prescription_item['id']; ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $prescription_item['id']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php elseif ($prescription_item['status'] === 'approved'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="dispense">
                                                        <input type="hidden" name="prescription_id" value="<?php echo $prescription_item['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-info">
                                                            <i class="fas fa-pills"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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

<!-- Prescription Detail Modals -->
<?php foreach ($prescriptions as $prescription_item): ?>
    <div class="modal fade" id="prescriptionModal<?php echo $prescription_item['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prescription #<?php echo $prescription_item['id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($prescription_item['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($prescription_item['customer_phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($prescription_item['customer_email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Prescription Details</h6>
                            <p><strong>Status:</strong>
                                <span class="badge bg-<?php
                                                        switch ($prescription_item['status']) {
                                                            case 'pending':
                                                                echo 'warning';
                                                                break;
                                                            case 'approved':
                                                                echo 'success';
                                                                break;
                                                            case 'rejected':
                                                                echo 'danger';
                                                                break;
                                                            case 'dispensed':
                                                                echo 'info';
                                                                break;
                                                            default:
                                                                echo 'secondary';
                                                        }
                                                        ?>"><?php echo ucfirst($prescription_item['status']); ?></span>
                            </p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?></p>
                            <p><strong>Pharmacist:</strong> <?php echo htmlspecialchars($prescription_item['pharmacist_name'] ?? 'Not assigned'); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Symptoms</h6>
                            <p><?php echo nl2br(htmlspecialchars($prescription_item['symptoms'])); ?></p>
                        </div>
                    </div>
                    <?php if ($prescription_item['diagnosis']): ?>
                        <div class="row">
                            <div class="col-12">
                                <h6>Diagnosis</h6>
                                <p><?php echo nl2br(htmlspecialchars($prescription_item['diagnosis'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($prescription_item['notes']): ?>
                        <div class="row">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <p><?php echo nl2br(htmlspecialchars($prescription_item['notes'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <?php if ($prescription_item['status'] === 'pending'): ?>
        <div class="modal fade" id="approveModal<?php echo $prescription_item['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Prescription #<?php echo $prescription_item['id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="prescription_id" value="<?php echo $prescription_item['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Diagnosis</label>
                                <textarea class="form-control" name="diagnosis" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal<?php echo $prescription_item['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Prescription #<?php echo $prescription_item['id']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="prescription_id" value="<?php echo $prescription_item['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Rejection Reason</label>
                                <textarea class="form-control" name="notes" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>