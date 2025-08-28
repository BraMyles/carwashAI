<?php
require_once 'includes/init.php';

echo "<h2>Adding Test Reports Data</h2>";

try {
    $pdo = getDBConnection();
    
    // Check current data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM service_history");
    $serviceHistoryCount = $stmt->fetch()['count'];
    echo "<p>Current completed services: $serviceHistoryCount</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
    $paymentsCount = $stmt->fetch()['count'];
    echo "<p>Current payments: $paymentsCount</p>";
    
    // Add test completed service if none exist
    if ($serviceHistoryCount == 0) {
        // Get first customer
        $stmt = $pdo->query("SELECT id FROM customers LIMIT 1");
        $customer = $stmt->fetch();
        
        // Get first service
        $stmt = $pdo->query("SELECT id FROM services LIMIT 1");
        $service = $stmt->fetch();
        
        // Get first user (staff)
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'staff' LIMIT 1");
        $user = $stmt->fetch();
        
        if ($customer && $service && $user) {
            // Add to service_history
            $stmt = $pdo->prepare("
                INSERT INTO service_history (booking_id, customer_id, service_id, staff_id, service_date, service_time, amount, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                1, // booking_id (dummy)
                $customer['id'],
                $service['id'],
                $user['id'],
                date('Y-m-d'),
                '10:30:00',
                25.00,
                'completed',
                'Test completed service'
            ]);
            echo "<p>✅ Added test completed service for today</p>";
        }
    }
    
    // Add test payment if none exist
    if ($paymentsCount == 0) {
        // Get first booking
        $stmt = $pdo->query("SELECT id FROM bookings LIMIT 1");
        $booking = $stmt->fetch();
        
        if ($booking) {
            // Add payment
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, amount, payment_method, status, payment_date, transaction_reference)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $booking['id'],
                25.00,
                'cash',
                'completed',
                date('Y-m-d H:i:s'),
                'TEST-' . time()
            ]);
            echo "<p>✅ Added test payment for today</p>";
        } else {
            // Create a dummy booking for payment
            $stmt = $pdo->query("SELECT id FROM customers LIMIT 1");
            $customer = $stmt->fetch();
            $stmt = $pdo->query("SELECT id FROM services LIMIT 1");
            $service = $stmt->fetch();
            $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
            $user = $stmt->fetch();
            
            if ($customer && $service && $user) {
                // Add booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (customer_id, service_id, staff_id, booking_date, booking_time, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $customer['id'],
                    $service['id'],
                    $user['id'],
                    date('Y-m-d'),
                    '11:00:00',
                    'completed',
                    'Test booking for payment'
                ]);
                $bookingId = $pdo->lastInsertId();
                
                // Add payment
                $stmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, amount, payment_method, status, payment_date, transaction_reference)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $bookingId,
                    25.00,
                    'cash',
                    'completed',
                    date('Y-m-d H:i:s'),
                    'TEST-' . time()
                ]);
                echo "<p>✅ Added test booking and payment for today</p>";
            }
        }
    }
    
    // Show current data
    echo "<h3>Current Data Summary:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM service_history");
    $serviceHistoryCount = $stmt->fetch()['count'];
    echo "<p>Completed services: $serviceHistoryCount</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
    $paymentsCount = $stmt->fetch()['count'];
    echo "<p>Payments: $paymentsCount</p>";
    
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
    $result = $stmt->fetch();
    echo "<p>Total revenue: $" . number_format($result['total'] ?? 0, 2) . "</p>";
    
    // Show today's data
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM service_history WHERE service_date = ?");
    $stmt->execute([$today]);
    $todayServices = $stmt->fetch()['count'];
    echo "<p>Today's completed services: $todayServices</p>";
    
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = ? AND status = 'completed'");
    $stmt->execute([$today]);
    $result = $stmt->fetch();
    echo "<p>Today's revenue: $" . number_format($result['total'] ?? 0, 2) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h4>Test Links:</h4>";
echo "<p><a href='reports.php'>Go to Reports page</a></p>";
echo "<p><a href='test_reports.php'>Run Reports Test</a></p>";
echo "<p><a href='payments.php'>Go to Payments page</a></p>";
?>
