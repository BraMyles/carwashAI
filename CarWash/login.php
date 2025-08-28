<?php
require_once 'includes/init.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            if ($auth->login($username, $password)) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - Car Wash Management System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/modern-style.css" rel="stylesheet">
	<style>
		body {
			background: var(--primary-gradient);
			min-height: 100vh;
			display: flex;
			align-items: center;
		}
		.login-card {
			background: white;
			border-radius: 15px;
			box-shadow: 0 15px 35px rgba(0,0,0,0.1);
			overflow: hidden;
		}
		.login-header {
			background: var(--primary-gradient);
			color: white;
			padding: 2rem;
			text-align: center;
		}
		.login-body {
			padding: 2rem;
		}
		.form-control {
			border-radius: 10px;
			border: 2px solid #e9ecef;
			padding: 12px 15px;
		}
		.form-control:focus {
			border-color: var(--primary-color);
			box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
		}
		.btn-login {
			background: var(--primary-gradient);
			border: none;
			border-radius: 10px;
			padding: 12px;
			font-weight: 600;
			width: 100%;
		}
		.btn-login:hover {
			filter: brightness(0.95);
		}
	</style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <h2 class="mb-0">🚗 Car Wash</h2>
                        <p class="mb-0">Management System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login">Login</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Default Admin: admin / admin123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
