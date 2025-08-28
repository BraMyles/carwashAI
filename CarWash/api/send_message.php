<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if (!$auth->isLoggedIn()) {
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}

// Handle both JSON and POST form data
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
    } else {
        $input = $_POST;
    }
}

$token = $input['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
	echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
	exit;
}

$recipientId = intval($input['recipient_id'] ?? 0);
$content = trim($input['content'] ?? '');

if ($recipientId <= 0 || $content === '') {
	echo json_encode(['success' => false, 'error' => 'Missing fields']);
	exit;
}

try {
	$message = new Message($pdo);
	$senderId = intval($auth->getCurrentUser()['id']);
	
	$ok = $message->sendMessage($senderId, $recipientId, $content);
	echo json_encode(['success' => $ok]);
	
} catch (Exception $e) {
	echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

