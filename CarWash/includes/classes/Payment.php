<?php
class Payment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO payments (booking_id, amount, payment_method, status, transaction_reference)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['booking_id'],
                $data['amount'],
                $data['payment_method'],
                $data['status'] ?? 'pending',
                $data['transaction_reference'] ?? null
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Payment creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, b.booking_date, b.booking_time,
                       c.full_name as customer_name, s.service_name
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN customers c ON b.customer_id = c.id
                JOIN services s ON b.service_id = s.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Payment retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getByBookingId($bookingId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Payment retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Payment status update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getDailyRevenue($date = null) {
        try {
            if ($date) {
                $stmt = $this->pdo->prepare("
                SELECT SUM(amount) as total_revenue, COUNT(*) as total_payments
                FROM payments 
                WHERE DATE(payment_date) = ? AND status = 'completed'
            ");
                $stmt->execute([$date]);
                return $stmt->fetch();
            } else {
                $stmt = $this->pdo->query("
                SELECT SUM(amount) as total_revenue, COUNT(*) as total_payments
                FROM payments 
                WHERE DATE(payment_date) = CURDATE() AND status = 'completed'
            ");
                return $stmt->fetch();
            }
        } catch (PDOException $e) {
            error_log("Daily revenue error: " . $e->getMessage());
            return ['total_revenue' => 0, 'total_payments' => 0];
        }
    }
    
    public function getPaymentMethods($date = null) {
        try {
            $whereClause = "WHERE status = 'completed'";
            $params = [];
            
            if ($date) {
                $whereClause .= " AND DATE(payment_date) = ?";
                $params[] = $date;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT payment_method, COUNT(*) as count, SUM(amount) as total
                FROM payments 
                $whereClause
                GROUP BY payment_method
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Payment methods error: " . $e->getMessage());
            return [];
        }
    }
}
?>

