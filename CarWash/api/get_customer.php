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
    $customerData = $customer->getById($customerId);
    
    if ($customerData) {
        echo json_encode([
            'success' => true,
            'customer' => $customerData
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving customer data'
    ]);
}
?>
