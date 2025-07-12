<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password_hash, full_name, role, is_active 
                      FROM users WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user['is_active']) {
                    return ['success' => false, 'message' => 'Account is deactivated'];
                }
                
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    
                    return ['success' => true, 'user' => $user];
                }
            }
            
            return ['success' => false, 'message' => 'Invalid username or password'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function register($username, $email, $password, $full_name, $phone = '', $address = '') {
        try {
            // Check if username or email already exists
            $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':username', $username);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password_hash, full_name, phone, address, role) 
                      VALUES (:username, :email, :password_hash, :full_name, :phone, :address, 'customer')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $query = "SELECT id, username, email, full_name, role, phone, address, is_active, created_at 
                      FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback to session data if database query fails
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
    }
    
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['role'] === $role;
    }
    
    public function requireRole($role) {
        if (!$this->hasRole($role)) {
            header('Location: ../index.php?error=unauthorized');
            exit();
        }
    }
    
    public function getAllUsers() {
        try {
            $query = "SELECT id, username, email, full_name, role, phone, address, is_active, created_at 
                      FROM users ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function createUser($user_data) {
        try {
            // Check if username or email already exists
            $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':username', $user_data['username']);
            $check_stmt->bindParam(':email', $user_data['email']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password_hash, full_name, phone, address, role) 
                      VALUES (:username, :email, :password_hash, :full_name, :phone, :address, :role)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $user_data['username']);
            $stmt->bindParam(':email', $user_data['email']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':full_name', $user_data['full_name']);
            $stmt->bindParam(':phone', $user_data['phone']);
            $stmt->bindParam(':address', $user_data['address']);
            $stmt->bindParam(':role', $user_data['role']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User created successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updateUser($user_id, $user_data) {
        try {
            // Check if username or email already exists for other users
            $check_query = "SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :user_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':username', $user_data['username']);
            $check_stmt->bindParam(':email', $user_data['email']);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $query = "UPDATE users SET username = :username, email = :email, full_name = :full_name, 
                      phone = :phone, address = :address, role = :role WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':username', $user_data['username']);
            $stmt->bindParam(':email', $user_data['email']);
            $stmt->bindParam(':full_name', $user_data['full_name']);
            $stmt->bindParam(':phone', $user_data['phone']);
            $stmt->bindParam(':address', $user_data['address']);
            $stmt->bindParam(':role', $user_data['role']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function toggleUserStatus($user_id) {
        try {
            $query = "UPDATE users SET is_active = NOT is_active WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'User status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user status'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getUserActivityReport($start_date = null, $end_date = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($start_date) {
                $where_conditions[] = "u.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $where_conditions[] = "u.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT 
                        DATE(u.created_at) as date,
                        COUNT(*) as new_users,
                        COUNT(CASE WHEN u.role = 'customer' THEN 1 END) as new_customers,
                        COUNT(CASE WHEN u.role = 'pharmacist' THEN 1 END) as new_pharmacists,
                        COUNT(CASE WHEN u.role = 'cashier' THEN 1 END) as new_cashiers,
                        COUNT(CASE WHEN u.role = 'admin' THEN 1 END) as new_admins,
                        COUNT(CASE WHEN u.is_active = 1 THEN 1 END) as active_users,
                        COUNT(CASE WHEN u.is_active = 0 THEN 1 END) as inactive_users
                      FROM users u 
                      $where_clause 
                      GROUP BY DATE(u.created_at) 
                      ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // First, verify the current password
            $query = "SELECT password_hash FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            if (!password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password
            $update_query = "UPDATE users SET password_hash = :password_hash WHERE id = :user_id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':password_hash', $new_password_hash);
            $update_stmt->bindParam(':user_id', $user_id);
            
            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updateUserProfile($user_id, $full_name, $email, $phone = '', $address = '') {
        try {
            // Check if email already exists for other users
            $check_query = "SELECT id FROM users WHERE email = :email AND id != :user_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            $query = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address 
                      WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                // Update session data if it's the current user
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['full_name'] = $full_name;
                }
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function resetPassword($user_id, $new_password) {
        try {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password_hash = :password_hash WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':password_hash', $password_hash);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password reset successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to reset password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?> 