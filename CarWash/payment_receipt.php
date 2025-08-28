<?php
require_once 'includes/init.php';
$auth->requireStaff();

$id = intval($_GET['id'] ?? 0);
$record = $payment->getById($id);
if (!$record) {
    die('Payment not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $record['id']; ?> - Car Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/modern-style.css" rel="stylesheet">
    <style>
        .receipt { max-width: 600px; margin: 20px auto; }
    </style>
</head>
<body onload="window.print()">
<div class="receipt">
    <div class="text-center mb-3">
        <h3>Car Wash - Payment Receipt</h3>
        <small><?php echo date('Y-m-d H:i'); ?></small>
    </div>
    <table class="table table-modern">
        <tr><th>Receipt #</th><td><?php echo $record['id']; ?></td></tr>
        <tr><th>Customer</th><td><?php echo htmlspecialchars($record['customer_name']); ?></td></tr>
        <tr><th>Service</th><td><?php echo htmlspecialchars($record['service_name']); ?></td></tr>
        <tr><th>Amount</th><td><?php echo formatCurrency($record['amount']); ?></td></tr>
        <tr><th>Method</th><td><?php echo getPaymentMethodLabel($record['payment_method']); ?></td></tr>
        <tr><th>Status</th><td><?php echo htmlspecialchars(ucfirst($record['status'])); ?></td></tr>
        <tr><th>Date</th><td><?php echo formatDate($record['payment_date']); ?> <?php echo formatTime($record['payment_date']); ?></td></tr>
    </table>
    <div class="text-center mt-4">
        <button class="btn btn-secondary" onclick="window.print()">Print</button>
        <a class="btn btn-primary" href="payments.php">Back to Payments</a>
    </div>
</div>
</body>
</html>

