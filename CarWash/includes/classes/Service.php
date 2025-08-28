<?php
class Service {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO services (service_name, description, price, duration_minutes)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['service_name'],
                $data['description'] ?? null,
                $data['price'],
                $data['duration_minutes'] ?? 30
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Service creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Service retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY service_name");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Service retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE services 
                SET service_name = ?, description = ?, price = ?, duration_minutes = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['service_name'],
                $data['description'] ?? null,
                $data['price'],
                $data['duration_minutes'] ?? 30,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Service update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Service deletion error: " . $e->getMessage());
            return false;
        }
    }
}
?>

