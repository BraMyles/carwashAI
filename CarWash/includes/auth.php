<?php
/**
 * Authentication System
 * Car Wash Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication class
class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Login user
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        return true;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Check if user has admin role
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }
    
    // Check if user has staff role
    public function isStaff() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'staff';
    }
    
    // Get current user ID
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    // Get current user role
    public function getCurrentUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'email' => $_SESSION['email']
        ];
    }
    
    // Require authentication
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '/') . "index.php");
            exit();
        }
    }
    
    // Require admin role
    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '/') . "access_denied.php");
            exit();
        }
    }
    
    // Require staff role
    public function requireStaff() {
        $this->requireAuth();
        if (!$this->isStaff() && !$this->isAdmin()) {
            header("Location: " . (defined('BASE_PATH') ? BASE_PATH : '/') . "access_denied.php");
            exit();
        }
    }
}

// Initialize authentication
$auth = new Auth(getDBConnection());
?>
