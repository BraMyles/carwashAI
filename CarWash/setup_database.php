<?php
/**
 * Database Setup Script
 * Run this file to set up the car wash management system database
 */

echo "<h2>Car Wash Management System - Database Setup</h2>";

// Check if MySQL extension is available
if (!extension_loaded('pdo_mysql')) {
    die("Error: PDO MySQL extension is not available. Please enable it in your XAMPP configuration.");
}

// Try to connect to MySQL server
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ MySQL server connection successful</p>";
} catch (PDOException $e) {
    die("Error: Could not connect to MySQL server. Make sure XAMPP is running and MySQL service is started.<br>Error: " . $e->getMessage());
}

// Read and execute the schema file
$schemaFile = 'database/schema.sql';
if (!file_exists($schemaFile)) {
    die("Error: Schema file not found at: $schemaFile");
}

try {
    $schema = file_get_contents($schemaFile);
    
    // Split the schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p style='color: green;'>✓ Database 'carwash_db' created successfully</p>";
    echo "<p style='color: green;'>✓ All tables created successfully</p>";
    echo "<p style='color: green;'>✓ Default data inserted successfully</p>";
    
    // Test the new database connection
    $pdo = new PDO("mysql:host=localhost;dbname=carwash_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify tables were created
    $tables = ['users', 'customers', 'services', 'bookings', 'payments', 'service_history', 'email_templates'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' verified</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' not found</p>";
        }
    }
    
    // Show default admin credentials
    echo "<h3>Default Admin Account:</h3>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><strong>Email:</strong> admin@carwash.com</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<p>1. Delete this setup file (setup_database.php) for security</p>";
    echo "<p>2. Access the system at: <a href='index.php'>index.php</a></p>";
    echo "<p>3. Login with the admin credentials above</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error setting up database: " . $e->getMessage() . "</p>";
}
?>

