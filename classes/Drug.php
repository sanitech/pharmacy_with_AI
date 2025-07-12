<?php
require_once __DIR__ . '/../config/database.php';

class Drug {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllDrugs($search = '', $category_id = null, $limit = 50, $offset = 0) {
        try {
            $where_conditions = ["d.is_active = 1"];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(d.name LIKE :search OR d.generic_name LIKE :search OR d.description LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if ($category_id) {
                $where_conditions[] = "d.category_id = :category_id";
                $params[':category_id'] = $category_id;
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $query = "SELECT d.*, c.name as category_name 
                      FROM drugs d 
                      LEFT JOIN drug_categories c ON d.category_id = c.id 
                      WHERE $where_clause 
                      ORDER BY d.name 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getDrugById($id) {
        try {
            $query = "SELECT d.*, c.name as category_name 
                      FROM drugs d 
                      LEFT JOIN drug_categories c ON d.category_id = c.id 
                      WHERE d.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function addDrug($data) {
        try {
            $query = "INSERT INTO drugs (name, generic_name, category_id, description, dosage_form, 
                      strength, manufacturer, price, cost_price, stock_quantity, reorder_level, is_prescription_required) 
                      VALUES (:name, :generic_name, :category_id, :description, :dosage_form, 
                      :strength, :manufacturer, :price, :cost_price, :stock_quantity, :reorder_level, :is_prescription_required)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':generic_name', $data['generic_name']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':dosage_form', $data['dosage_form']);
            $stmt->bindParam(':strength', $data['strength']);
            $stmt->bindParam(':manufacturer', $data['manufacturer']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':cost_price', $data['cost_price']);
            $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
            $stmt->bindParam(':reorder_level', $data['reorder_level']);
            $stmt->bindParam(':is_prescription_required', $data['is_prescription_required']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to add drug'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateDrug($id, $data) {
        try {
            $query = "UPDATE drugs SET name = :name, generic_name = :generic_name, category_id = :category_id, 
                      description = :description, dosage_form = :dosage_form, strength = :strength, 
                      manufacturer = :manufacturer, price = :price, cost_price = :cost_price, 
                      stock_quantity = :stock_quantity, reorder_level = :reorder_level, 
                      is_prescription_required = :is_prescription_required 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':generic_name', $data['generic_name']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':dosage_form', $data['dosage_form']);
            $stmt->bindParam(':strength', $data['strength']);
            $stmt->bindParam(':manufacturer', $data['manufacturer']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':cost_price', $data['cost_price']);
            $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
            $stmt->bindParam(':reorder_level', $data['reorder_level']);
            $stmt->bindParam(':is_prescription_required', $data['is_prescription_required']);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update drug'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteDrug($id) {
        try {
            $query = "UPDATE drugs SET is_active = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to delete drug'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateStock($id, $quantity, $operation = 'add') {
        try {
            if ($operation === 'add') {
                $query = "UPDATE drugs SET stock_quantity = stock_quantity + :quantity WHERE id = :id";
            } else {
                $query = "UPDATE drugs SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':quantity', $quantity);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update stock'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getLowStockDrugs($limit = null) {
        try {
            $query = "SELECT d.*, c.name as category_name 
                      FROM drugs d 
                      LEFT JOIN drug_categories c ON d.category_id = c.id 
                      WHERE d.stock_quantity <= d.reorder_level AND d.is_active = 1 
                      ORDER BY d.stock_quantity ASC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getExpiringDrugs($days = 30) {
        try {
            $query = "SELECT d.name, d.strength, db.batch_number, db.expiry_date, db.quantity 
                      FROM drug_batches db 
                      JOIN drugs d ON db.drug_id = d.id 
                      WHERE db.expiry_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY) 
                      AND db.quantity > 0 
                      ORDER BY db.expiry_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getAllCategories() {
        try {
            $query = "SELECT * FROM drug_categories ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getTotalDrugs() {
        try {
            $query = "SELECT COUNT(*) as total FROM drugs WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getRecentDrugs($limit = 5) {
        try {
            $query = "SELECT d.*, c.name as category_name 
                      FROM drugs d 
                      LEFT JOIN drug_categories c ON d.category_id = c.id 
                      WHERE d.is_active = 1 
                      ORDER BY d.created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getInventoryReport() {
        try {
            $query = "SELECT d.name, d.strength, d.dosage_form, c.name as category, 
                      d.stock_quantity, d.reorder_level, d.price, d.cost_price,
                      COALESCE(db.expiry_date, 'N/A') as expiry_date
                      FROM drugs d 
                      LEFT JOIN drug_categories c ON d.category_id = c.id
                      LEFT JOIN drug_batches db ON d.id = db.drug_id
                      WHERE d.is_active = 1 
                      ORDER BY d.stock_quantity ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function addCategory($name, $description = '') {
        try {
            $query = "INSERT INTO drug_categories (name, description) VALUES (:name, :description)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to add category'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateCategory($id, $name, $description = '') {
        try {
            $query = "UPDATE drug_categories SET name = :name, description = :description WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update category'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteCategory($id) {
        try {
            // Check if category has drugs
            $check_query = "SELECT COUNT(*) as count FROM drugs WHERE category_id = :id AND is_active = 1";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete category that contains drugs'];
            }
            
            $query = "DELETE FROM drug_categories WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to delete category'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getAllCategoriesWithCount() {
        try {
            $query = "SELECT c.*, COUNT(d.id) as drugs_count 
                      FROM drug_categories c 
                      LEFT JOIN drugs d ON c.id = d.category_id AND d.is_active = 1
                      GROUP BY c.id 
                      ORDER BY c.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
