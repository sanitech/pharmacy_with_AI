<?php
require_once '../classes/Auth.php';
require_once '../classes/AIService.php';
require_once '../classes/Prescription.php';
require_once '../classes/Drug.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$ai_service = new AIService();
$prescription = new Prescription();
$drug = new Drug();

$error = '';
$success = '';

// Get AI usage statistics
$ai_usage_stats = $ai_service->getAIUsageStats();
$popular_symptoms = $ai_service->getPopularSymptoms(10);
$prescription_stats = $prescription->getPrescriptionStats();
$low_stock_drugs = $drug->getLowStockDrugs(5);

// Set page title for header
$page_title = 'AI Insights';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line me-2"></i>AI Insights</h2>
            <div class="text-muted">Analytics and AI-powered insights</div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- AI Usage Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $ai_usage_stats['total_requests'] ?? 0; ?></h4>
                                <p class="mb-0">Total AI Requests</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-robot fa-2x"></i>
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
                                <h4><?php echo $ai_usage_stats['successful_requests'] ?? 0; ?></h4>
                                <p class="mb-0">Successful</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
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
                                <h4><?php echo $ai_usage_stats['popular_symptoms_count'] ?? 0; ?></h4>
                                <p class="mb-0">Popular Symptoms</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-bar fa-2x"></i>
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
                                <h4><?php echo $ai_usage_stats['avg_response_time'] ?? 0; ?>s</h4>
                                <p class="mb-0">Avg Response</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Symptoms Analysis -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Popular Symptoms</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($popular_symptoms)): ?>
                            <p class="text-muted">No symptom data available.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Symptom</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_symptoms = array_sum(array_column($popular_symptoms, 'count'));
                                        foreach ($popular_symptoms as $symptom):
                                            $percentage = $total_symptoms > 0 ? round(($symptom['count'] / $total_symptoms) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($symptom['symptom']); ?></td>
                                                <td><?php echo $symptom['count']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                                            <?php echo $percentage; ?>%
                                                        </div>
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
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-prescription me-2"></i>Prescription Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <h4 class="text-primary"><?php echo $prescription_stats['total_prescriptions'] ?? 0; ?></h4>
                                    <p class="text-muted">Total Prescriptions</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <h4 class="text-warning"><?php echo $prescription_stats['pending_prescriptions'] ?? 0; ?></h4>
                                    <p class="text-muted">Pending Review</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <h4 class="text-success"><?php echo $prescription_stats['approved_prescriptions'] ?? 0; ?></h4>
                                    <p class="text-muted">Approved</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <h4 class="text-danger"><?php echo $prescription_stats['rejected_prescriptions'] ?? 0; ?></h4>
                                    <p class="text-muted">Rejected</p>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($prescription_stats['approval_rate'])): ?>
                            <div class="mt-3">
                                <h6>Approval Rate</h6>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $prescription_stats['approval_rate']; ?>%">
                                        <?php echo $prescription_stats['approval_rate']; ?>%
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>AI Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="fas fa-pills fa-3x text-primary mb-3"></i>
                                        <h6>Stock Management</h6>
                                        <p class="text-muted">Based on AI analysis, consider restocking these items:</p>
                                        <?php if (!empty($low_stock_drugs)): ?>
                                            <ul class="list-unstyled">
                                                <?php foreach (array_slice($low_stock_drugs, 0, 3) as $drug_item): ?>
                                                    <li class="text-danger"><?php echo htmlspecialchars($drug_item['name']); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-success">All drugs are well stocked!</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                        <h6>Trend Analysis</h6>
                                        <p class="text-muted">Most common symptoms this month:</p>
                                        <?php if (!empty($popular_symptoms)): ?>
                                            <ul class="list-unstyled">
                                                <?php foreach (array_slice($popular_symptoms, 0, 3) as $symptom): ?>
                                                    <li><?php echo htmlspecialchars($symptom['symptom']); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-muted">No trend data available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                                        <h6>Performance</h6>
                                        <p class="text-muted">AI system performance metrics:</p>
                                        <ul class="list-unstyled">
                                            <li><strong>Response Time:</strong> <?php echo $ai_usage_stats['avg_response_time'] ?? 'N/A'; ?>s</li>
                                            <li><strong>Success Rate:</strong> <?php echo $ai_usage_stats['success_rate'] ?? 'N/A'; ?>%</li>
                                            <li><strong>Uptime:</strong> <?php echo $ai_usage_stats['uptime'] ?? 'N/A'; ?>%</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="ai_suggest.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-robot me-2"></i>Get AI Suggestions
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="prescriptions.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-prescription me-2"></i>Review Prescriptions
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="manage_inventory.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-pills me-2"></i>Manage Inventory
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-chart-bar me-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>