<?php
/**
 * Database Configuration
 * Car Wash Management System
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'carwash_db');
define('DB_USER', 'root');
define('DB_PASS', '');
if (!defined('DB_PORT')) {
    define('DB_PORT', '3306');
}

// Create database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test database connection
function testConnection() {
    try {
        $pdo = getDBConnection();
        return "Database connection successful!";
    } catch (Exception $e) {
        return "Database connection failed: " . $e->getMessage();
    }
}
?>
