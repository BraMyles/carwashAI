<?php
class Message {
	private PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
		// Ensure the messages table exists on new environments
		$this->createTableIfNotExists();
	}

	public function createTableIfNotExists(): void {
		$sql = "CREATE TABLE IF NOT EXISTS messages (
			id INT PRIMARY KEY AUTO_INCREMENT,
			sender_id INT NOT NULL,
			recipient_id INT NOT NULL,
			content TEXT NOT NULL,
			is_read TINYINT(1) DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
			FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
			INDEX idx_messages_pair (sender_id, recipient_id, created_at),
			INDEX idx_messages_recipient (recipient_id, is_read)
		)";
		$this->pdo->exec($sql);
	}

	public function sendMessage(int $senderId, int $recipientId, string $content): bool {
		try {
			$stmt = $this->pdo->prepare("INSERT INTO messages (sender_id, recipient_id, content) VALUES (?, ?, ?)");
			return $stmt->execute([$senderId, $recipientId, $content]);
		} catch (Exception $e) {
			error_log("Message::sendMessage error: " . $e->getMessage());
			return false;
		}
	}

	public function fetchConversation(int $userId, int $otherId, int $afterId = 0): array {
		try {
			$sql = "SELECT m.id, m.sender_id, m.recipient_id, m.content, m.created_at, u.full_name AS sender_name
				FROM messages m
				JOIN users u ON u.id = m.sender_id
				WHERE ((m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?))
				AND m.id > ?
				ORDER BY m.id ASC
				LIMIT 200";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([$userId, $otherId, $otherId, $userId, $afterId]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
			return $rows;
		} catch (Exception $e) {
			error_log("Message::fetchConversation error: " . $e->getMessage());
			return [];
		}
	}

	public function markAsReadUpTo(int $userId, int $otherId, int $upToId): void {
		try {
			$sql = "UPDATE messages SET is_read = 1
				WHERE recipient_id = ? AND sender_id = ? AND id <= ?";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([$userId, $otherId, $upToId]);
		} catch (Exception $e) {
			error_log("Message::markAsReadUpTo error: " . $e->getMessage());
		}
	}
}
?>

