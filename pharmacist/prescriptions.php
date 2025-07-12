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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions Management - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
            <a href="prescriptions.php" class="nav-link active">
                <i class="fas fa-prescription"></i>Prescriptions
            </a>
            <a href="manage_inventory.php" class="nav-link">
                <i class="fas fa-pills"></i>Manage Inventory
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
                    <h4 class="mb-0">Prescription Management</h4>
                    <p class="mb-0 text-muted">Review and manage customer prescriptions</p>
                </div>
                <div class="user-info">
                    <span class="user-name">Dr. <?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

   

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['pending'] ?? 0; ?></div>
                            <div>Pending</div>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['approved'] ?? 0; ?></div>
                            <div>Approved</div>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['rejected'] ?? 0; ?></div>
                            <div>Rejected</div>
                        </div>
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['dispensed'] ?? 0; ?></div>
                            <div>Dispensed</div>
                        </div>
                        <i class="fas fa-pills fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Search patient name..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="dispensed" <?php echo $status === 'dispensed' ? 'selected' : ''; ?>>Dispensed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="prescriptions.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-refresh me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescriptions List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Prescriptions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($prescriptions)): ?>
                            <p class="text-muted">No prescriptions found matching your criteria.</p>
                        <?php else: ?>
                            <?php foreach ($prescriptions as $prescription_item): ?>
                                <div class="card prescription-card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="card-title">Prescription #<?php echo $prescription_item['id']; ?></h6>
                                                <p class="card-text">
                                                    <strong>Patient:</strong> <?php echo htmlspecialchars($prescription_item['customer_name']); ?><br>
                                                    <strong>Phone:</strong> <?php echo htmlspecialchars($prescription_item['customer_phone']); ?><br>
                                                    <strong>Symptoms:</strong> <?php echo htmlspecialchars($prescription_item['symptoms']); ?><br>
                                                    <strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?>
                                                    <?php if ($prescription_item['diagnosis']): ?>
                                                        <br><strong>Diagnosis:</strong> <?php echo htmlspecialchars($prescription_item['diagnosis']); ?>
                                                    <?php endif; ?>
                                                    <?php if ($prescription_item['notes']): ?>
                                                        <br><strong>Notes:</strong> <?php echo htmlspecialchars($prescription_item['notes']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($prescription_item['prescription_file']): ?>
                                                    <a href="../uploads/prescriptions/<?php echo $prescription_item['prescription_file']; ?>"
                                                        target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-file me-1"></i>View Prescription
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-center justify-content-end">
                                                <div class="text-end">
                                                    <div class="mb-2">
                                                        <span class="status-badge badge bg-<?php
                                                                                            echo $prescription_item['status'] === 'pending' ? 'warning' : ($prescription_item['status'] === 'approved' ? 'success' : ($prescription_item['status'] === 'rejected' ? 'danger' : 'info'));
                                                                                            ?>">
                                                            <?php echo ucfirst($prescription_item['status']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="btn-group">
                                                        <?php if ($prescription_item['status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-success btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#approveModal<?php echo $prescription_item['id']; ?>">
                                                                <i class="fas fa-check me-1"></i>Approve
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal<?php echo $prescription_item['id']; ?>">
                                                                <i class="fas fa-times me-1"></i>Reject
                                                            </button>
                                                        <?php elseif ($prescription_item['status'] === 'approved'): ?>
                                                            <button type="button" class="btn btn-info btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#dispenseModal<?php echo $prescription_item['id']; ?>">
                                                                <i class="fas fa-pills me-1"></i>Dispense
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewModal<?php echo $prescription_item['id']; ?>">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approve Modal -->
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
                                                        <label for="diagnosis" class="form-label">Diagnosis</label>
                                                        <textarea class="form-control" name="diagnosis" rows="3" required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="notes" class="form-label">Notes (Optional)</label>
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
                                                        <label for="notes" class="form-label">Reason for Rejection</label>
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

                                <!-- Dispense Modal -->
                                <div class="modal fade" id="dispenseModal<?php echo $prescription_item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Dispense Prescription #<?php echo $prescription_item['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="dispense">
                                                    <input type="hidden" name="prescription_id" value="<?php echo $prescription_item['id']; ?>">
                                                    <p>Are you sure you want to mark this prescription as dispensed?</p>
                                                    <p><strong>Patient:</strong> <?php echo htmlspecialchars($prescription_item['customer_name']); ?></p>
                                                    <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($prescription_item['diagnosis']); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-info">Mark as Dispensed</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $prescription_item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Prescription Details #<?php echo $prescription_item['id']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Patient Information</h6>
                                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($prescription_item['customer_name']); ?></p>
                                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($prescription_item['customer_phone']); ?></p>
                                                        <p><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Prescription Details</h6>
                                                        <p><strong>Status:</strong>
                                                            <span class="badge bg-<?php
                                                                                    echo $prescription_item['status'] === 'pending' ? 'warning' : ($prescription_item['status'] === 'approved' ? 'success' : ($prescription_item['status'] === 'rejected' ? 'danger' : 'info'));
                                                                                    ?>">
                                                                <?php echo ucfirst($prescription_item['status']); ?>
                                                            </span>
                                                        </p>
                                                        <?php if ($prescription_item['pharmacist_name']): ?>
                                                            <p><strong>Reviewed by:</strong> <?php echo htmlspecialchars($prescription_item['pharmacist_name']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($prescription_item['updated_at']): ?>
                                                            <p><strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($prescription_item['updated_at'])); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h6>Symptoms</h6>
                                                        <p><?php echo htmlspecialchars($prescription_item['symptoms']); ?></p>
                                                    </div>
                                                </div>
                                                <?php if ($prescription_item['diagnosis']): ?>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Diagnosis</h6>
                                                            <p><?php echo htmlspecialchars($prescription_item['diagnosis']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($prescription_item['notes']): ?>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <h6>Notes</h6>
                                                            <p><?php echo htmlspecialchars($prescription_item['notes']); ?></p>
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
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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