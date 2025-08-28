<?php
require_once 'includes/init.php';

echo "<h2>Reports System Test</h2>";

// Test database connection
try {
    $pdo = getDBConnection();
    echo "<p>✅ Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test service_history table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM service_history");
    $result = $stmt->fetch();
    echo "<p>📊 Total completed services in service_history: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT * FROM service_history ORDER BY service_date DESC LIMIT 3");
        $recentServices = $stmt->fetchAll();
        echo "<h4>Recent Completed Services:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Staff</th></tr>";
        foreach ($recentServices as $service) {
            // Get customer name
            $custStmt = $pdo->prepare("SELECT full_name FROM customers WHERE id = ?");
            $custStmt->execute([$service['customer_id']]);
            $customer = $custStmt->fetch();
            
            // Get service name
            $svcStmt = $pdo->prepare("SELECT service_name FROM services WHERE id = ?");
            $svcStmt->execute([$service['service_id']]);
            $serviceName = $svcStmt->fetch();
            
            // Get staff name
            $staffStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $staffStmt->execute([$service['staff_id']]);
            $staff = $staffStmt->fetch();
            
            echo "<tr>";
            echo "<td>{$service['id']}</td>";
            echo "<td>" . ($customer['full_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . ($serviceName['service_name'] ?? 'Unknown') . "</td>";
            echo "<td>{$service['service_date']}</td>";
            echo "<td>{$service['service_time']}</td>";
            echo "<td>" . ($staff['full_name'] ?? 'Unknown') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking service_history: " . $e->getMessage() . "</p>";
}

// Test payments table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
    $result = $stmt->fetch();
    echo "<p>💰 Total payments: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
        $result = $stmt->fetch();
        echo "<p>💰 Total revenue: $" . number_format($result['total'] ?? 0, 2) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking payments: " . $e->getMessage() . "</p>";
}

// Test today's data
$today = date('Y-m-d');
echo "<h4>Today's Data ($today):</h4>";

try {
    // Today's completed services
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM service_history WHERE service_date = ?");
    $stmt->execute([$today]);
    $result = $stmt->fetch();
    echo "<p>📊 Today's completed services: " . $result['count'] . "</p>";
    
    // Today's revenue
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = ? AND status = 'completed'");
    $stmt->execute([$today]);
    $result = $stmt->fetch();
    echo "<p>💰 Today's revenue: $" . number_format($result['total'] ?? 0, 2) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error checking today's data: " . $e->getMessage() . "</p>";
}

// Test Payment class getDailyRevenue method
try {
    $dailyRevenue = $payment->getDailyRevenue($today);
    echo "<p>📈 Payment class daily revenue: $" . number_format($dailyRevenue['total_revenue'] ?? 0, 2) . "</p>";
    echo "<p>📈 Payment class total payments: " . ($dailyRevenue['total_payments'] ?? 0) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error in Payment class: " . $e->getMessage() . "</p>";
}

// Test if we can create a test completed service
echo "<h4>Creating Test Data:</h4>";
try {
    // Check if we have bookings that can be completed
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'");
    $result = $stmt->fetch();
    echo "<p>📋 Confirmed bookings available: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        // Get a confirmed booking
        $stmt = $pdo->query("SELECT * FROM bookings WHERE status = 'confirmed' LIMIT 1");
        $booking = $stmt->fetch();
        
        if ($booking) {
            echo "<p>📋 Found booking ID: {$booking['id']} for customer: {$booking['customer_id']}</p>";
            
            // Check if this booking is already in service_history
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM service_history WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                echo "<p>✅ This booking is not yet completed - can be used for testing</p>";
            } else {
                echo "<p>⚠️ This booking is already completed</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking bookings: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h4>Test Links:</h4>";
echo "<p><a href='reports.php'>Go to Reports page</a></p>";
echo "<p><a href='reports.php?date=" . date('Y-m-d') . "&export=csv'>Test CSV Export</a></p>";
echo "<p><a href='payments.php'>Go to Payments page</a></p>";
echo "<p><a href='bookings.php'>Go to Bookings page</a></p>";
?>
