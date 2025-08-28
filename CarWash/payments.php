<?php
require_once 'includes/init.php';
$auth->requireStaff();

$currentUser = $auth->getCurrentUser();
$message = '';

// Create or update payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
		$message = 'Invalid request. Please refresh and try again.';
	} else {
		$action = $_POST['action'] ?? '';
		if ($action === 'create') {
			$bookingId = intval($_POST['booking_id']);
			$amount = max(0, (float)($_POST['amount']));
			$method = $_POST['payment_method'];
			$status = $_POST['status'] ?? 'pending';
			$reference = trim($_POST['transaction_reference'] ?? '');

			$paymentId = $payment->create([
				'booking_id' => $bookingId,
				'amount' => $amount,
				'payment_method' => $method,
				'status' => $status,
				'transaction_reference' => $reference
			]);

			if ($paymentId) {
				// If completed, mark booking completed and add to service_history
				if ($status === 'completed') {
					$booking->updateStatus($bookingId, 'completed');
					// Insert into service_history
					$stmt = $pdo->prepare("SELECT customer_id, service_id, staff_id, booking_date, booking_time FROM bookings WHERE id = ?");
					$stmt->execute([$bookingId]);
					if ($row = $stmt->fetch()) {
						$ins = $pdo->prepare("INSERT INTO service_history (booking_id, customer_id, service_id, staff_id, service_date, service_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
						$ins->execute([$bookingId, $row['customer_id'], $row['service_id'], $row['staff_id'], $row['booking_date'], $row['booking_time'], '']);

						// Send payment email
						require_once 'includes/email.php';
						$custStmt = $pdo->prepare("SELECT full_name, email FROM customers WHERE id = ?");
						$custStmt->execute([$row['customer_id']]);
						$cust = $custStmt->fetch();
						$svcStmt = $pdo->prepare("SELECT service_name FROM services WHERE id = ?");
						$svcStmt->execute([$row['service_id']]);
						$svc = $svcStmt->fetch();
						if ($cust && !empty($cust['email'])) {
							sendSystemEmail('payment_receipt', $cust['email'], [
								'customer_name' => $cust['full_name'],
								'service_name' => $svc['service_name'] ?? 'Service',
								'amount' => number_format($amount, 2),
								'receipt_number' => $paymentId
							]);
						}
					}
				}
				$message = 'Payment recorded successfully.';
			} else {
				$message = 'Failed to record payment.';
			}
		} elseif ($action === 'update_status') {
			$id = intval($_POST['id']);
			$status = $_POST['status'];
			if ($payment->updateStatus($id, $status)) {
				$message = 'Payment status updated.';
			} else {
				$message = 'Failed to update status.';
			}
		}
	}
}

// Filters
$dateFilter = $_GET['date'] ?? '';
$paymentsList = [];
if ($dateFilter) {
	$stmt = $pdo->prepare("SELECT p.*, c.full_name AS customer_name, s.service_name FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN customers c ON b.customer_id=c.id JOIN services s ON b.service_id=s.id WHERE DATE(p.payment_date)=? ORDER BY p.payment_date DESC");
	$stmt->execute([$dateFilter]);
	$paymentsList = $stmt->fetchAll();
} else {
	$stmt = $pdo->query("SELECT p.*, c.full_name AS customer_name, s.service_name FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN customers c ON b.customer_id=c.id JOIN services s ON b.service_id=s.id ORDER BY p.payment_date DESC LIMIT 100");
	$paymentsList = $stmt->fetchAll();
}

