<?php
require_once 'includes/init.php';
$auth->requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $message = 'Invalid request. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $fullName = trim($_POST['full_name']);
            $role = $_POST['role'];
            if ($username === '' || $password === '' || !$email || $fullName === '') {
                $message = 'All fields are required and email must be valid.';
            } else if ($user->create([
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role
            ])) {
                $message = 'User created successfully.';
            } else {
                $message = 'Failed to create user.';
            }
        } elseif ($action === 'toggle') {
            $id = intval($_POST['id']);
            if ($user->toggleStatus($id)) {
                $message = 'User status updated.';
            } else {
                $message = 'Failed to update status.';
            }
        }
    }
}

$users = $user->getAll() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Car Wash</title>
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
					<li class="nav-item">
						<a class="nav-link active" href="<?php echo BASE_PATH; ?>users.php">👤 Users</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo BASE_PATH; ?>reports.php">📈 Reports</a>
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

	<div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white">Staff Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">👤 Add User</button>
            </div>
            <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="card neon-border">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($u['role'])); ?></td>
                                    <td><?php echo $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button class="btn btn-sm btn-warning" type="submit"><?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?></button>
                                        </form>
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

<!-- Add User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input class="form-control" type="text" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input class="form-control" type="text" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/modern-effects.js"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/modal-enhancements.js"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/modal-fixes.js"></script>
</body>
</html>
