<?php
require_once 'includes/init.php';
if (!$auth->isLoggedIn()) {
	header('Location: index.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Access Denied - Car Wash</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
	<div class="container py-5">
		<div class="row justify-content-center">
			<div class="col-md-6">
				<div class="card shadow-sm">
					<div class="card-body text-center p-5">
						<h2 class="mb-3">Access Denied</h2>
						<p class="text-muted">You don't have permission to access this page.</p>
						<a href="dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

