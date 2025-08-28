<?php
require_once 'includes/init.php';

// Debug: Check if user is logged in
if (!$auth->isLoggedIn()) {
	header("Location: " . BASE_PATH . "index.php");
	exit();
}

// Debug: Check user role
$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
	header("Location: " . BASE_PATH . "index.php");
	exit();
}

// For debugging, allow admin access too
if (!$auth->isStaff() && !$auth->isAdmin()) {
	header("Location: " . BASE_PATH . "index.php");
	exit();
}

$message = '';

// Actions: create, status update, reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
		$message = 'Invalid request. Please refresh and try again.';
	} else {
		$action = $_POST['action'] ?? '';
		if ($action === 'create') {
			$data = [
				'customer_id' => intval($_POST['customer_id']),
				'service_id' => intval($_POST['service_id']),
				'staff_id' => intval($currentUser['id']),
				'booking_date' => $_POST['booking_date'],
				'booking_time' => $_POST['booking_time'],
				'notes' => trim($_POST['notes'] ?? '')
			];
			// Check slot availability for clearer feedback
			if (!$booking->isSlotAvailable($data['booking_date'], $data['booking_time'])) {
				$alts = $booking->getAvailableSlots($data['booking_date']);
				$message = 'Selected slot is not available. ' . (empty($alts) ? 'No free slots left for this date.' : ('Available: ' . implode(', ', $alts)));
			} else {
				$newId = $booking->create($data);
				if ($newId) {
					// Send booking confirmation email
					require_once 'includes/email.php';
					$custStmt = $pdo->prepare("SELECT full_name, email FROM customers WHERE id = ?");
					$custStmt->execute([$data['customer_id']]);
					$cust = $custStmt->fetch();
					$svcStmt = $pdo->prepare("SELECT service_name FROM services WHERE id = ?");
					$svcStmt->execute([$data['service_id']]);
					$svc = $svcStmt->fetch();
					if ($cust && !empty($cust['email'])) {
						sendSystemEmail('booking_confirmation', $cust['email'], [
							'customer_name' => $cust['full_name'],
							'service_name' => $svc['service_name'] ?? 'Service',
							'booking_date' => $data['booking_date'],
							'booking_time' => $data['booking_time']
						]);
					}
					$message = 'Booking created successfully.';
				} else {
					$message = 'Failed to create booking.';
				}
			}
		} elseif ($action === 'status' && isset($_POST['id'], $_POST['status'])) {
			$id = intval($_POST['id']);
			$status = $_POST['status'];
			if ($booking->updateStatus($id, $status)) {
				$message = 'Booking status updated.';
			} else {
				$message = 'Failed to update status.';
			}
		} elseif ($action === 'reschedule' && isset($_POST['id'])) {
			$id = intval($_POST['id']);
			$newDate = $_POST['new_date'];
			$newTime = $_POST['new_time'];
			$notes = trim($_POST['notes'] ?? '');
			// Pre-check availability for better feedback
			if (!$booking->isSlotAvailable($newDate, $newTime, $id)) {
				$alts = $booking->getAvailableSlots($newDate);
				$message = 'Cannot reschedule: slot is taken. ' . (empty($alts) ? 'No free slots left for this date.' : ('Available: ' . implode(', ', $alts)));
			} else if ($booking->reschedule($id, $newDate, $newTime, $notes)) {
				$message = 'Booking rescheduled.';
			} else {
				$message = 'Failed to reschedule.';
			}
		}
	}
}

