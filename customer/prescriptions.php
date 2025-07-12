<?php
require_once '../classes/Auth.php';
require_once '../classes/Prescription.php';

$auth = new Auth();
$auth->requireRole('customer');

$user = $auth->getCurrentUser();
$prescription = new Prescription();

$error = '';
$success = '';

// Handle prescription upload
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'upload_prescription') {
    $symptoms = $_POST['symptoms'] ?? '';
    $prescription_file = null;
    
    if (!empty($symptoms)) {
        // Handle file upload if provided
        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/prescriptions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['prescription_file']['name'], PATHINFO_EXTENSION);
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                $file_name = 'prescription_' . $user['id'] . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], $file_path)) {
                    $prescription_file = $file_name;
                } else {
                    $error = 'Failed to upload file';
                }
            } else {
                $error = 'Invalid file type. Please upload PDF, JPG, or PNG files only.';
            }
        }
        
        if (empty($error)) {
            $result = $prescription->uploadPrescription($user['id'], $symptoms, $prescription_file);
            if ($result['success']) {
                $success = 'Prescription uploaded successfully! Our pharmacist will review it soon.';
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = 'Please describe your symptoms';
    }
}

// Get user's prescriptions
$user_prescriptions = $prescription->getPrescriptionsByCustomer($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .prescription-card { border-left: 4px solid #3498db; margin-bottom: 15px; transition: transform 0.2s; }
        .prescription-card:hover { transform: translateY(-2px); }
        .status-badge { font-size: 0.8rem; padding: 5px 10px; border-radius: 20px; }
        .upload-area { border: 2px dashed #dee2e6; border-radius: 10px; padding: 40px; text-align: center; transition: all 0.3s ease; }
        .upload-area:hover { border-color: #3498db; background-color: #f8f9ff; }
        .upload-area.dragover { border-color: #3498db; background-color: #f8f9ff; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Customer Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-home"></i>Dashboard
            </a>
            <a href="search.php" class="nav-link">
                <i class="fas fa-search"></i>Search Drugs
            </a>
            <a href="orders.php" class="nav-link">
                <i class="fas fa-shopping-bag"></i>My Orders
            </a>
            <a href="prescriptions.php" class="nav-link active">
                <i class="fas fa-prescription"></i>My Prescriptions
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
                    <h4 class="mb-0">My Prescriptions</h4>
                    <p class="mb-0 text-muted">Upload and manage your prescriptions</p>
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

            <div class="row">
                <!-- Upload New Prescription -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload New Prescription</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_prescription">
                                
                                <div class="mb-3">
                                    <label for="symptoms" class="form-label">Describe your symptoms:</label>
                                    <textarea class="form-control" id="symptoms" name="symptoms" rows="4" 
                                              placeholder="Please describe your symptoms, medical condition, or reason for the prescription..." required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prescription_file" class="form-label">Upload Prescription (Optional):</label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-2">Drag & drop your prescription file here</p>
                                        <p class="text-muted small">or click to browse</p>
                                        <input type="file" class="form-control" id="prescription_file" name="prescription_file" 
                                               accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                                    </div>
                                    <small class="text-muted">Supported formats: PDF, JPG, PNG (Max 10MB)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Prescription
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>How it works</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6><i class="fas fa-1 me-2"></i>Upload Prescription</h6>
                                <p class="text-muted small">Upload your prescription or describe your symptoms</p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-2 me-2"></i>Pharmacist Review</h6>
                                <p class="text-muted small">Our pharmacist will review your prescription within 24 hours</p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-3 me-2"></i>Get Notification</h6>
                                <p class="text-muted small">You'll be notified when your prescription is approved or rejected</p>
                            </div>
                            <div>
                                <h6><i class="fas fa-4 me-2"></i>Place Order</h6>
                                <p class="text-muted small">Order your medications once approved</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescriptions List -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Prescription History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($user_prescriptions)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-prescription fa-3x text-muted mb-3"></i>
                                    <h5>No Prescriptions Found</h5>
                                    <p class="text-muted">You haven't uploaded any prescriptions yet.</p>
                                    <p class="text-muted">Upload your first prescription to get started.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($user_prescriptions as $prescription_item): ?>
                                    <div class="card prescription-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="card-title mb-1">Prescription #<?php echo $prescription_item['id']; ?></h6>
                                                    <p class="text-muted mb-0"><?php echo date('M d, Y H:i', strtotime($prescription_item['created_at'])); ?></p>
                                                </div>
                                                <span class="status-badge badge bg-<?php 
                                                    echo $prescription_item['status'] === 'approved' ? 'success' : 
                                                        ($prescription_item['status'] === 'rejected' ? 'danger' : 'warning'); 
                                                ?>">
                                                    <?php echo ucfirst($prescription_item['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <h6>Symptoms:</h6>
                                                <p class="text-muted"><?php echo htmlspecialchars($prescription_item['symptoms']); ?></p>
                                            </div>
                                            
                                            <?php if ($prescription_item['prescription_file']): ?>
                                                <div class="mb-3">
                                                    <h6>Uploaded File:</h6>
                                                    <a href="../uploads/prescriptions/<?php echo htmlspecialchars($prescription_item['prescription_file']); ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file me-1"></i>View File
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($prescription_item['notes']): ?>
                                                <div class="mb-3">
                                                    <h6>Pharmacist Notes:</h6>
                                                    <p class="text-muted"><?php echo htmlspecialchars($prescription_item['notes']); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <?php if ($prescription_item['updated_at'] && $prescription_item['updated_at'] !== $prescription_item['created_at']): ?>
                                                        Reviewed: <?php echo date('M d, Y H:i', strtotime($prescription_item['updated_at'])); ?>
                                                    <?php else: ?>
                                                        Pending review
                                                    <?php endif; ?>
                                                </small>
                                                
                                                <?php if ($prescription_item['status'] === 'approved'): ?>
                                                    <a href="search.php" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-shopping-cart me-1"></i>Order Medications
                                                    </a>
                                                <?php endif; ?>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('prescription_file');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadArea.innerHTML = `
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-success mb-2">File selected: ${fileInput.files[0].name}</p>
                    <p class="text-muted small">Click to change file</p>
                `;
            }
        });

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