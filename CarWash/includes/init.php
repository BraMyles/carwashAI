<?php
/**
 * Main Initialization File
 * Car Wash Management System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path for absolute URLs
if (!defined('BASE_PATH')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    define('BASE_PATH', ($dir === '' || $dir === '.') ? '/' : $dir . '/');
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Include authentication system
require_once __DIR__ . '/auth.php';

// Include all business logic classes
require_once __DIR__ . '/classes/Customer.php';
require_once __DIR__ . '/classes/Service.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/classes/Payment.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Message.php';

// Initialize database connection
$pdo = getDBConnection();

// Initialize business logic classes
$customer = new Customer($pdo);
$service = new Service($pdo);
$booking = new Booking($pdo);
$payment = new Payment($pdo);
$user = new User($pdo);
$messageService = new Message($pdo);

// Helper functions
function formatCurrency($amount) {
    return 'GH₵' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
        'confirmed' => '<span class="badge bg-info text-dark">Confirmed</span>',
        'in_progress' => '<span class="badge bg-primary">In Progress</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

function getPaymentMethodLabel($method) {
    $labels = [
        'cash' => 'Cash',
        'mobile_money' => 'Mobile Money'
    ];
    
    return $labels[$method] ?? 'Unknown';
}

// Error and success message handling
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

function displayMessage() {
    $message = getMessage();
    if ($message) {
        $alertClass = $message['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($message['text']);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}

// CSRF helpers
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token'])) { return false; }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}
?>
