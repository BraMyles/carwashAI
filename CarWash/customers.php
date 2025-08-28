<?php
require_once 'includes/init.php';
$auth->requireAuth();

$message = '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Handle customer creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
	if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
		$message = 'Invalid request. Please refresh and try again.';
	} else if ($_POST['action'] === 'create') {
		$fullName = trim($_POST['full_name'] ?? '');
		$phone = trim($_POST['phone'] ?? '');
		if ($fullName === '' || $phone === '') {
			$message = 'Full name and phone are required.';
		} else {
			$customerData = [
				'full_name' => $fullName,
				'phone' => $phone,
				'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
				'vehicle_type' => $_POST['vehicle_type'] ?? null,
				'vehicle_model' => $_POST['vehicle_model'] ?? null,
				'vehicle_color' => $_POST['vehicle_color'] ?? null,
				'license_plate' => $_POST['license_plate'] ?? null,
				'address' => $_POST['address'] ?? null
			];
			
			if ($customer->create($customerData)) {
				$message = 'Customer created successfully!';
			} else {
				$message = 'Error creating customer.';
			}
		}
	}
}

// Get customers with pagination
$customers = $customer->getAll($page, 20, $search);
$totalCustomers = $customer->getTotalCount($search);
$totalPages = ceil($totalCustomers / 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Customers - Car Wash Management System</title>
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
						<a class="nav-link active" href="<?php echo BASE_PATH; ?>customers.php">👥 Customers</a>
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
							<h2 class="mb-0">Customer Management</h2>
							<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
								👤 Add Customer
							</button>
						</div>
					</div>
					<div class="card-body">
				
						<?php if ($message): ?>
							<div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
						<?php endif; ?>
						
						<!-- Search Bar -->
						<div class="card mb-4">
							<div class="card-body">
								<form method="GET" class="row g-3">
									<div class="col-md-6">
										<input type="text" class="form-control" name="search" 
											   placeholder="🔍 Search by name or phone..." 
											   value="<?php echo htmlspecialchars($search); ?>">
									</div>
									<div class="col-md-3">
										<button type="submit" class="btn btn-primary">🔍 Search</button>
										<?php if ($search): ?>
											<a href="<?php echo BASE_PATH; ?>customers.php" class="btn btn-secondary">❌ Clear</a>
										<?php endif; ?>
									</div>
								</form>
							</div>
						</div>
						
						<!-- Customers Table -->
						<div class="card">
							<div class="card-header">
								<h5 class="mb-0">👥 Customers (<?php echo $totalCustomers; ?>)</h5>
							</div>
							<div class="card-body">
						<?php if (empty($customers)): ?>
							<p class="text-center text-muted">No customers found.</p>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>Name</th>
											<th>Phone</th>
											<th>Email</th>
											<th>Vehicle</th>
											<th>License Plate</th>
											<th>Created</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($customers as $customerData): ?>
										<tr>
											<td><?php echo htmlspecialchars($customerData['full_name']); ?></td>
											<td><?php echo htmlspecialchars($customerData['phone']); ?></td>
											<td><?php echo htmlspecialchars($customerData['email'] ?? '-'); ?></td>
											<td>
												<?php if ($customerData['vehicle_type']): ?>
													<?php echo htmlspecialchars($customerData['vehicle_type']); ?>
													<?php if ($customerData['vehicle_model']): ?>
														- <?php echo htmlspecialchars($customerData['vehicle_model']); ?>
													<?php endif; ?>
												<?php else: ?>
													-
												<?php endif; ?>
											</td>
											<td><?php echo htmlspecialchars($customerData['license_plate'] ?? '-'); ?></td>
											<td><?php echo formatDate($customerData['created_at']); ?></td>
											<td>
												<button class="btn btn-sm btn-info" onclick="viewCustomer(<?php echo $customerData['id']; ?>)">👁️ View</button>
												<a href="<?php echo BASE_PATH; ?>bookings.php?customer_id=<?php echo $customerData['id']; ?>" class="btn btn-sm btn-success">📅 Book</a>
											</td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							
							<!-- Pagination -->
							<?php if ($totalPages > 1): ?>
								<nav>
									<ul class="pagination justify-content-center">
										<?php for ($i = 1; $i <= $totalPages; $i++): ?>
											<li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
												<a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
													<?php echo $i; ?>
												</a>
											</li>
										<?php endfor; ?>
									</ul>
								</nav>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Add Customer Modal -->
	<div class="modal fade" id="addCustomerModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Add New Customer</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form method="POST">
					<div class="modal-body">
						<input type="hidden" name="action" value="create">
						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Full Name *</label>
								<input type="text" class="form-control" name="full_name" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Phone *</label>
								<input type="tel" class="form-control" name="phone" required>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="email">
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">License Plate</label>
								<input type="text" class="form-control" name="license_plate">
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Vehicle Type</label>
								<select class="form-select" name="vehicle_type">
									<option value="">Select Type</option>
									<option value="Sedan">Sedan</option>
									<option value="SUV">SUV</option>
									<option value="Truck">Truck</option>
									<option value="Van">Van</option>
									<option value="Motorcycle">Motorcycle</option>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Vehicle Model</label>
								<input type="text" class="form-control" name="vehicle_model">
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Vehicle Color</label>
								<input type="text" class="form-control" name="vehicle_color">
							</div>
						</div>
						
						<div class="mb-3">
							<label class="form-label">Address</label>
							<textarea class="form-control" name="address" rows="2"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Add Customer</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<!-- View Customer Modal -->
	<div class="modal fade" id="viewCustomerModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Customer Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" id="customerDetails">
					<!-- Customer details will be loaded here -->
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="<?php echo BASE_PATH; ?>assets/js/modern-effects.js"></script>
	<script>
		function viewCustomer(customerId) {
			// Show loading
			document.getElementById('customerDetails').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading...</p></div>';
			
			// Show modal
			const modal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
			modal.show();
			
			// Fetch customer details
			fetch('<?php echo BASE_PATH; ?>api/get_customer.php?id=' + customerId)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						const customer = data.customer;
						document.getElementById('customerDetails').innerHTML = `
							<div class="row">
								<div class="col-md-6">
									<h6>Personal Information</h6>
									<p><strong>Name:</strong> ${customer.full_name}</p>
									<p><strong>Phone:</strong> ${customer.phone}</p>
									<p><strong>Email:</strong> ${customer.email || '-'}</p>
									<p><strong>Address:</strong> ${customer.address || '-'}</p>
								</div>
								<div class="col-md-6">
									<h6>Vehicle Information</h6>
									<p><strong>Type:</strong> ${customer.vehicle_type || '-'}</p>
									<p><strong>Model:</strong> ${customer.vehicle_model || '-'}</p>
									<p><strong>Color:</strong> ${customer.vehicle_color || '-'}</p>
									<p><strong>License Plate:</strong> ${customer.license_plate || '-'}</p>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="col-12">
									<h6>Service History</h6>
									<div id="serviceHistory">
										<div class="text-center text-muted">Loading service history...</div>
									</div>
								</div>
							</div>
						`;
						
						// Load service history
						loadServiceHistory(customerId);
					} else {
						document.getElementById('customerDetails').innerHTML = '<div class="alert alert-danger">Error loading customer details.</div>';
					}
				})
				.catch(error => {
					document.getElementById('customerDetails').innerHTML = '<div class="alert alert-danger">Error loading customer details.</div>';
				});
		}
		
		function loadServiceHistory(customerId) {
			fetch('<?php echo BASE_PATH; ?>api/get_customer_history.php?id=' + customerId)
				.then(response => response.json())
				.then(data => {
					const historyDiv = document.getElementById('serviceHistory');
					if (data.success && data.history.length > 0) {
						let html = '<div class="table-responsive"><table class="table table-sm">';
						html += '<thead><tr><th>Date</th><th>Service</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
						data.history.forEach(service => {
							html += `<tr>
								<td>${service.service_date}</td>
								<td>${service.service_name}</td>
								<td>$${service.amount}</td>
								<td><span class="badge bg-${service.status === 'completed' ? 'success' : 'warning'}">${service.status}</span></td>
							</tr>`;
						});
						html += '</tbody></table></div>';
						historyDiv.innerHTML = html;
					} else {
						historyDiv.innerHTML = '<p class="text-muted">No service history found.</p>';
					}
				})
				.catch(error => {
					document.getElementById('serviceHistory').innerHTML = '<p class="text-muted">Error loading service history.</p>';
				});
		}
	</script>
</body>
</html>
