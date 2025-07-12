<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Prescription.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$drug = new Drug();
$prescription = new Prescription();
$ai_service = new AIService();

$error = '';
$success = '';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - Sky Pharmacy</title>
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
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            <a href="prescriptions.php" class="nav-link">
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
                    <h4 class="mb-0">Pharmacist Dashboard</h4>
                    <p class="mb-0 text-muted">Welcome back, Dr. <?php echo htmlspecialchars($user['full_name']); ?></p>
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
                            <div class="stat-label">Pending Prescriptions</div>
                        </div>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $prescription_stats['approved'] ?? 0; ?></div>
                            <div class="stat-label">Approved Today</div>
                        </div>
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo count($low_stock_drugs); ?></div>
                            <div class="stat-label">Low Stock Items</div>
                        </div>
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-number"><?php echo $ai_usage_stats[0]['total_requests'] ?? 0; ?></div>
                            <div class="stat-label">AI Requests Today</div>
                        </div>
                        <i class="fas fa-robot stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="prescriptions.php" class="btn btn-primary w-100">
                                    Review Prescriptions
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="manage_inventory.php" class="btn btn-primary w-100">
                                    Manage Inventory
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="ai_insights.php" class="btn btn-primary w-100">
                                    AI Insights
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-primary w-100">
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Prescriptions and Low Stock -->
        <div class="row">
            <!-- Pending Prescriptions -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Pending Prescriptions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_prescriptions)): ?>
                            <p class="text-muted">No pending prescriptions.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($pending_prescriptions, 0, 5) as $prescription_item): ?>
                                <div class="card mb-3 prescription-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($prescription_item['customer_name']); ?></h6>
                                                <p class="text-muted mb-1"><?php echo htmlspecialchars($prescription_item['symptoms']); ?></p>
                                                <small class="text-muted">Submitted: <?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <button class="btn btn-sm btn-success" onclick="approvePrescription(<?php echo $prescription_item['id']; ?>)">
                                                    Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectPrescription(<?php echo $prescription_item['id']; ?>)">
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($pending_prescriptions) > 5): ?>
                                <a href="prescriptions.php" class="btn btn-sm btn-outline-primary">View All Prescriptions</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Low Stock Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_drugs)): ?>
                            <p class="text-muted">No low stock items.</p>
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

    <!-- Prescription Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="prescription_id" id="approve_prescription_id">
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
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

    <!-- Prescription Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="prescription_id" id="reject_prescription_id">
                        <div class="mb-3">
                            <label for="reject_notes" class="form-label">Rejection Reason</label>
                            <textarea class="form-control" name="notes" id="reject_notes" rows="3" required></textarea>
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

        // Prescription approval functions
        function approvePrescription(prescriptionId) {
            document.getElementById('approve_prescription_id').value = prescriptionId;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectPrescription(prescriptionId) {
            document.getElementById('reject_prescription_id').value = prescriptionId;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
</body>
</html> 