<?php
/**
 * Customer Management Class
 * Car Wash Management System
 */

class Customer {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO customers (full_name, phone, email, vehicle_type, vehicle_model, vehicle_color, license_plate, address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['full_name'],
                $data['phone'],
                $data['email'] ?? null,
                $data['vehicle_type'] ?? null,
                $data['vehicle_model'] ?? null,
                $data['vehicle_color'] ?? null,
                $data['license_plate'] ?? null,
                $data['address'] ?? null
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Customer creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Customer retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($page = 1, $limit = 20, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE full_name LIKE ? OR phone LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }
            
            $stmt = $this->pdo->prepare("
                SELECT * FROM customers 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Customer retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalCount($search = '') {
        try {
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE full_name LIKE ? OR phone LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm];
            }
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM customers $whereClause");
            $stmt->execute($params);
            $result = $stmt->fetch();
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Customer count error: " . $e->getMessage());
            return 0;
        }
    }
}
?>
