<?php
require_once 'includes/init.php';

echo "<h2>Adding Test Data</h2>";

try {
    $pdo = getDBConnection();
    
    // Check if we have customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
    $customerCount = $stmt->fetch()['count'];
    echo "<p>Current customers: $customerCount</p>";
    
    // Check if we have services
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $serviceCount = $stmt->fetch()['count'];
    echo "<p>Current services: $serviceCount</p>";
    
    // Check if we have users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p>Current users: $userCount</p>";
    
    // Add a test customer if none exist
    if ($customerCount == 0) {
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, email) VALUES (?, ?, ?)");
        $stmt->execute(['John Doe', '1234567890', 'john@example.com']);
        echo "<p>✅ Added test customer: John Doe</p>";
    }
    
    // Add a staff user if none exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
    $staffCount = $stmt->fetch()['count'];
    echo "<p>Current staff users: $staffCount</p>";
    
    if ($staffCount == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash('staff123', PASSWORD_DEFAULT);
        $stmt->execute(['staff', $hashedPassword, 'staff@carwash.com', 'Staff Member', 'staff']);
        echo "<p>✅ Added test staff user: staff/staff123</p>";
    }
    
    // Add a test booking if none exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $bookingCount = $stmt->fetch()['count'];
    echo "<p>Current bookings: $bookingCount</p>";
    
    if ($bookingCount == 0) {
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
            $stmt = $pdo->prepare("
                INSERT INTO bookings (customer_id, service_id, staff_id, booking_date, booking_time, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customer['id'],
                $service['id'],
                $user['id'],
                date('Y-m-d'),
                '10:00:00',
                'pending',
                'Test booking'
            ]);
            echo "<p>✅ Added test booking for today at 10:00 AM</p>";
        } else {
            echo "<p>❌ Cannot create booking - missing required data</p>";
        }
    }
    
    // Show all bookings
    $stmt = $pdo->query("
        SELECT b.*, c.full_name as customer_name, s.service_name, u.full_name as staff_name
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN services s ON b.service_id = s.id
        JOIN users u ON b.staff_id = u.id
        ORDER BY b.booking_date DESC, b.booking_time DESC
    ");
    $bookings = $stmt->fetchAll();
    
    echo "<h3>All Bookings:</h3>";
    if (count($bookings) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Staff</th></tr>";
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>{$booking['id']}</td>";
            echo "<td>{$booking['customer_name']}</td>";
            echo "<td>{$booking['service_name']}</td>";
            echo "<td>{$booking['booking_date']}</td>";
            echo "<td>{$booking['booking_time']}</td>";
            echo "<td>{$booking['status']}</td>";
            echo "<td>{$booking['staff_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No bookings found.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='bookings.php'>Go to Bookings page</a></p>";
echo "<p><a href='test_bookings.php'>Run Booking Test</a></p>";
?>
