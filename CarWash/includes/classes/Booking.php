<?php
class Booking {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function isSlotAvailable($date, $time, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) AS cnt FROM bookings WHERE booking_date = ? AND booking_time = ? AND status IN ('pending','confirmed','in_progress')";
            $params = [$date, $time];
            if ($excludeId) {
                $sql .= " AND id <> ?";
                $params[] = $excludeId;
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return intval($row['cnt'] ?? 0) === 0;
        } catch (PDOException $e) {
            error_log("Slot availability error: " . $e->getMessage());
            // Fail closed (assume unavailable) to be safe
            return false;
        }
    }
    
    public function create($data) {
        try {
            if (!$this->isSlotAvailable($data['booking_date'], $data['booking_time'])) {
                return false;
            }
            $stmt = $this->pdo->prepare("
                INSERT INTO bookings (customer_id, service_id, staff_id, booking_date, booking_time, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['customer_id'],
                $data['service_id'],
                $data['staff_id'],
                $data['booking_date'],
                $data['booking_time'],
                $data['notes'] ?? null
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Booking creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.*, c.full_name as customer_name, c.phone as customer_phone,
                       s.service_name, s.price, u.full_name as staff_name
                FROM bookings b
                JOIN customers c ON b.customer_id = c.id
                JOIN services s ON b.service_id = s.id
                JOIN users u ON b.staff_id = u.id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Booking retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($date = null, $status = null, $staff_id = null) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($date) {
                $whereClause .= " AND b.booking_date = ?";
                $params[] = $date;
            }
            
            if ($status) {
                $whereClause .= " AND b.status = ?";
                $params[] = $status;
            }
            
            if ($staff_id) {
                $whereClause .= " AND b.staff_id = ?";
                $params[] = $staff_id;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT b.*, c.full_name as customer_name, c.phone as customer_phone,
                       s.service_name, s.price, u.full_name as staff_name
                FROM bookings b
                JOIN customers c ON b.customer_id = c.id
                JOIN services s ON b.service_id = s.id
                JOIN users u ON b.staff_id = u.id
                $whereClause
                ORDER BY b.booking_date ASC, b.booking_time ASC
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Booking retrieval error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            error_log("Booking status update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function reschedule($id, $newDate, $newTime, $notes = null) {
        try {
            if (!$this->isSlotAvailable($newDate, $newTime, $id)) {
                return false;
            }
            $stmt = $this->pdo->prepare("UPDATE bookings SET booking_date = ?, booking_time = ?, notes = ?, status = ? WHERE id = ?");
            return $stmt->execute([$newDate, $newTime, $notes, 'confirmed', $id]);
        } catch (PDOException $e) {
            error_log("Booking reschedule error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAvailableSlots($date) {
        try {
            // Get all time slots (9 AM to 6 PM, 30-minute intervals)
            $timeSlots = [];
            $startTime = strtotime('09:00');
            $endTime = strtotime('18:00');
            
            for ($time = $startTime; $time <= $endTime; $time += 1800) {
                $timeSlots[] = date('H:i', $time);
            }
            
            // Get booked slots for the date
            $stmt = $this->pdo->prepare("
                SELECT booking_time FROM bookings 
                WHERE booking_date = ? AND status IN ('pending', 'confirmed', 'in_progress')
            ");
            $stmt->execute([$date]);
            $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Return available slots
            return array_diff($timeSlots, $bookedSlots);
        } catch (PDOException $e) {
            error_log("Available slots error: " . $e->getMessage());
            return [];
        }
    }
}
?>
