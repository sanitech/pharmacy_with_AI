<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Prescription.php';
require_once '../classes/Order.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('customer');

$user = $auth->getCurrentUser();
$drug = new Drug();
$prescription = new Prescription();
$order = new Order();
$ai_service = new AIService();

$error = '';
$success = '';

// Handle AI recommendation request
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'ai_recommend') {
    $symptoms = $_POST['symptoms'] ?? '';
    if (!empty($symptoms)) {
        // Use the new getSuggestions method
        $ai_result = $ai_service->getSuggestions($symptoms, $user['id']);
        if ($ai_result['success']) {
            $ai_suggestions = $ai_result['suggestions'];
            $available_drugs = $ai_result['available_drugs'] ?? [];
            $ai_source = $ai_result['source'] ?? 'ai';
        } else {
            $error = $ai_result['message'] ?? 'Failed to get AI recommendations';
        }
    } else {
        $error = 'Please describe your symptoms';
    }
}

// Handle prescription upload
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'upload_prescription') {
    $symptoms = $_POST['prescription_symptoms'] ?? '';
    $prescription_file = null;

    if (!empty($symptoms)) {
        // Handle file upload if needed
        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/prescriptions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['prescription_file']['name'], PATHINFO_EXTENSION);
            $file_name = 'prescription_' . $user['id'] . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], $file_path)) {
                $prescription_file = $file_name;
            }
        }

        $result = $prescription->uploadPrescription($user['id'], $symptoms, $prescription_file);
        if ($result['success']) {
            $success = 'Prescription uploaded successfully! Our pharmacist will review it soon.';
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please describe your symptoms';
    }
}

// Get user's prescriptions and orders
$user_prescriptions = $prescription->getPrescriptionsByCustomer($user['id']);
$user_orders = $order->getOrdersByCustomer($user['id']);

// Get drug categories for search
$categories = $drug->getAllCategories();

// Get recent drugs
$recent_drugs = $drug->getAllDrugs('', null, 8);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Customer Portal</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link active">
                Dashboard
            </a>
            <a href="search.php" class="nav-link">
                Search Drugs
            </a>
            <a href="prescriptions.php" class="nav-link">
                My Prescriptions
            </a>
            <a href="orders.php" class="nav-link">
                My Orders
            </a>
            <a href="profile.php" class="nav-link">
                Profile
            </a>
            <hr class="my-3">
            <a href="../logout.php" class="nav-link text-danger">
                Logout
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
                    <h4 class="mb-0">Customer Dashboard</h4>
                    <p class="mb-0 text-muted">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?></p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- AI Recommendation Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>AI Health Assistant</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="ai_recommend">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="symptoms" class="form-label">Describe your symptoms:</label>
                                    <textarea class="form-control" id="symptoms" name="symptoms" rows="3"
                                        placeholder="e.g., headache, fever, cough, stomach pain..."><?php echo $_POST['symptoms'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Get Recommendations
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendations Results -->
        <?php if (isset($ai_suggestions)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="ai-recommend-box card p-0" style="border: 1.5px solid var(--accent-color); box-shadow: 0 2px 8px var(--shadow-color);">
                        <div class="card-header" style="background: var(--white-color); color: var(--primary-color); border-bottom: 1px solid var(--border-color);">
                            <h5 style="margin:0; font-weight:600;"><i class="fas fa-robot me-2 text-primary"></i>AI Recommendations</h5>
                        </div>
                        <div class="card-body" style="background: var(--white-color);">
                            <div class="ai-response mb-3" style="background: #f4f8fb; border-radius: 8px; padding: 1.25rem 1rem; border: 1px solid var(--border-color); display: flex; align-items: flex-start; gap: 1rem;">
                                <div style="font-size: 2rem; color: var(--accent-color); flex-shrink:0;"><i class="fas fa-robot"></i></div>
                                <div>
                                    <div style="font-size: 1.1rem; font-weight: 500; color: var(--primary-color); margin-bottom: 0.25rem;">AI Health Assistant says:</div>
                                    <div style="font-size: 1.05rem; color: var(--text-color); line-height: 1.6;">
                                        <?php echo nl2br(htmlspecialchars($ai_suggestions)); ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($available_drugs)): ?>
                                <h6 class="mt-3 mb-2" style="color: var(--primary-color); font-weight: 600;">Recommended Medications:</h6>
                                <div class="row">
                                    <?php foreach ($available_drugs as $drug_item): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card drug-card" style="border: 1px solid var(--border-color); background: var(--white-color);">
                                                <div class="card-body">
                                                    <h6 class="card-title" style="color: var(--primary-color); font-weight: 600;"><?php echo htmlspecialchars($drug_item['name']); ?></h6>
                                                    <p class="card-text text-muted"><?php echo htmlspecialchars($drug_item['description']); ?></p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-primary fw-bold">ETB <?php echo number_format($drug_item['price'], 2); ?></span>
                                                        <a href="drug_details.php?id=<?php echo $drug_item['id']; ?>" class="btn btn-sm btn-primary">
                                                            View Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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
                                <a href="search.php" class="btn btn-primary w-100">
                                    Search Drugs
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="prescriptions.php" class="btn btn-primary w-100">
                                    Upload Prescription
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="orders.php" class="btn btn-primary w-100">
                                    My Orders
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="profile.php" class="btn btn-primary w-100">
                                    My Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <!-- Recent Prescriptions -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Prescriptions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($user_prescriptions)): ?>
                            <p class="text-muted">No prescriptions uploaded yet.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($user_prescriptions, 0, 3) as $prescription_item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong><?php echo htmlspecialchars($prescription_item['symptoms']); ?></strong>
                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($prescription_item['created_at'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge badge-<?php
                                                                    echo $prescription_item['status'] === 'approved' ? 'success' : ($prescription_item['status'] === 'pending' ? 'warning' : 'danger');
                                                                    ?>"><?php echo ucfirst($prescription_item['status']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="prescriptions.php" class="btn btn-sm btn-outline-primary">View All Prescriptions</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($user_orders)): ?>
                            <p class="text-muted">No orders placed yet.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($user_orders, 0, 3) as $order_item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                                    <div>
                                        <strong>Order #<?php echo $order_item['id']; ?></strong>
                                        <br><small class="text-muted"><?php echo date('M d, Y', strtotime($order_item['created_at'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge badge-<?php
                                                                    echo $order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'pending' ? 'warning' : 'danger');
                                                                    ?>"><?php echo ucfirst($order_item['status']); ?></span>
                                        <br><small class="text-muted">ETB <?php echo number_format($order_item['total_amount'], 2); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Drugs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Popular Drugs</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($recent_drugs as $drug_item): ?>
                                <div class="col-md-3 col-lg-2 mb-3">
                                    <div class="card drug-card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title"><?php echo htmlspecialchars($drug_item['name']); ?></h6>
                                            <p class="text-primary fw-bold">ETB <?php echo number_format($drug_item['price'], 2); ?></p>
                                            <a href="drug_details.php?id=<?php echo $drug_item['id']; ?>" class="btn btn-sm btn-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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