<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$order = new Order();

$error = '';
$success = '';

// Handle approve/reject
if ($_POST && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    if ($_POST['action'] === 'approve_order') {
        $result = $order->updateOrderStatus($order_id, 'approved');
        if ($result['success']) {
            $success = 'Order approved!';
        } else {
            $error = $result['message'] ?? 'Failed to approve order.';
        }
    } elseif ($_POST['action'] === 'reject_order') {
        $result = $order->updateOrderStatus($order_id, 'rejected');
        if ($result['success']) {
            $success = 'Order rejected.';
        } else {
            $error = $result['message'] ?? 'Failed to reject order.';
        }
    }
}

// Get all pending orders
$pending_orders = $order->getAllOrders('pending', 100);

// Set page title for header
$page_title = 'Approve Orders';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">
    <div class="container mt-5">
        <h2 class="mb-4">Approve Customer Orders</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (empty($pending_orders)): ?>
            <div class="alert alert-info">No pending orders to approve.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Items</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_orders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
                                <td>ETB <?php echo number_format($o['total_amount'], 2); ?></td>
                                <td><?php echo (int)$o['item_count']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($o['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" name="action" value="approve_order" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                        <button type="submit" name="action" value="reject_order" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('show');
        this.classList.remove('show');
    });
</script>
</body>

</html>