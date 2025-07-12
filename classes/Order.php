<?php
require_once __DIR__ . '/../config/database.php';

class Order
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createOrder($customer_id, $items, $total_amount)
    {
        try {
            $this->conn->beginTransaction();

            // Generate unique order number
            $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create order
            $query = "INSERT INTO orders (customer_id, order_number, total_amount, status, payment_status) 
                      VALUES (:customer_id, :order_number, :total_amount, 'pending', 'pending')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':order_number', $order_number);
            $stmt->bindParam(':total_amount', $total_amount);

            if (!$stmt->execute()) {
                throw new Exception('Failed to create order');
            }

            $order_id = $this->conn->lastInsertId();

            // Add order items
            $item_query = "INSERT INTO order_items (order_id, drug_id, quantity, unit_price, total_price) 
                          VALUES (:order_id, :drug_id, :quantity, :unit_price, :total_price)";

            $item_stmt = $this->conn->prepare($item_query);

            foreach ($items as $item) {
                $item_stmt->bindParam(':order_id', $order_id);
                $item_stmt->bindParam(':drug_id', $item['drug_id']);
                $item_stmt->bindParam(':quantity', $item['quantity']);
                $item_stmt->bindParam(':unit_price', $item['unit_price']);
                $item_stmt->bindParam(':total_price', $item['total_price']);

                if (!$item_stmt->execute()) {
                    throw new Exception('Failed to add order item');
                }

                // Update stock
                $this->updateStock($item['drug_id'], $item['quantity'], 'subtract');
            }

            $this->conn->commit();
            return ['success' => true, 'order_id' => $order_id, 'order_number' => $order_number];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function updateStock($drug_id, $quantity, $operation)
    {
        try {
            if ($operation === 'add') {
                $query = "UPDATE drugs SET stock_quantity = stock_quantity + :quantity WHERE id = :drug_id";
            } else {
                $query = "UPDATE drugs SET stock_quantity = stock_quantity - :quantity WHERE id = :drug_id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':drug_id', $drug_id);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception('Failed to update stock: ' . $e->getMessage());
        }
    }

    public function getOrdersByCustomer($customer_id)
    {
        try {
            $query = "SELECT o.*, COUNT(oi.id) as item_count 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      WHERE o.customer_id = :customer_id 
                      GROUP BY o.id 
                      ORDER BY o.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getAllOrders($status = null, $limit = 50, $offset = 0)
    {
        try {
            $where_conditions = [];
            $params = [];

            if ($status) {
                $where_conditions[] = "o.status = :status";
                $params[':status'] = $status;
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $query = "SELECT o.*, u.full_name as customer_name, u.phone as customer_phone,
                      COUNT(oi.id) as item_count 
                      FROM orders o 
                      JOIN users u ON o.customer_id = u.id 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      $where_clause 
                      GROUP BY o.id 
                      ORDER BY o.created_at DESC 
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

    public function getOrderById($id)
    {
        try {
            $query = "SELECT o.*, u.full_name as customer_name, u.phone as customer_phone,
                      u2.full_name as cashier_name 
                      FROM orders o 
                      JOIN users u ON o.customer_id = u.id 
                      LEFT JOIN users u2 ON o.cashier_id = u2.id 
                      WHERE o.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getOrderItems($order_id)
    {
        try {
            $query = "SELECT oi.*, d.name as drug_name, d.strength, d.dosage_form 
                      FROM order_items oi 
                      JOIN drugs d ON oi.drug_id = d.id 
                      WHERE oi.order_id = :order_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateOrderStatus($id, $status, $cashier_id = null)
    {
        try {
            $query = "UPDATE orders SET status = :status";
            $params = [':id' => $id, ':status' => $status];

            if ($cashier_id) {
                $query .= ", cashier_id = :cashier_id";
                $params[':cashier_id'] = $cashier_id;
            }

            $query .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Failed to update order status'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function processPayment($order_id, $cashier_id, $payment_method, $final_amount, $tax_amount = 0, $discount_amount = 0)
    {
        try {
            $this->conn->beginTransaction();

            // Update order payment status
            $order_query = "UPDATE orders SET payment_status = 'paid', payment_method = :payment_method, 
                           cashier_id = :cashier_id, status = 'completed', updated_at = CURRENT_TIMESTAMP 
                           WHERE id = :order_id";

            $order_stmt = $this->conn->prepare($order_query);
            $order_stmt->bindParam(':order_id', $order_id);
            $order_stmt->bindParam(':payment_method', $payment_method);
            $order_stmt->bindParam(':cashier_id', $cashier_id);

            if (!$order_stmt->execute()) {
                throw new Exception('Failed to update order');
            }

            // Create sales record
            $sales_query = "INSERT INTO sales (order_id, cashier_id, total_amount, tax_amount, discount_amount, final_amount) 
                           VALUES (:order_id, :cashier_id, :total_amount, :tax_amount, :discount_amount, :final_amount)";

            $sales_stmt = $this->conn->prepare($sales_query);
            $sales_stmt->bindParam(':order_id', $order_id);
            $sales_stmt->bindParam(':cashier_id', $cashier_id);
            $sales_stmt->bindParam(':total_amount', $final_amount);
            $sales_stmt->bindParam(':tax_amount', $tax_amount);
            $sales_stmt->bindParam(':discount_amount', $discount_amount);
            $sales_stmt->bindParam(':final_amount', $final_amount);

            if (!$sales_stmt->execute()) {
                throw new Exception('Failed to create sales record');
            }

            $this->conn->commit();
            return ['success' => true, 'sales_id' => $this->conn->lastInsertId()];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getSalesReport($start_date = null, $end_date = null)
    {
        try {
            $where_conditions = [];
            $params = [];

            if ($start_date) {
                $where_conditions[] = "s.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }

            if ($end_date) {
                $where_conditions[] = "s.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $query = "SELECT 
                        COUNT(s.id) as total_sales,
                        SUM(s.final_amount) as total_revenue,
                        SUM(s.tax_amount) as total_tax,
                        SUM(s.discount_amount) as total_discount,
                        AVG(s.final_amount) as average_sale
                      FROM sales s 
                      $where_clause";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getSalesTransactions($start_date = null, $end_date = null)
    {
        try {
            $where_conditions = [];
            $params = [];

            if ($start_date) {
                $where_conditions[] = "s.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }

            if ($end_date) {
                $where_conditions[] = "s.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $query = "SELECT 
                        s.id as sale_id,
                        s.order_id,
                        s.final_amount,
                        s.tax_amount,
                        s.discount_amount,
                        s.payment_method,
                        s.created_at as sale_date,
                        o.order_number,
                        u.full_name as customer_name,
                        u.phone as customer_phone
                      FROM sales s 
                      JOIN orders o ON s.order_id = o.id
                      JOIN users u ON o.customer_id = u.id
                      $where_clause 
                      ORDER BY s.created_at DESC";

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

    public function getTopSellingDrugs($limit = 10, $start_date = null, $end_date = null)
    {
        try {
            $where_conditions = [];
            $params = [];

            if ($start_date) {
                $where_conditions[] = "s.created_at >= :start_date";
                $params[':start_date'] = $start_date;
            }

            if ($end_date) {
                $where_conditions[] = "s.created_at <= :end_date";
                $params[':end_date'] = $end_date . ' 23:59:59';
            }

            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $query = "SELECT 
                        d.name as drug_name,
                        d.strength,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.total_price) as total_revenue
                      FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      JOIN sales s ON o.id = s.order_id 
                      JOIN drugs d ON oi.drug_id = d.id 
                      $where_clause 
                      GROUP BY d.id 
                      ORDER BY total_quantity DESC 
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getTotalOrders()
    {
        try {
            $query = "SELECT COUNT(*) as total FROM orders";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getTotalSales()
    {
        try {
            $query = "SELECT COALESCE(SUM(final_amount), 0) as total FROM sales";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getRecentOrders($limit = 10)
    {
        try {
            $query = "SELECT o.*, u.full_name as customer_name, u.phone as customer_phone,
                      COUNT(oi.id) as item_count 
                      FROM orders o 
                      JOIN users u ON o.customer_id = u.id 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      GROUP BY o.id 
                      ORDER BY o.created_at DESC 
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
