<?php
require_once __DIR__ . '/../config/database.php';

class Prescription {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function uploadPrescription($customer_id, $symptoms, $prescription_file = null) {
        try {
            $query = "INSERT INTO prescriptions (customer_id, symptoms, prescription_file, status) 
                      VALUES (:customer_id, :symptoms, :prescription_file, 'pending')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':symptoms', $symptoms);
            $stmt->bindParam(':prescription_file', $prescription_file);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to upload prescription'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionsByCustomer($customer_id) {
        try {
            $query = "SELECT p.*, u.full_name as pharmacist_name 
                      FROM prescriptions p 
                      LEFT JOIN users u ON p.pharmacist_id = u.id 
                      WHERE p.customer_id = :customer_id 
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPendingPrescriptions() {
        try {
            $query = "SELECT p.*, u.full_name as customer_name, u.phone as customer_phone 
                      FROM prescriptions p 
                      JOIN users u ON p.customer_id = u.id 
                      WHERE p.status = 'pending' 
                      ORDER BY p.created_at ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionById($id) {
        try {
            $query = "SELECT p.*, u1.full_name as customer_name, u1.phone as customer_phone,
                      u2.full_name as pharmacist_name 
                      FROM prescriptions p 
                      JOIN users u1 ON p.customer_id = u1.id 
                      LEFT JOIN users u2 ON p.pharmacist_id = u2.id 
                      WHERE p.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function approvePrescription($id, $pharmacist_id, $diagnosis, $notes = '') {
        try {
            $query = "UPDATE prescriptions SET status = 'approved', pharmacist_id = :pharmacist_id, 
                      diagnosis = :diagnosis, notes = :notes, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':pharmacist_id', $pharmacist_id);
            $stmt->bindParam(':diagnosis', $diagnosis);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to approve prescription'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function rejectPrescription($id, $pharmacist_id, $notes = '') {
        try {
            $query = "UPDATE prescriptions SET status = 'rejected', pharmacist_id = :pharmacist_id, 
                      notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':pharmacist_id', $pharmacist_id);
            $stmt->bindParam(':notes', $notes);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to reject prescription'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function addPrescriptionItems($prescription_id, $items) {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO prescription_items (prescription_id, drug_id, dosage, frequency, duration, quantity, notes) 
                      VALUES (:prescription_id, :drug_id, :dosage, :frequency, :duration, :quantity, :notes)";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($items as $item) {
                $stmt->bindParam(':prescription_id', $prescription_id);
                $stmt->bindParam(':drug_id', $item['drug_id']);
                $stmt->bindParam(':dosage', $item['dosage']);
                $stmt->bindParam(':frequency', $item['frequency']);
                $stmt->bindParam(':duration', $item['duration']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':notes', $item['notes']);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add prescription item');
                }
            }
            
            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionItems($prescription_id) {
        try {
            $query = "SELECT pi.*, d.name as drug_name, d.strength, d.dosage_form 
                      FROM prescription_items pi 
                      JOIN drugs d ON pi.drug_id = d.id 
                      WHERE pi.prescription_id = :prescription_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':prescription_id', $prescription_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function markAsDispensed($id) {
        try {
            $query = "UPDATE prescriptions SET status = 'dispensed', updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to mark as dispensed'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = 'dispensed' THEN 1 ELSE 0 END) as dispensed
                      FROM prescriptions 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionsByStatus($status) {
        try {
            $query = "SELECT p.*, u.full_name as customer_name, u.phone as customer_phone,
                      u2.full_name as pharmacist_name 
                      FROM prescriptions p 
                      JOIN users u ON p.customer_id = u.id 
                      LEFT JOIN users u2 ON p.pharmacist_id = u2.id 
                      WHERE p.status = :status 
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getAllPrescriptions() {
        try {
            $query = "SELECT p.*, u.full_name as customer_name, u.phone as customer_phone,
                      u2.full_name as pharmacist_name 
                      FROM prescriptions p 
                      JOIN users u ON p.customer_id = u.id 
                      LEFT JOIN users u2 ON p.pharmacist_id = u2.id 
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPrescriptionReport($start_date = null, $end_date = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($start_date) {
                $where_conditions[] = "p.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $where_conditions[] = "p.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT 
                        DATE(p.created_at) as date,
                        COUNT(*) as total_prescriptions,
                        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN p.status = 'approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN p.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN p.status = 'dispensed' THEN 1 ELSE 0 END) as dispensed,
                        AVG(CASE WHEN p.status = 'approved' THEN 1 ELSE 0 END) * 100 as approval_rate
                      FROM prescriptions p 
                      $where_clause 
                      GROUP BY DATE(p.created_at) 
                      ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?> 