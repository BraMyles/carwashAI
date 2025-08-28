<?php
require_once __DIR__ . '/includes/init.php';

if (!$auth->isLoggedIn()) {
    // Allow running without login, but protect with CSRF-like token in URL if desired
}

try {
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin' LIMIT 1");
    $stmt->execute([$newHash]);
    echo "<p style='font-family: sans-serif'>Admin password has been reset to <strong>admin123</strong>.</p>";
    echo "<p style='font-family: sans-serif'>Please delete this file: <code>reset_admin.php</code> after use.</p>";
    echo "<p style='font-family: sans-serif'><a href='login.php'>Go to login</a></p>";
} catch (Exception $e) {
    echo "<p style='font-family: sans-serif; color: red'>Failed to reset password: " . htmlspecialchars($e->getMessage()) . "</p>";
}

