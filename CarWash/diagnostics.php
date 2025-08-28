<?php
// Diagnostics page (safe to delete after use)
session_start();
require_once __DIR__ . '/config/database.php';

$results = [
    'php' => [
        'version' => PHP_VERSION,
        'pdo_mysql_loaded' => extension_loaded('pdo_mysql') ? 'yes' : 'no',
        'session_ok' => 'yes'
    ],
    'db' => [
        'connected' => 'no',
        'error' => '',
        'server_version' => '',
        'database' => DB_NAME,
        'host' => DB_HOST,
        'port' => defined('DB_PORT') ? DB_PORT : '3306'
    ],
    'tables' => [],
    'admin' => [
        'exists' => 'no',
        'is_active' => 'no',
        'matches_admin123' => 'no',
        'matches_password' => 'no'
    ]
];

try {
    $pdo = getDBConnection();
    $results['db']['connected'] = 'yes';
    $results['db']['server_version'] = $pdo->query('SELECT VERSION()')->fetchColumn();

    // Check expected tables and counts
    $expected = ['users','customers','services','bookings','payments','service_history','email_templates'];
    foreach ($expected as $tbl) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
            $results['tables'][$tbl] = ['exists' => 'yes', 'count' => (int)$count];
        } catch (Throwable $e) {
            $results['tables'][$tbl] = ['exists' => 'no', 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // Admin row and hash verification against common defaults
    try {
        $stmt = $pdo->prepare("SELECT id, username, is_active, password FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        if ($row = $stmt->fetch()) {
            $results['admin']['exists'] = 'yes';
            $results['admin']['is_active'] = ((int)$row['is_active'] === 1) ? 'yes' : 'no';
            $hash = $row['password'];
            $results['admin']['matches_admin123'] = password_verify('admin123', $hash) ? 'yes' : 'no';
            $results['admin']['matches_password'] = password_verify('password', $hash) ? 'yes' : 'no';
        }
    } catch (Throwable $e) {
        // ignore
    }
} catch (Throwable $e) {
    $results['db']['error'] = $e->getMessage();
}

function badge($yesno) {
    if ($yesno === 'yes') return '<span class="badge bg-success">yes</span>';
    if ($yesno === 'no') return '<span class="badge bg-danger">no</span>';
    return htmlspecialchars($yesno);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Car Wash System Diagnostics</h2>
        <a class="btn btn-outline-secondary" href="login.php">Go to Login</a>
    </div>

    <div class="card mb-3">
        <div class="card-header">Environment</div>
        <div class="card-body">
            <div>PHP Version: <strong><?php echo htmlspecialchars($results['php']['version']); ?></strong></div>
            <div>pdo_mysql loaded: <?php echo badge($results['php']['pdo_mysql_loaded']); ?></div>
            <div>Session: <?php echo badge($results['php']['session_ok']); ?></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Database</div>
        <div class="card-body">
            <div>Connected: <?php echo badge($results['db']['connected']); ?> <?php if ($results['db']['error']) echo '<div class="text-danger">'.htmlspecialchars($results['db']['error']).'</div>'; ?></div>
            <div>Server Version: <strong><?php echo htmlspecialchars($results['db']['server_version']); ?></strong></div>
            <div>DSN: <code><?php echo htmlspecialchars($results['db']['host'] . ':' . $results['db']['port'] . '/' . $results['db']['database']); ?></code></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Tables</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Table</th><th>Exists</th><th>Row Count</th><th>Error</th></tr></thead>
                    <tbody>
                        <?php foreach ($results['tables'] as $name => $info): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($name); ?></td>
                            <td><?php echo badge($info['exists']); ?></td>
                            <td><?php echo isset($info['count']) ? (int)$info['count'] : 0; ?></td>
                            <td class="text-danger"><?php echo isset($info['error']) ? htmlspecialchars($info['error']) : ''; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Admin Account</div>
        <div class="card-body">
            <div>Exists: <?php echo badge($results['admin']['exists']); ?></div>
            <div>Active: <?php echo badge($results['admin']['is_active']); ?></div>
            <div>Hash matches "admin123": <?php echo badge($results['admin']['matches_admin123']); ?></div>
            <div>Hash matches "password": <?php echo badge($results['admin']['matches_password']); ?></div>
            <div class="mt-2">
                <a class="btn btn-outline-danger btn-sm" href="reset_admin.php">Reset admin password to admin123</a>
            </div>
        </div>
    </div>

    <div class="alert alert-warning">
        This page is for diagnostics only. Delete <code>diagnostics.php</code> after use.
    </div>
</div>
</body>
</html>