// Filters
$dateFilter = $_GET['date'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$preSelectedCustomer = $_GET['customer_id'] ?? '';

$bookings = $booking->getAll($dateFilter ?: null, $statusFilter ?: null, null) ?: [];
$customersList = $customer->getAll(1, 100, '') ?: [];
$servicesList = $service->getAll() ?: [];
$availableSlots = [];
if ($dateFilter) {
	$availableSlots = $booking->getAvailableSlots($dateFilter);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Car Wash</title>
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
						<h2 class="mb-0">Bookings</h2>
					</div>
					<div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Bookings</h2>
                    <div>
                        <a class="btn btn-outline-secondary me-2" href="<?php echo BASE_PATH; ?>calendar.php">Calendar View</a>
                        <button class="btn btn-modern" data-bs-toggle="modal" data-bs-target="#bookingModal">📅 New Booking</button>
                    </div>
                </div>
                <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                

                <div class="glass-card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET">
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All</option>
                                    <?php foreach (['pending','confirmed','in_progress','completed','cancelled'] as $st): ?>
                                    <option value="<?php echo $st; ?>" <?php echo $statusFilter===$st?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$st)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-secondary me-2" type="submit">Filter</button>
                                <a class="btn btn-outline-secondary" href="<?php echo BASE_PATH; ?>bookings.php">Clear</a>
                            </div>
                        </form>
                        <?php if ($dateFilter): ?>
                        <hr>
                        <div>
                            <strong>Available slots for <?php echo htmlspecialchars($dateFilter); ?>:</strong>
                            <?php if (empty($availableSlots)): ?>
                                <span class="badge bg-secondary">No free slots</span>
                            <?php else: ?>
                                <?php foreach ($availableSlots as $slot): ?>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($slot); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-header" style="background: var(--dark-gradient); border-bottom: 1px solid var(--glass-border);">
                        <h5 class="mb-0 text-white">📅 Bookings (<?php echo count($bookings); ?> found)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center text-muted py-4">
                                <p>No bookings found.</p>
                                <p>Try creating a new booking or adjusting your filters.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Staff</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($b['service_name']); ?></td>
                                        <td><?php echo formatDate($b['booking_date']); ?></td>
                                        <td><?php echo formatTime($b['booking_time']); ?></td>
                                        <td><?php echo getStatusBadge($b['status']); ?></td>
                                        <td><?php echo htmlspecialchars($b['staff_name']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                                                    <input type="hidden" name="action" value="status">
                                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button class="btn btn-outline-primary" type="submit">Confirm</button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                                                    <input type="hidden" name="action" value="status">
                                                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button class="btn btn-outline-danger" type="submit">Cancel</button>
                                                </form>
                                                <button 
                                                    class="btn btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rescheduleModal"
                                                    data-id="<?php echo $b['id']; ?>"
                                                    data-date="<?php echo $b['booking_date']; ?>"
                                                    data-time="<?php echo $b['booking_time']; ?>"
                                                >Reschedule</button>
                                            </div>
                                        </td>
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

    <!-- New Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select customer</option>
                                <?php foreach ($customersList as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($preSelectedCustomer == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['full_name']).' - '.htmlspecialchars($c['phone']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service</label>
                            <select class="form-select" name="service_id" required>
                                <option value="">Select service</option>
                                <?php foreach ($servicesList as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['service_name']).' ('.formatCurrency($s['price']).')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="booking_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Time</label>
                                <input type="time" class="form-control" name="booking_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                        <input type="hidden" name="action" value="reschedule">
                        <input type="hidden" name="id" id="rs-id">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">New Date</label>
                                <input type="date" class="form-control" name="new_date" id="rs-date" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">New Time</label>
                                <input type="time" class="form-control" name="new_time" id="rs-time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/modern-effects.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/modal-enhancements.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/modal-fixes.js"></script>
    <script>
    var rsModal = document.getElementById('rescheduleModal');
    rsModal && rsModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('rs-id').value = button.getAttribute('data-id');
        document.getElementById('rs-date').value = button.getAttribute('data-date');
        document.getElementById('rs-time').value = button.getAttribute('data-time');
    });
    
    // Auto-open booking modal if customer is pre-selected
    <?php if ($preSelectedCustomer): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        bookingModal.show();
    });
    <?php endif; ?>
    </script>
	<script>
	// Modal interactivity and background suppression
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
			// Hard fallback
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

		['bookingModal','rescheduleModal'].forEach(function(id){
			var m = document.getElementById(id);
			if (!m) return;
			m.addEventListener('show.bs.modal', function(){ disableBackgroundLayers(true); setTimeout(()=>forceEnableModal(this), 50); });
			m.addEventListener('shown.bs.modal', function(){ setTimeout(()=>forceEnableModal(this), 100); });
			m.addEventListener('hide.bs.modal', function(){ disableBackgroundLayers(false); });
		});
	});
	</script>
</body>
</html>
