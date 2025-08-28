<?php
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($data) {
        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, password, email, full_name, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['username'],
                $hashedPassword,
                $data['email'],
                $data['full_name'],
                $data['role'] ?? 'staff'
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, full_name, role, is_active FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("User retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function toggleStatus($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("User status toggle error: " . $e->getMessage());
            return false;
        }
    }
}
?>
