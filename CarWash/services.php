<?php
require_once 'includes/init.php';
$auth->requireStaff();

$isAdmin = $auth->isAdmin();
$message = '';

// Handle create/update/delete for admins
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $message = 'Invalid request. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $data = [
                'service_name' => trim($_POST['service_name']),
                'description' => trim($_POST['description'] ?? ''),
                'price' => max(0, (float)($_POST['price'])),
                'duration_minutes' => max(1, (int)($_POST['duration_minutes'] ?? 30))
            ];
            if ($data['service_name'] === '') {
                $message = 'Service name is required.';
            } else if ($service->create($data)) {
                $message = 'Service created successfully.';
            } else {
                $message = 'Failed to create service.';
            }
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $data = [
                'service_name' => trim($_POST['service_name']),
                'description' => trim($_POST['description'] ?? ''),
                'price' => max(0, (float)($_POST['price'])),
                'duration_minutes' => max(1, (int)($_POST['duration_minutes'] ?? 30))
            ];
            if ($data['service_name'] === '') {
                $message = 'Service name is required.';
            } else if ($service->update($id, $data)) {
                $message = 'Service updated successfully.';
            } else {
                $message = 'Failed to update service.';
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            if ($service->delete($id)) {
                $message = 'Service deleted successfully.';
            } else {
                $message = 'Failed to delete service.';
            }
        }
    }
}

$services = $service->getAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Services - Car Wash</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/ai-modern-theme.css" rel="stylesheet">
	<link href="assets/css/modal-fixes.css" rel="stylesheet">
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
			<a class="navbar-brand" href="dashboard.php">
				🚗 Car Wash AI
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a class="nav-link" href="dashboard.php">📊 Dashboard</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="customers.php">👥 Customers</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="bookings.php">📅 Bookings</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="services.php">🔧 Services</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="payments.php">💰 Payments</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="messages.php">💬 Messages</a>
					</li>
					<?php if ($isAdmin): ?>
					<li class="nav-item">
						<a class="nav-link" href="users.php">👤 Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="reports.php">📈 Reports</a>
					</li>
					<?php endif; ?>
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
							👤 <?php echo htmlspecialchars($auth->getCurrentUser()['full_name']); ?>
						</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="logout.php">🚪 Logout</a></li>
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
							<h2 class="mb-0">Service Packages</h2>
							<?php if ($isAdmin): ?>
							<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal" data-mode="create">+ Add Service</button>
							<?php endif; ?>
						</div>
					</div>
					<div class="card-body">
				<?php if ($message): ?>
				<div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
				<?php endif; ?>
				<div class="card glass-card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-hover table-modern">
								<thead>
									<tr>
										<th>Name</th>
										<th>Description</th>
										<th>Duration</th>
										<th>Price</th>
										<?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($services as $srv): ?>
									<tr>
										<td><?php echo htmlspecialchars($srv['service_name']); ?></td>
										<td><?php echo htmlspecialchars($srv['description'] ?? ''); ?></td>
										<td><?php echo intval($srv['duration_minutes']); ?> mins</td>
										<td><?php echo formatCurrency($srv['price']); ?></td>
										<?php if ($isAdmin): ?>
										<td>
											<button 
												class="btn btn-sm btn-outline-primary"
												data-bs-toggle="modal"
												data-bs-target="#serviceModal"
												data-mode="update"
												data-id="<?php echo $srv['id']; ?>"
												data-name="<?php echo htmlspecialchars($srv['service_name']); ?>"
												data-desc="<?php echo htmlspecialchars($srv['description'] ?? ''); ?>"
												data-price="<?php echo $srv['price']; ?>"
												data-duration="<?php echo $srv['duration_minutes']; ?>"
											>Edit</button>
											<form method="POST" class="d-inline" onsubmit="return confirm('Delete this service?');">
												<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
												<input type="hidden" name="action" value="delete">
												<input type="hidden" name="id" value="<?php echo $srv['id']; ?>">
												<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
											</form>
										</td>
										<?php endif; ?>
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

	<?php if ($isAdmin): ?>
	<!-- Service Modal -->
	<div class="modal fade" id="serviceModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Service</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form method="POST">
					<div class="modal-body">
						<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
						<input type="hidden" name="action" value="create" id="svc-action">
						<input type="hidden" name="id" id="svc-id">
						<div class="mb-3">
							<label class="form-label">Name</label>
							<input type="text" class="form-control" name="service_name" id="svc-name" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea class="form-control" name="description" id="svc-desc" rows="3"></textarea>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Price</label>
								<input type="number" step="0.01" class="form-control" name="price" id="svc-price" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Duration (mins)</label>
								<input type="number" class="form-control" name="duration_minutes" id="svc-duration" value="30">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
		var serviceModal = document.getElementById('serviceModal');
		serviceModal && serviceModal.addEventListener('show.bs.modal', function (event) {
			var button = event.relatedTarget;
			var mode = button.getAttribute('data-mode');
			document.getElementById('svc-action').value = mode === 'update' ? 'update' : 'create';
			document.getElementById('svc-id').value = button.getAttribute('data-id') || '';
			document.getElementById('svc-name').value = button.getAttribute('data-name') || '';
			document.getElementById('svc-desc').value = button.getAttribute('data-desc') || '';
			document.getElementById('svc-price').value = button.getAttribute('data-price') || '';
			document.getElementById('svc-duration').value = button.getAttribute('data-duration') || '30';
		});
	</script>
	<?php endif; ?>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/modal-enhancements.js"></script>
	<script src="assets/js/modal-fixes.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		function disableBackgroundLayers(disable) {
			try {
				var fe = document.querySelector('.floating-elements');
				var pc = document.querySelector('.particle-container');
				[fe, pc].forEach(function(el){ if (el) { el.style.display = disable ? 'none' : ''; }});
			} catch(e) {}
		}
		function forceEnableModal(modalEl) {
			try {
				if (window.ModalEnhancements && window.ModalEnhancements.forceEnableFormElementsInModal) {
					window.ModalEnhancements.forceEnableFormElementsInModal(modalEl);
				}
				if (window.ModalEnhancements && window.ModalEnhancements.fixModalPosition) {
					window.ModalEnhancements.fixModalPosition(modalEl);
				}
			} catch(e) {}
			modalEl.querySelectorAll('input, select, textarea, button, label').forEach(function(el){
				el.style.pointerEvents = 'auto';
				el.style.userSelect = 'auto';
				el.style.webkitUserSelect = 'auto';
				el.style.opacity = '1';
				el.style.visibility = 'visible';
				el.style.position = 'relative';
				el.style.zIndex = '12010';
				el.disabled = false;
				el.readOnly = false;
				el.removeAttribute('disabled');
				el.removeAttribute('readonly');
			});
			var first = modalEl.querySelector('input, select, textarea');
			first && first.focus();
		}
		var m = document.getElementById('serviceModal');
		if (m) {
			m.addEventListener('show.bs.modal', function(){ disableBackgroundLayers(true); setTimeout(()=>forceEnableModal(this), 50); });
			m.addEventListener('shown.bs.modal', function(){ setTimeout(()=>forceEnableModal(this), 100); });
			m.addEventListener('hide.bs.modal', function(){ disableBackgroundLayers(false); });
		}
	});
	</script>
</body>
</html>