// Unpaid bookings for today (primary source)
$today = date('Y-m-d');
$showingRecentUnpaid = false;
$stmt = $pdo->prepare("SELECT b.id, b.booking_date, b.booking_time, c.full_name, c.email, s.service_name, s.price
FROM bookings b
JOIN customers c ON b.customer_id=c.id
JOIN services s ON b.service_id=s.id
LEFT JOIN payments p ON p.booking_id=b.id AND p.status='completed'
WHERE b.booking_date = ?
AND b.status IN ('pending','confirmed','in_progress','completed')
AND p.id IS NULL
ORDER BY b.booking_time DESC");
$stmt->execute([$today]);
$unpaidBookings = $stmt->fetchAll();

// Fallback: recent unpaid bookings (±30 days)
if (empty($unpaidBookings)) {
	$from = date('Y-m-d', strtotime('-30 days'));
	$to = date('Y-m-d', strtotime('+30 days'));
	$stmt = $pdo->prepare("SELECT b.id, b.booking_date, b.booking_time, c.full_name, c.email, s.service_name, s.price
FROM bookings b
JOIN customers c ON b.customer_id=c.id
JOIN services s ON b.service_id=s.id
LEFT JOIN payments p ON p.booking_id=b.id AND p.status='completed'
WHERE b.booking_date BETWEEN ? AND ?
AND b.status IN ('pending','confirmed','in_progress','completed')
AND p.id IS NULL
ORDER BY b.booking_date DESC, b.booking_time DESC");
	$stmt->execute([$from, $to]);
	$unpaidBookings = $stmt->fetchAll();
	$showingRecentUnpaid = !empty($unpaidBookings);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payments - Car Wash</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo BASE_PATH; ?>assets/css/ai-modern-theme.css" rel="stylesheet">
	<link href="<?php echo BASE_PATH; ?>assets/css/modal-fixes.css" rel="stylesheet">
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-modern">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_PATH; ?>dashboard.php">
                <i class="fas fa-car me-2"></i>Car Wash Management
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>customers.php">
                            <i class="fas fa-users me-1"></i>Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>bookings.php">
                            <i class="fas fa-calendar-check me-1"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>services.php">
                            <i class="fas fa-concierge-bell me-1"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo BASE_PATH; ?>payments.php">
                            <i class="fas fa-credit-card me-1"></i>Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>messages.php">
                            <i class="fas fa-comments me-1"></i>Messages
                        </a>
                    </li>
                    <?php if ($auth->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>users.php">
                            <i class="fas fa-user-shield me-1"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_PATH; ?>reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($currentUser['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-modern">
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title">
                        <i class="fas fa-credit-card me-2"></i>Payments Management
                    </h2>
                    <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="fas fa-plus me-2"></i>Record Payment
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-modern">
                    <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                <!-- Filter Section -->
                <div class="card glass-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>Filter Payments
                        </h5>
                    </div>
                    <div class="card-body">
                        <form class="row g-3" method="GET">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date Filter</label>
                                <input class="form-control form-control-modern" type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-secondary btn-modern me-2" type="submit">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a class="btn btn-outline-secondary btn-modern" href="<?php echo BASE_PATH; ?>payments.php">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card glass-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Payment Records
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-modern">
                                <thead>
                                <tr>
                                    <th><i class="fas fa-user me-1"></i>Customer</th>
                                    <th><i class="fas fa-concierge-bell me-1"></i>Service</th>
                                    <th><i class="fas fa-dollar-sign me-1"></i>Amount</th>
                                    <th><i class="fas fa-credit-card me-1"></i>Method</th>
                                    <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                    <th><i class="fas fa-calendar me-1"></i>Date</th>
                                    <th><i class="fas fa-print me-1"></i>Receipt</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($paymentsList as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['service_name']); ?></td>
                                        <td><span class="fw-bold text-success"><?php echo formatCurrency($p['amount']); ?></span></td>
                                        <td><span class="badge bg-info"><?php echo getPaymentMethodLabel($p['payment_method']); ?></span></td>
                                        <td>
                                            <?php 
                                            $statusClass = $p['status'] === 'completed' ? 'bg-success' : ($p['status'] === 'pending' ? 'bg-warning' : 'bg-secondary');
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($p['status'])); ?></span>
                                        </td>
                                        <td><?php echo formatDate($p['payment_date']); ?> <?php echo formatTime($p['payment_date']); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary btn-modern" href="<?php echo BASE_PATH; ?>payment_receipt.php?id=<?php echo $p['id']; ?>" target="_blank">
                                                <i class="fas fa-print me-1"></i>Print
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card me-2"></i>Record Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar-check me-1"></i>Booking Selection
                        </label>
                        <select class="form-select" name="booking_id" required>
                            <option value="">Select booking</option>
                            <?php if (empty($unpaidBookings)): ?>
                                <option value="">No unpaid bookings found for today.</option>
                            <?php else: ?>
                                <?php if ($showingRecentUnpaid): ?>
                                    <option value="" disabled>Showing recent unpaid bookings</option>
                                <?php endif; ?>
                                <?php foreach ($unpaidBookings as $b): ?>
                                    <option value="<?php echo $b['id']; ?>" data-price="<?php echo $b['price']; ?>">
                                        #<?php echo $b['id']; ?> - <?php echo htmlspecialchars($b['full_name']); ?> (<?php echo htmlspecialchars($b['service_name']); ?>) — <?php echo formatDate($b['booking_date']); ?> <?php echo htmlspecialchars($b['booking_time']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-dollar-sign me-1"></i>Amount
                            </label>
                            <input class="form-control" type="number" step="0.01" name="amount" id="amount-input" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-credit-card me-1"></i>Payment Method
                            </label>
                            <select class="form-select" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle me-1"></i>Status
                            </label>
                            <select class="form-select" name="status">
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag me-1"></i>Reference
                            </label>
                            <input class="form-control" type="text" name="transaction_reference" placeholder="Transaction Reference (optional)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save me-2"></i>Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/modal-enhancements.js"></script>
<script src="assets/js/modal-fixes.js"></script>
<script>
// Prefill amount from selected booking
var select = document.querySelector('select[name="booking_id"]');
if (select) {
    select.addEventListener('change', function() {
        var opt = select.options[select.selectedIndex];
        var price = opt.getAttribute('data-price') || '';
        var amountInput = document.getElementById('amount-input');
        if (price && amountInput) amountInput.value = price;
    });
}
</script>
</body>
</html>
