<?php
require_once 'includes/init.php';
$auth->requireAuth();

$isAdmin = $auth->isAdmin();
$currentUser = $auth->getCurrentUser();

$date = $_GET['date'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'daily';
$export = $_GET['export'] ?? '';
$debug = isset($_GET['debug']) ? (int)$_GET['debug'] : 0;

// Data queries - show completed payments for selected date
if ($isAdmin) {
    $payStmt = $pdo->prepare(
        "SELECT p.*, b.booking_date, b.booking_time, c.full_name AS customer_name, s.service_name, u.full_name AS staff_name
         FROM payments p
         JOIN bookings b ON p.booking_id = b.id
         JOIN customers c ON b.customer_id = c.id
         JOIN services s ON b.service_id = s.id
         JOIN users u ON b.staff_id = u.id
         WHERE DATE(p.payment_date) = ? AND p.status = 'completed'
         ORDER BY p.payment_date DESC"
    );
    $payStmt->execute([$date]);
    $paymentsData = $payStmt->fetchAll();

    $rev = $payment->getDailyRevenue($date);

    if ($export === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payments_'.$date.'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Customer', 'Service', 'Staff', 'Amount', 'Method', 'Date', 'Time', 'Reference']);
        foreach ($paymentsData as $row) {
            fputcsv($out, [
                $row['customer_name'],
                $row['service_name'],
                $row['staff_name'],
                number_format((float)$row['amount'], 2),
                $row['payment_method'],
                $row['booking_date'],
                $row['booking_time'],
                $row['transaction_reference']
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['Daily Revenue', number_format($rev['total_revenue'] ?? 0, 2), 'Total Payments', $rev['total_payments'] ?? 0]);
        fclose($out);
        exit;
    }
} else {
    $payStmt = $pdo->prepare(
        "SELECT p.*, b.booking_date, b.booking_time, c.full_name AS customer_name, s.service_name
         FROM payments p
         JOIN bookings b ON p.booking_id = b.id
         JOIN customers c ON b.customer_id = c.id
         JOIN services s ON b.service_id = s.id
         WHERE DATE(p.payment_date) = ? AND p.status = 'completed' AND b.staff_id = ?
         ORDER BY p.payment_date DESC"
    );
    $payStmt->execute([$date, $currentUser['id']]);
    $paymentsData = $payStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Car Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_PATH; ?>assets/css/ai-modern-theme.css" rel="stylesheet">
</head>
<body>
	<!-- Floating Elements Background -->
	<div class="floating-elements">
		<div class="floating-element"></div>
		<div class="floating-element"></div>
		<div class="floating-element"></div>
	</div>

	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="<?php echo BASE_PATH; ?>dashboard.php">
				🚗 Car Wash AI
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>dashboard.php">📊 Dashboard</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>customers.php">👥 Customers</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>bookings.php">📅 Bookings</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>services.php">🔧 Services</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>payments.php">💰 Payments</a>
					</li>
					<?php if ($isAdmin): ?>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>users.php">👤 Users</a>
					</li>
					<?php endif; ?>
					<li class="nav-item">
						<a class="nav-link active" href="<?php echo BASE_PATH; ?>reports.php">📈 Reports</a>
					</li>
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
							👤 <?php echo htmlspecialchars($auth->getCurrentUser()['full_name']); ?>
						</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>logout.php">🚪 Logout</a></li>
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
				<div class="card neon-border">
					<div class="card-header">
						<div class="d-flex justify-content-between align-items-center">
							<h2 class="mb-0">Reports</h2>
							<form method="GET" class="d-flex align-items-center">
								<input type="date" class="form-control me-2" name="date" value="<?php echo htmlspecialchars($date); ?>">
								<?php if ($isAdmin): ?>
								<a href="<?php echo BASE_PATH; ?>reports.php?date=<?php echo urlencode($date); ?>&export=csv" class="btn btn-primary me-2">📊 Export CSV</a>
								<?php endif; ?>
								<button class="btn btn-primary" type="submit">✅ Apply</button>
							</form>
						</div>
					</div>
					<div class="card-body">

            <?php if ($isAdmin): ?>
            <?php if ($debug): ?>
            <?php
                try {
                    $dbgSh = $pdo->prepare("SELECT COUNT(*) FROM service_history WHERE service_date = ?");
                    $dbgSh->execute([$date]);
                    $countSh = (int)$dbgSh->fetchColumn();

                    $dbgBk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND status = 'completed'");
                    $dbgBk->execute([$date]);
                    $countBk = (int)$dbgBk->fetchColumn();

                    $dbgPm = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE DATE(payment_date) = ? AND status = 'completed'");
                    $dbgPm->execute([$date]);
                    $countPm = (int)$dbgPm->fetchColumn();
                } catch (Exception $e) {
                    $countSh = $countBk = $countPm = 0;
                }
            ?>
            <div class="alert alert-warning">
                <strong>Debug:</strong>
                Date: <?php echo htmlspecialchars($date); ?> —
                service_history: <?php echo $countSh; ?>,
                completed bookings: <?php echo $countBk; ?>,
                completed payments: <?php echo $countPm; ?>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card glass-card ai-glow float">
                        <h5>✅ Payments Completed</h5>
                        <h3><?php echo isset($paymentsData) ? count($paymentsData) : 0; ?></h3>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card glass-card ai-glow float" style="animation-delay: 0.5s;">
                        <h5>💰 Daily Revenue</h5>
                        <h3><?php echo formatCurrency($rev['total_revenue'] ?? 0); ?></h3>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card glass-card ai-glow float" style="animation-delay: 1s;">
                        <h5>💳 Total Payments</h5>
                        <h3><?php echo intval($rev['total_payments'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="glass-card">
                <div class="card-header" style="background: var(--dark-gradient); border-bottom: 1px solid var(--glass-border);">
                    <h5 class="mb-0 text-white"><?php echo $isAdmin ? '✅ Completed Payments' : '✅ My Completed Payments'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($paymentsData)): ?>
                        <div class="text-center text-muted py-4">No completed payments found for <?php echo formatDate($date); ?>.</div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Service</th>
                                <?php if ($isAdmin): ?><th>Staff</th><?php endif; ?>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($paymentsData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                    <?php if ($isAdmin): ?><td><?php echo htmlspecialchars($row['staff_name']); ?></td><?php endif; ?>
                                    <td><?php echo formatCurrency($row['amount']); ?></td>
                                    <td><?php echo htmlspecialchars(getPaymentMethodLabel($row['payment_method'])); ?></td>
                                    <td><?php echo formatDate($row['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/modern-effects.js"></script>
</body>
</html>

