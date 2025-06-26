<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Shipping fee constant
define('SHIPPING_FEE', 4.90);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE Order_ID = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id status updated to $new_status";
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: orders.php");
    exit;
}

// Handle order disable/enable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];
    
    $new_status = ($action === 'disable') ? 'Disabled' : 'Pending';
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE Order_ID = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $action_text = ($action === 'disable') ? 'disabled' : 'enabled';
        $_SESSION['success_message'] = "Order #$order_id has been $action_text";
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: orders.php" . ($action === 'enable' ? '?show_disabled=1' : ''));
    exit;
}

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete order items
        $stmt = $conn->prepare("DELETE FROM Order_Items WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Then delete the order
        $stmt = $conn->prepare("DELETE FROM orders WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success_message'] = "Order #$order_id has been deleted successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting order: " . $e->getMessage();
    }
    
    header("Location: orders.php");
    exit;
}

// Check if we should show disabled orders
$show_disabled = isset($_GET['show_disabled']) && $_GET['show_disabled'] == '1';

// Build the SQL query
$sql = "SELECT o.Order_ID, c.Customer_name, o.Total, o.Address, o.PaymentMethod, o.Order_Date, o.Status 
        FROM `orders` o
        JOIN Customer c ON o.Customer_ID = c.Customer_id
        WHERE o.Status " . ($show_disabled ? "= 'Disabled'" : "!= 'Disabled'") . "
        ORDER BY o.Order_Date DESC";

$result = $conn->query($sql);

