<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if (!$auth->isLoggedIn()) {
	echo json_encode(['success' => false, 'error' => 'Unauthorized']);
	exit;
}

$otherId = intval($_GET['other_id'] ?? 0);
$afterId = intval($_GET['after_id'] ?? 0);

if ($otherId <= 0) {
	echo json_encode(['success' => false, 'error' => 'Missing other_id']);
	exit;
}

try {
	$message = new Message($pdo);
	$userId = intval($auth->getCurrentUser()['id']);
	
	$rows = $message->fetchConversation($userId, $otherId, $afterId);
	
	$lastId = 0;
	if (!empty($rows)) { 
		$lastId = intval($rows[count($rows)-1]['id']); 
	}
	
	if ($lastId > 0) { 
		$message->markAsReadUpTo($userId, $otherId, $lastId);
	}

	echo json_encode(['success' => true, 'messages' => $rows, 'last_id' => $lastId]);
	
} catch (Exception $e) {
	echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

