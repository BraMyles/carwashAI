<?php
require_once '../includes/init.php';
$auth->requireAuth();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

$customerId = intval($_GET['id']);

try {
    $pdo = getDBConnection();
    
    // Get service history for the customer
    $stmt = $pdo->prepare("
        SELECT 
            sh.id,
            sh.service_date,
            s.name as service_name,
            sh.amount,
            sh.status,
            sh.created_at
        FROM service_history sh
        LEFT JOIN services s ON sh.service_id = s.id
        WHERE sh.customer_id = ?
        ORDER BY sh.service_date DESC, sh.created_at DESC
        LIMIT 20
    ");
    
    $stmt->execute([$customerId]);
    $history = $stmt->fetchAll();
    
    // Format dates for display
    foreach ($history as &$record) {
        $record['service_date'] = date('M j, Y', strtotime($record['service_date']));
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving service history'
    ]);
}
?>
