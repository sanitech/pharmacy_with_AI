<?php
require_once '../classes/Auth.php';
require_once '../classes/Drug.php';
require_once '../classes/Order.php';

$auth = new Auth();
$auth->requireRole('customer');

$user = $auth->getCurrentUser();
$drug = new Drug();
$order = new Order();

$error = '';
$success = '';

// Handle add to cart
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $drug_id = $_POST['drug_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!empty($drug_id) && $quantity > 0) {
        // Get drug details
        $drug_details = $drug->getDrugById($drug_id);
        if ($drug_details && $drug_details['stock_quantity'] >= $quantity) {
            // Add to session cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$drug_id])) {
                $_SESSION['cart'][$drug_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$drug_id] = [
                    'drug_id' => $drug_id,
                    'name' => $drug_details['name'],
                    'strength' => $drug_details['strength'],
                    'price' => $drug_details['price'],
                    'quantity' => $quantity,
                    'requires_prescription' => $drug_details['is_prescription_required']
                ];
            }
            $success = 'Drug added to cart successfully!';
        } else {
            $error = 'Drug not available in requested quantity';
        }
    } else {
        $error = 'Invalid drug or quantity';
    }
}

// Handle remove from cart
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'remove_from_cart') {
    $drug_id = $_POST['drug_id'] ?? '';
    if (!empty($drug_id) && isset($_SESSION['cart'][$drug_id])) {
        unset($_SESSION['cart'][$drug_id]);
        $success = 'Drug removed from cart';
    }
}

// Handle place order
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    if (!empty($_SESSION['cart'])) {
        $items = [];
        $total_amount = 0;
        
        foreach ($_SESSION['cart'] as $item) {
            $items[] = [
                'drug_id' => $item['drug_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['price'] * $item['quantity']
            ];
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        $result = $order->createOrder($user['id'], $items, $total_amount);
        if ($result['success']) {
            // Clear cart
            unset($_SESSION['cart']);
            $success = 'Order placed successfully! Order #: ' . $result['order_number'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Cart is empty';
    }
}

// Get search parameters
$search_term = $_GET['q'] ?? '';
$category_id = $_GET['category'] ?? '';
$sort_by = $_GET['sort'] ?? 'name';

// Get drugs based on search
$drugs = $drug->getAllDrugs($search_term, $category_id, 50);

// Get categories for filter
$categories = $drug->getAllCategories();

// Calculate cart total
$cart_total = 0;
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += $item['price'] * $item['quantity'];
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Drugs - Sky Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>Sky Pharmacy</h4>
            <p>Customer Panel</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="nav-link">
                Dashboard
            </a>
            <a href="search.php" class="nav-link active">
                Search Drugs
            </a>
            <a href="orders.php" class="nav-link">
                My Orders
            </a>
            <a href="prescriptions.php" class="nav-link">
                My Prescriptions
            </a>
            <a href="profile.php" class="nav-link">
                Profile
            </a>
            <hr class="my-3">
            <a href="../logout.php" class="nav-link text-danger">
                Logout
            </a>
        </nav>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Search Drugs</h4>
                    <p class="mb-0 text-muted">Find and order medications</p>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Search Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2><i class="fas fa-search me-2"></i>Search Medications</h2>
                    <p class="text-muted">Find and order your medications</p>
                </div>
            </div>

            <div class="row">
                <!-- Search Filters -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET">
                                <div class="mb-3">
                                    <label for="q" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="q" name="q" 
                                           value="<?php echo htmlspecialchars($search_term); ?>" 
                                           placeholder="Drug name...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="sort" class="form-label">Sort By</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Shopping Cart -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-primary ms-2"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <p class="text-muted">Your cart is empty</p>
                            <?php else: ?>
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <p class="text-muted mb-1"><?php echo htmlspecialchars($item['strength']); ?></p>
                                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                            </div>
                                            <div class="text-end">
                                                <p class="mb-1 fw-bold">ETB <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="remove_from_cart">
                                                    <input type="hidden" name="drug_id" value="<?php echo $item['drug_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="border-top pt-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong>ETB <?php echo number_format($cart_total, 2); ?></strong>
                                    </div>
                                </div>
                                
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="action" value="place_order">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-2"></i>Place Order
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Drug Results -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-pills me-2"></i>Available Medications
                                <?php if (!empty($search_term)): ?>
                                    <span class="text-muted">for "<?php echo htmlspecialchars($search_term); ?>"</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($drugs)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5>No medications found</h5>
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($drugs as $drug_item): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="drug-card">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($drug_item['name']); ?></h6>
                                                    <?php if ($drug_item['is_prescription_required']): ?>
                                                        <span class="badge bg-warning">Prescription Required</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <p class="text-muted mb-2"><?php echo htmlspecialchars($drug_item['strength']); ?></p>
                                                <p class="small mb-3"><?php echo htmlspecialchars(substr($drug_item['description'], 0, 100)) . '...'; ?></p>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted">Price:</small><br>
                                                        <strong class="text-primary">ETB <?php echo number_format($drug_item['price'], 2); ?></strong>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <small class="text-muted">Stock:</small><br>
                                                        <span class="badge bg-<?php echo $drug_item['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                            <?php echo $drug_item['stock_quantity']; ?> available
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($drug_item['stock_quantity'] > 0): ?>
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="add_to_cart">
                                                        <input type="hidden" name="drug_id" value="<?php echo $drug_item['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <input type="number" class="form-control form-control-sm" 
                                                                       name="quantity" value="1" min="1" max="<?php echo $drug_item['stock_quantity']; ?>">
                                                            </div>
                                                            <div class="col-6">
                                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                    <i class="fas fa-cart-plus me-1"></i>Add
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                                        <i class="fas fa-times me-1"></i>Out of Stock
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        // Set active nav link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar-menu .nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 