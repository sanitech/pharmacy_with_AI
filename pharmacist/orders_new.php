<?php
require_once '../classes/Auth.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$order = new Order();

$error = '';
$success = '';

// Handle order approval/rejection
if ($_POST && isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    if ($_POST['action'] === 'approve_order') {
        $result = $order->updateOrderStatus($order_id, 'approved');
        if ($result['success']) {
            $success = 'Order approved successfully!';
        } else {
            $error = $result['message'] ?? 'Failed to approve order.';
        }
    } elseif ($_POST['action'] === 'reject_order') {
        $result = $order->updateOrderStatus($order_id, 'rejected');
        if ($result['success']) {
            $success = 'Order rejected successfully!';
        } else {
            $error = $result['message'] ?? 'Failed to reject order.';
        }
    }
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';

// Get orders based on status
$orders = $order->getAllOrders($status, 50);

// Set page title for header
$page_title = 'Approve Orders';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-check me-2"></i>Approve Orders</h2>
            <div class="text-muted">Review and approve customer orders</div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Status Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="?status=pending" class="btn btn-<?php echo $status === 'pending' ? 'primary' : 'outline-primary'; ?>">
                        <i class="fas fa-clock me-2"></i>Pending
                    </a>
                    <a href="?status=approved" class="btn btn-<?php echo $status === 'approved' ? 'success' : 'outline-success'; ?>">
                        <i class="fas fa-check me-2"></i>Approved
                    </a>
                    <a href="?status=rejected" class="btn btn-<?php echo $status === 'rejected' ? 'danger' : 'outline-danger'; ?>">
                        <i class="fas fa-times me-2"></i>Rejected
                    </a>
                    <a href="?status=completed" class="btn btn-<?php echo $status === 'completed' ? 'info' : 'outline-info'; ?>">
                        <i class="fas fa-check-double me-2"></i>Completed
                    </a>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <?php if (empty($orders)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No <?php echo $status; ?> orders found</h5>
                            <p class="text-muted">There are currently no orders with this status.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order_item): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-<?php
                                                switch ($order_item['status']) {
                                                    case 'pending':
                                                        echo 'warning';
                                                        break;
                                                    case 'approved':
                                                        echo 'success';
                                                        break;
                                                    case 'rejected':
                                                        echo 'danger';
                                                        break;
                                                    case 'completed':
                                                        echo 'info';
                                                        break;
                                                    default:
                                                        echo 'secondary';
                                                }
                                                ?>">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Order #<?php echo $order_item['id']; ?></h6>
                                    <span class="badge bg-<?php
                                                            switch ($order_item['status']) {
                                                                case 'pending':
                                                                    echo 'warning';
                                                                    break;
                                                                case 'approved':
                                                                    echo 'success';
                                                                    break;
                                                                case 'rejected':
                                                                    echo 'danger';
                                                                    break;
                                                                case 'completed':
                                                                    echo 'info';
                                                                    break;
                                                                default:
                                                                    echo 'secondary';
                                                            }
                                                            ?>"><?php echo ucfirst($order_item['status']); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Customer:</strong><br>
                                        <?php echo htmlspecialchars($order_item['customer_name']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Phone:</strong><br>
                                        <?php echo htmlspecialchars($order_item['customer_phone']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Total Amount:</strong><br>
                                        ETB <?php echo number_format($order_item['total_amount'], 2); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Items:</strong><br>
                                        <?php echo (int)$order_item['item_count']; ?> items
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <strong>Date:</strong><br>
                                        <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?>
                                    </div>
                                </div>

                                <?php if ($order_item['status'] === 'pending'): ?>
                                    <div class="d-flex gap-2">
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="action" value="approve_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-check me-2"></i>Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="action" value="reject_order">
                                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="fas fa-times me-2"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order_item['id']; ?>">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Detail Modals -->
<?php foreach ($orders as $order_item): ?>
    <div class="modal fade" id="orderModal<?php echo $order_item['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order #<?php echo $order_item['id']; ?> Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_item['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_item['customer_phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_item['customer_email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Status:</strong>
                                <span class="badge bg-<?php
                                                        switch ($order_item['status']) {
                                                            case 'pending':
                                                                echo 'warning';
                                                                break;
                                                            case 'approved':
                                                                echo 'success';
                                                                break;
                                                            case 'rejected':
                                                                echo 'danger';
                                                                break;
                                                            case 'completed':
                                                                echo 'info';
                                                                break;
                                                            default:
                                                                echo 'secondary';
                                                        }
                                                        ?>"><?php echo ucfirst($order_item['status']); ?></span>
                            </p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order_item['created_at'])); ?></p>
                            <p><strong>Total:</strong> ETB <?php echo number_format($order_item['total_amount'], 2); ?></p>
                        </div>
                    </div>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Drug</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_items = $order->getOrderItems($order_item['id']);
                                foreach ($order_items as $item):
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['drug_name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>ETB <?php echo number_format($item['price'], 2); ?></td>
                                        <td>ETB <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if ($order_item['status'] === 'pending'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="approve_order">
                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Approve
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="reject_order">
                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Reject
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>