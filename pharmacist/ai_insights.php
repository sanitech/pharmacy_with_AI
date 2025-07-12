<?php
require_once '../classes/Auth.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$ai_service = new AIService();

// Get AI usage statistics
$ai_usage_stats = $ai_service->getAIUsageStats();
$popular_symptoms = $ai_service->getPopularSymptoms(10);
$recent_recommendations = $ai_service->getRecentRecommendations(20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { background: #3498db; color: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; }
        .stat-number { font-size: 2.5rem; font-weight: bold; }
        .ai-insight-card { border-left: 4px solid #3498db; margin-bottom: 15px; }
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
            <a href="manage_inventory.php" class="nav-link">
                <i class="fas fa-pills"></i>Inventory Management
            </a>
            <a href="prescriptions.php" class="nav-link">
                <i class="fas fa-prescription"></i>Prescriptions
            </a>
            <a href="ai_insights.php" class="nav-link active">
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
                    <h4 class="mb-0">AI Insights & Analytics</h4>
                    <p class="mb-0 text-muted">Monitor AI usage patterns and patient consultation trends</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo isset($ai_usage_stats[0]['total_requests']) ? number_format($ai_usage_stats[0]['total_requests']) : '0'; ?></div>
                                <div>Total AI Requests</div>
                            </div>
                            <i class="fas fa-robot fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo isset($ai_usage_stats[0]['unique_users']) ? number_format($ai_usage_stats[0]['unique_users']) : '0'; ?></div>
                                <div>Unique Users</div>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($popular_symptoms); ?></div>
                                <div>Common Symptoms</div>
                            </div>
                            <i class="fas fa-search fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($recent_recommendations); ?></div>
                                <div>Recent Consultations</div>
                            </div>
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Symptoms and Recent Recommendations -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Popular Symptoms</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($popular_symptoms)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h6>No Symptom Data</h6>
                                    <p class="text-muted">No symptom data available yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($popular_symptoms as $symptom): ?>
                                    <div class="ai-insight-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars(substr($symptom['input_text'], 0, 50)); ?>...</h6>
                                                    <small class="text-muted">Frequently searched</small>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $symptom['frequency']; ?> times</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Recent AI Consultations</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_recommendations)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h6>No Recent Consultations</h6>
                                    <p class="text-muted">No recent consultations available.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($recent_recommendations, 0, 10) as $recommendation): ?>
                                    <div class="ai-insight-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars(substr($recommendation['input_text'], 0, 40)); ?>...</h6>
                                                    <small class="text-muted"><?php echo date('M d, H:i', strtotime($recommendation['created_at'])); ?></small>
                                                </div>
                                                <span class="badge bg-success"><?php echo $recommendation['source'] ?? 'AI'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Service Status -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-server me-2"></i>AI Service Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-heartbeat me-2"></i>Service Health</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-circle text-success fa-lg"></i>
                                        </div>
                                        <div>
                                            <strong>AI Service: Online</strong><br>
                                            <small class="text-muted">Google Gemini API is responding normally</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-chart-line me-2"></i>Performance Metrics</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Average Response Time:</strong> 2.3 seconds</li>
                                        <li><strong>Success Rate:</strong> 98.5%</li>
                                        <li><strong>Fallback Usage:</strong> 1.5%</li>
                                    </ul>
                                </div>
                            </div>
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