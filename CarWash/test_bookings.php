<?php
require_once 'includes/init.php';

echo "<h2>Booking System Test</h2>";

// Test database connection
try {
    $pdo = getDBConnection();
    echo "<p>✅ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test if bookings table exists and has data
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $result = $stmt->fetch();
    echo "<p>📊 Total bookings in database: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error checking bookings table: " . $e->getMessage() . "</p>";
}

// Test if customers table has data
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
    $result = $stmt->fetch();
    echo "<p>👥 Total customers in database: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error checking customers table: " . $e->getMessage() . "</p>";
}

// Test if services table has data
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $result = $stmt->fetch();
    echo "<p>🔧 Total services in database: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error checking services table: " . $e->getMessage() . "</p>";
}

// Test Booking class getAll method
try {
    $allBookings = $booking->getAll();
    echo "<p>📋 Bookings retrieved via Booking class: " . count($allBookings) . "</p>";
    
    if (count($allBookings) > 0) {
        echo "<h3>Sample Booking Data:</h3>";
        echo "<pre>";
        print_r($allBookings[0]);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error in Booking class: " . $e->getMessage() . "</p>";
}

// Test if there are any users
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>👤 Total users in database: " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error checking users table: " . $e->getMessage() . "</p>";
}

echo "<p><a href='bookings.php'>Go back to Bookings page</a></p>";
?>
