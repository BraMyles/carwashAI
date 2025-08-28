<?php
require_once 'includes/init.php';
$auth->requireAuth();
$currentUser = $auth->getCurrentUser();

// Compute metrics
// Today's bookings (all statuses except cancelled)
try {
	$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM bookings WHERE booking_date = CURDATE() AND status IN ('pending','confirmed','in_progress','completed')");
	$todayBookings = (int)($stmt->fetch()['cnt'] ?? 0);
} catch (Exception $e) {
	$todayBookings = 0;
}

// Today's revenue
$rev = $payment->getDailyRevenue();
$todayRevenue = (float)($rev['total_revenue'] ?? 0);

// Total customers
try {
	$custStmt = $pdo->query("SELECT COUNT(*) AS c FROM customers");
	$totalCustomers = (int)($custStmt->fetch()['c'] ?? 0);
} catch (Exception $e) {
	$totalCustomers = 0;
}

// Total services
try {
	$svcStmt = $pdo->query("SELECT COUNT(*) AS c FROM services WHERE is_active = 1");
	$totalServices = (int)($svcStmt->fetch()['c'] ?? 0);
} catch (Exception $e) {
	$totalServices = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard - Car Wash Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link href="<?php echo BASE_PATH; ?>assets/css/ai-modern-theme.css" rel="stylesheet">
	<link href="<?php echo BASE_PATH; ?>assets/css/modal-fixes.css" rel="stylesheet">
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
						<a class="nav-link active" href="<?php echo BASE_PATH; ?>dashboard.php">📊 Dashboard</a>
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
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>messages.php">💬 Messages</a>
					</li>
					<?php if ($auth->isAdmin()): ?>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>users.php">👤 Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>reports.php">📈 Reports</a>
					</li>
					<?php endif; ?>
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
							👤 <?php echo htmlspecialchars($currentUser['full_name']); ?>
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
						<h2 class="mb-0">Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</h2>
					</div>
					<div class="card-body">
						<p class="mb-4">Car Wash Management System Dashboard - AI-Powered Insights</p>
						
						<div class="row">
							<div class="col-md-3 mb-4">
								<div class="card neon-border">
									<div class="card-body text-center">
										<h5 class="card-title">📅 Today's Bookings</h5>
										<h3 class="text-primary"><?php echo $todayBookings; ?></h3>
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-4">
								<div class="card neon-border">
									<div class="card-body text-center">
										<h5 class="card-title">💰 Today's Revenue</h5>
										<h3 class="text-success"><?php echo formatCurrency($todayRevenue); ?></h3>
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-4">
								<div class="card neon-border">
									<div class="card-body text-center">
										<h5 class="card-title">👥 Total Customers</h5>
										<h3 class="text-success"><?php echo $totalCustomers; ?></h3>
									</div>
								</div>
							</div>
							<div class="col-md-3 mb-4">
								<div class="card neon-border">
									<div class="card-body text-center">
										<h5 class="card-title">🔧 Total Services</h5>
										<h3 class="text-warning"><?php echo $totalServices; ?></h3>
									</div>
								</div>
							</div>
						</div>
						
						<?php if ($auth->isAdmin()): ?>
						<div class="row mt-4">
							<div class="col-12">
								<div class="card neon-border">
									<div class="card-body text-center">
										<h5 class="card-title">⚙️ System Administration</h5>
										<p class="mb-3">Administrative tools and system updates</p>
										<a href="<?php echo BASE_PATH; ?>update_currency_to_ghs.php" class="btn btn-success">🔄 Update to Ghanaian Cedis (GH₵)</a>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="<?php echo BASE_PATH; ?>assets/js/modern-effects.js"></script>
	<script src="<?php echo BASE_PATH; ?>assets/js/modal-fixes.js"></script>
</body>
</html>
