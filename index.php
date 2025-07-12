<?php
require_once 'classes/Auth.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    switch ($user['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'pharmacist':
            header('Location: pharmacist/dashboard.php');
            break;
        case 'cashier':
            header('Location: cashier/dashboard.php');
            break;
        case 'customer':
            header('Location: customer/dashboard.php');
            break;
    }
    exit();
}

$error = '';
$success = '';

// Handle login
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            $user = $auth->getCurrentUser();
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                case 'pharmacist':
                    header('Location: pharmacist/dashboard.php');
                    break;
                case 'cashier':
                    header('Location: cashier/dashboard.php');
                    break;
                case 'customer':
                    header('Location: customer/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Handle registration
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = $_POST['reg_username'] ?? '';
    $email = $_POST['reg_email'] ?? '';
    $password = $_POST['reg_password'] ?? '';
    $full_name = $_POST['reg_full_name'] ?? '';
    $phone = $_POST['reg_phone'] ?? '';
    $address = $_POST['reg_address'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill all required fields';
    } else {
        $result = $auth->register($username, $email, $password, $full_name, $phone, $address);
        if ($result['success']) {
            $success = 'Registration successful! Please login.';
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky Pharmacy - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 3rem;
            max-width: 450px;
            width: 100%;
        }
        
        .pharmacy-logo {
            width: 70px;
            height: 70px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 1.8rem;
        }
        
        .pharmacy-title {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .pharmacy-title h1 {
            color: #333;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .pharmacy-title p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: white;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
            background: white;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #007bff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: none;
            box-shadow: none;
        }
        
        .nav-tabs {
            border: none;
            margin-bottom: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px;
            color: #666;
            font-weight: 500;
            padding: 10px 20px;
            margin-right: 8px;
            transition: all 0.2s ease;
            background: #f8f9fa;
        }
        
        .nav-tabs .nav-link.active {
            background: #007bff;
            color: white;
        }
        
        .nav-tabs .nav-link:hover:not(.active) {
            background: #e9ecef;
            color: #333;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 2rem;
                margin: 0;
            }
            
            .pharmacy-title h1 {
                font-size: 1.7rem;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="pharmacy-logo">
            <i class="fas fa-heartbeat"></i>
        </div>
        
        <div class="pharmacy-title">
            <h1>Sky Pharmacy</h1>
            <p>Adama, Ethiopia</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="authTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                    <i class="fas fa-user-plus me-2"></i>Register
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="authTabsContent">
            <!-- Login Tab -->
            <div class="tab-pane fade show active" id="login" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username or Email
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
            
            <!-- Register Tab -->
            <div class="tab-pane fade" id="register" role="tabpanel">
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="mb-3">
                        <label for="reg_username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control" id="reg_email" name="reg_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_full_name" class="form-label">
                            <i class="fas fa-id-card me-2"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="reg_full_name" name="reg_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_phone" class="form-label">
                            <i class="fas fa-phone me-2"></i>Phone
                        </label>
                        <input type="tel" class="form-control" id="reg_phone" name="reg_phone">
                    </div>
                    <div class="mb-3">
                        <label for="reg_address" class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Address
                        </label>
                        <textarea class="form-control" id="reg_address" name="reg_address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="reg_password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 