<?php
require_once 'includes/init.php';
$auth->requireAdmin();

echo "<h2>🔄 Currency Update to Ghanaian Cedis (GH₵)</h2>";

try {
    // Update existing service prices to realistic Ghanaian prices
    $priceUpdates = [
        ['Basic Wash', 25.00],
        ['Premium Wash', 45.00], 
        ['Interior Clean', 35.00],
        ['Full Service', 75.00]
    ];
    
    echo "<h3>Updating Service Prices:</h3>";
    
    foreach ($priceUpdates as $update) {
        $serviceName = $update[0];
        $newPrice = $update[1];
        
        $stmt = $pdo->prepare("UPDATE services SET price = ? WHERE service_name = ?");
        $result = $stmt->execute([$newPrice, $serviceName]);
        
        if ($result) {
            echo "<p>✅ Updated <strong>$serviceName</strong> to GH₵$newPrice</p>";
        } else {
            echo "<p>❌ Failed to update <strong>$serviceName</strong></p>";
        }
    }
    
    // Update email templates
    echo "<h3>Updating Email Templates:</h3>";
    
    $stmt = $pdo->prepare("UPDATE email_templates SET body = REPLACE(body, '\${amount}', 'GH₵{amount}') WHERE template_name = 'payment_receipt'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "<p>✅ Updated payment receipt email template</p>";
    } else {
        echo "<p>❌ Failed to update email template</p>";
    }
    
    // Show current prices
    echo "<h3>Current Service Prices:</h3>";
    $stmt = $pdo->query("SELECT service_name, price FROM services ORDER BY price");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Service</th><th>Price (GH₵)</th></tr></thead>";
    echo "<tbody>";
    foreach ($services as $service) {
        echo "<tr><td>" . htmlspecialchars($service['service_name']) . "</td><td>GH₵" . number_format($service['price'], 2) . "</td></tr>";
    }
    echo "</tbody></table>";
    
    echo "<h3>✅ Currency Update Complete!</h3>";
    echo "<p>The system has been updated to use <strong>Ghanaian Cedis (GH₵)</strong> instead of US Dollars ($).</p>";
    echo "<p>All prices, reports, and displays will now show amounts in GH₵.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
echo "<p><a href='services.php'>View Services</a></p>";
echo "<p><a href='reports.php'>View Reports</a></p>";
?>