// Fetch order items (products) for each order
$order_items = [];
if ($result->num_rows > 0) {
    while($order = $result->fetch_assoc()) {
        $order_id = $order['Order_ID'];
        // Modified query to handle deleted products
        $item_sql = "SELECT 
                        COALESCE(p.Product_name, 'Deleted Product') AS Product_name, 
                        oi.Quantity, 
                        oi.unit_price,
                        oi.Product_ID
                     FROM Order_Items oi
                     LEFT JOIN Products p ON oi.Product_ID = p.Product_id
                     WHERE oi.Order_ID = $order_id";
        $item_result = $conn->query($item_sql);
        $items = [];
        $full_items = [];
        $calculated_total = 0;
        
        while($item = $item_result->fetch_assoc()) {
            $product_name = $item['Product_name'];
            $items[] = $product_name . " (" . $item['Quantity'] . ")";
            $item_total = $item['unit_price'] * $item['Quantity'];
            $calculated_total += $item_total;
            
            $full_items[] = [
                'name' => $product_name,
                'quantity' => $item['Quantity'],
                'price' => $item['unit_price'],
                'item_total' => $item_total,
                'product_id' => $item['Product_ID']
            ];
        }
        
        // Add shipping fee after the loop
        $calculated_total += SHIPPING_FEE;
        
        // Ensure the order total matches the sum of item totals
        $order['Total'] = $calculated_total;
        $order['products'] = implode(", ", $items);
        $order['full_items'] = $full_items;
        $order_items[] = $order;
    }
}

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_disabled ? 'Disabled Orders' : 'Order Management'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .action-modal .modal-header {
            background-color: #4e73df;
            color: white;
        }
        .delete-modal .modal-header {
            background-color: #dc3545;
            color: white;
        }
        .disable-modal .modal-header {
            background-color: #dc3545;
        }
        .enable-modal .modal-header {
            background-color: #28a745;
        }
        .status-modal .modal-header {
            background-color: #ffc107;
            color: #000;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        .section-title:after {
            content: '';
            display: block;
            height: 4px;
            width: 70px;
            background-color: var(--primary);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <img src="Hachi_Logo.png" alt="PetShop Admin" height="40">
        </a>
    </div>
    <div>
        <span class="text-light me-3"><i class="fas fa-user-circle me-1"></i> Welcome, <?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
        <a href="admin_login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-2 d-md-block bg-dark sidebar">
            <div class="position-sticky">
                <h4 class="text-light text-center py-3"><i class="fas fa-paw me-2"></i>Admin Menu</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
            <li class="nav-item">
                <a class="nav-link text-light" data-bs-toggle="collapse" href="#staffMenu">
                    <i class="fas fa-users me-2"></i>Staff Management
                </a>
                <div class="collapse" id="staffMenu">
                    <ul class="nav flex-column ps-4">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="manage_staff.php">
                                <i class="fas fa-list me-2"></i>Staff List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="staff_logs.php">
                                <i class="fas fa-history me-2"></i>Login/Logout Logs
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#customerMenu">
                            <i class="fas fa-user-friends me-2"></i>Customer Management
                        </a>
                        <div class="collapse" id="customerMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="customer_list.php">
                                        <i class="fas fa-list me-2"></i>Customer List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="customer_logs.php">
                                        <i class="fas fa-history me-2"></i>Login/Logout Logs
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light <?php echo !$show_disabled ? 'active' : ''; ?>" href="orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light <?php echo $show_disabled ? 'active' : ''; ?>" href="orders.php?show_disabled=1">
                                        <i class="fas fa-ban me-2"></i>Disabled Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_setting.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i><?php echo $show_disabled ? 'Disabled Orders' : 'Order Management'; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i><?php echo $show_disabled ? 'Disabled Orders' : 'Active Orders'; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($order_items)): ?>
                                    <?php foreach ($order_items as $order): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['Order_ID']); ?></td>
                                            <td><?php echo htmlspecialchars($order['Customer_name']); ?></td>
                                            <td>
                                                <?php 
                                                $products = explode(", ", $order['products']);
                                                if (count($products) > 2) {
                                                    echo htmlspecialchars($products[0] . ', ' . $products[1] . '...');
                                                } else {
                                                    echo htmlspecialchars($order['products']);
                                                }
                                                ?>
                                            </td>
                                            <td>RM<?php echo number_format($order['Total'], 2); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['Order_Date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['Status'] === 'Completed' ? 'success' : 
                                                         ($order['Status'] === 'Processing' ? 'warning' : 
                                                         ($order['Status'] === 'Shipped' ? 'info' : 
                                                         ($order['Status'] === 'Disabled' ? 'secondary' : 'danger'))); 
                                                ?>">
                                                    <?php echo htmlspecialchars($order['Status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!$show_disabled): ?>
                                                    <button class="btn btn-sm btn-warning update-status-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateStatusModal"
                                                            data-order-id="<?php echo $order['Order_ID']; ?>"
                                                            data-current-status="<?php echo htmlspecialchars($order['Status']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary disable-order-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#disableOrderModal"
                                                            data-order-id="<?php echo $order['Order_ID']; ?>"
                                                            data-customer="<?php echo htmlspecialchars($order['Customer_name']); ?>">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success enable-order-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#enableOrderModal"
                                                            data-order-id="<?php echo $order['Order_ID']; ?>"
                                                            data-customer="<?php echo htmlspecialchars($order['Customer_name']); ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No <?php echo $show_disabled ? 'disabled' : 'active'; ?> orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade status-modal" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="orders.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel"><i class="fas fa-sync-alt me-2"></i>Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <input type="hidden" name="update_status" value="1">
                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">New Status:</label>
                        <select class="form-select" id="statusSelect" name="new_status" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disable Order Modal -->
<div class="modal fade disable-modal" id="disableOrderModal" tabindex="-1" aria-labelledby="disableOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="orders.php">
                <input type="hidden" name="toggle_order_status" value="1">
                <input type="hidden" name="action" value="disable">
                <input type="hidden" name="order_id" id="disableOrderId">
                <div class="modal-header">
                    <h5 class="modal-title" id="disableOrderModalLabel"><i class="fas fa-ban me-2"></i>Disable Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to disable this order?</p>
                    <p><strong>Order ID:</strong> <span id="displayDisableOrderId"></span></p>
                    <p><strong>Customer:</strong> <span id="disableCustomer"></span></p>
                    <p class="text-muted"><small>The order will be moved to disabled orders list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disable Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enable Order Modal -->
<div class="modal fade enable-modal" id="enableOrderModal" tabindex="-1" aria-labelledby="enableOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="orders.php">
                <input type="hidden" name="toggle_order_status" value="1">
                <input type="hidden" name="action" value="enable">
                <input type="hidden" name="order_id" id="enableOrderId">
                <div class="modal-header">
                    <h5 class="modal-title" id="enableOrderModalLabel"><i class="fas fa-check me-2"></i>Enable Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to enable this order?</p>
                    <p><strong>Order ID:</strong> <span id="displayEnableOrderId"></span></p>
                    <p><strong>Customer:</strong> <span id="enableCustomer"></span></p>
                    <p class="text-muted"><small>The order will be moved back to active orders list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Enable Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-2"> <!-- This matches the main content area -->
                <div class="row">
                    <!-- Footer About -->
                    <div class="col-md-5 mb-4 mb-lg-0">
                        <div class="footer-about">
                            <div class="footer-logo">
                                <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
                            </div>
                            <p>Your trusted partner in pet products. We're dedicated to providing quality products for pet lovers everywhere.</p>
                            <div class="social-links">
                                <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="fab fa-facebook"></i></a>
                                <a href="https://www.instagram.com/smal.l7018/"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-md-7">
                        <h4 class="footer-title">Contact Us</h4>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?php echo !empty($shopSettings['address']) ? htmlspecialchars($shopSettings['address']) : 'Address not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-telephone"></i>
                                    <span><?php echo !empty($shopSettings['phone_number']) ? htmlspecialchars($shopSettings['phone_number']) : 'Phone number not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-envelope"></i>
                                    <span><?php echo !empty($shopSettings['contact_email']) ? htmlspecialchars($shopSettings['contact_email']) : 'Email not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-clock"></i>
                                    <span><?php echo !empty($shopSettings['opening_hours']) ? htmlspecialchars($shopSettings['opening_hours']) : 'Opening hours not available'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer Bottom -->
                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-12 text-center">
                            <p class="mb-0 text-white">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Status Modal Handler
    const updateStatusModal = document.getElementById('updateStatusModal');
    if (updateStatusModal) {
        updateStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const currentStatus = button.getAttribute('data-current-status');
            
            document.getElementById('updateOrderId').value = orderId;
            
            // Set the current status as selected in the dropdown
            const statusSelect = document.getElementById('statusSelect');
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value === currentStatus) {
                    statusSelect.options[i].selected = true;
                    break;
                }
            }
        });
    }

    // Disable Order Modal Handler
    const disableOrderModal = document.getElementById('disableOrderModal');
    if (disableOrderModal) {
        disableOrderModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            document.getElementById('disableOrderId').value = orderId;
            document.getElementById('displayDisableOrderId').textContent = '#' + orderId;
            document.getElementById('disableCustomer').textContent = button.getAttribute('data-customer');
        });
    }

    // Enable Order Modal Handler
    const enableOrderModal = document.getElementById('enableOrderModal');
    if (enableOrderModal) {
        enableOrderModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            document.getElementById('enableOrderId').value = orderId;
            document.getElementById('displayEnableOrderId').textContent = '#' + orderId;
            document.getElementById('enableCustomer').textContent = button.getAttribute('data-customer');
        });
    }

    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    }
});
</script>
</body>
</html>