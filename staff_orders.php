<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php?redirect=staff_orders.php");
    exit;
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

// Fetch staff username
$staff_id = $_SESSION['staff_id'];
$stmt = $conn->prepare("SELECT Staff_username FROM staff WHERE Staff_ID = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();
$staff_username = $staff['Staff_username'];

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
    header("Location: staff_orders.php");
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
    header("Location: staff_orders.php" . ($action === 'enable' ? '?show_disabled=1' : ''));
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
    
    header("Location: staff_orders.php");
    exit;
}

// Check if we should show disabled orders
$show_disabled = isset($_GET['show_disabled']) && $_GET['show_disabled'] == '1';

// Build the base SQL query
$sql = "SELECT o.Order_ID, c.Customer_name, o.Total, o.Address, o.PaymentMethod, o.Order_Date, o.Status 
        FROM `orders` o
        JOIN Customer c ON o.Customer_ID = c.Customer_id";

// Add filters if they exist
$where_clauses = [];
$params = [];
$types = '';

// Only add status filter if not showing disabled orders
if (!$show_disabled && !empty($_GET['status'])) {
    $where_clauses[] = "o.Status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

if (!empty($_GET['date_from'])) {
    $where_clauses[] = "o.Order_Date >= ?";
    $params[] = $_GET['date_from'];
    $types .= 's';
}

if (!empty($_GET['date_to'])) {
    $where_clauses[] = "o.Order_Date <= ?";
    $params[] = $_GET['date_to'] . ' 23:59:59';
    $types .= 's';
}

// Add disabled status filter if needed
if ($show_disabled) {
    $where_clauses[] = "o.Status = 'Disabled'";
} else {
    $where_clauses[] = "o.Status != 'Disabled'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY o.Order_Date DESC";

// Prepare and execute the query with filters
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch order items (products) for each order
$order_items = [];
if ($result->num_rows > 0) {
    while($order = $result->fetch_assoc()) {
        $order_id = $order['Order_ID'];
        $item_sql = "SELECT p.Product_name, oi.Quantity, oi.unit_price 
                     FROM Order_Items oi
                     JOIN Products p ON oi.Product_ID = p.Product_id
                     WHERE oi.Order_ID = $order_id";
        $item_result = $conn->query($item_sql);
        $items = [];
        $full_items = [];
        while($item = $item_result->fetch_assoc()) {
            $items[] = $item['Product_name'] . " (" . $item['Quantity'] . ")";
            $full_items[] = [
                'name' => $item['Product_name'],
                'quantity' => $item['Quantity'],
                'price' => $item['unit_price']
            ];
        }
        $order['products'] = implode(", ", $items);
        $order['full_items'] = $full_items;
        $order_items[] = $order;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_disabled ? 'Disabled Orders - Staff' : 'Order Management - Staff'; ?></title>
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
        .details-modal .modal-header {
            background-color: #17a2b8;
            color: white;
        }
        .order-details {
            padding: 15px;
        }
        .order-details p {
            margin-bottom: 10px;
        }
        .order-details strong {
            display: inline-block;
            width: 150px;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        .product-list {
            list-style-type: none;
            padding-left: 0;
        }
        .product-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
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
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <img src="Hachi_Logo.png" alt="PetShop Staff" height="40">
        </a>
    </div>
    <div>
        <span class="text-light me-3">
            <i class="fas fa-user-circle me-1"></i>
            Welcome, <?php echo htmlspecialchars($staff['Staff_username'] ?? $_SESSION['staff_name']); ?>
        </span>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="d-flex flex-column align-items-center mb-4">
                    <?php
                    // Check for avatar in this order: 1. Session avatar_path, 2. staff_avatars folder, 3. Default initials
                    $avatar_path = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
                    
                    if (file_exists($avatar_path)): ?>
                        <img src="<?php echo $avatar_path; ?>" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle mb-2 bg-secondary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <span class="text-white" style="font-size: 24px;">
                            <?php 
$username = $staff['Staff_username'] ?? $_SESSION['staff_name'];
echo strtoupper(substr($username, 0, 1)); 
?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <h5 class="text-white mb-1"><?php echo htmlspecialchars($staff['Staff_username'] ?? $_SESSION['staff_name']); ?></h5>
                    <small class="text-muted text-center"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
                </div>

                <!-- Sidebar Menu -->
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#customerMenu">
                            <i class="fas fa-user-friends me-2"></i>Customer Management
                        </a>
                        <div class="collapse" id="customerMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_customer_list.php">
                                        <i class="fas fa-list me-2"></i>Customer List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_customer_logs.php">
                                        <i class="fas fa-history me-2"></i>Login/Logout Logs
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light <?php echo !$show_disabled ? 'active' : ''; ?>" href="staff_orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light <?php echo $show_disabled ? 'active' : ''; ?>" href="staff_orders.php?show_disabled=1">
                                        <i class="fas fa-ban me-2"></i>Disabled Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>

                    <li class="nav-item mt-3">
                        <a class="nav-link text-light" href="settings.php">
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

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-filter me-2"></i>Filters
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="show_disabled" value="<?php echo $show_disabled ? '1' : '0'; ?>">
                        <?php if (!$show_disabled): ?>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo (isset($_GET['status'])) && $_GET['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo (isset($_GET['status'])) && $_GET['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo (isset($_GET['status'])) && $_GET['status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Completed" <?php echo (isset($_GET['status'])) && $_GET['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo (isset($_GET['status'])) && $_GET['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-<?php echo $show_disabled ? '6' : '3'; ?>">
                            <label for="dateFrom" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="dateFrom" name="date_from" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                        </div>
                        <div class="col-md-<?php echo $show_disabled ? '6' : '3'; ?>">
                            <label for="dateTo" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="dateTo" name="date_to" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                        </div>
                        <div class="col-md-<?php echo $show_disabled ? '12' : '3'; ?> d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <?php if (!empty($_GET)): ?>
                                <a href="staff_orders.php<?php echo $show_disabled ? '?show_disabled=1' : ''; ?>" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
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
                                            <td>$<?php echo number_format($order['Total'], 2); ?></td>
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
                                                <button class="btn btn-sm btn-info view-details-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#orderDetailsModal"
                                                        data-order-details='<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8'); ?>'>
                                                    <i class="fas fa-eye"></i>
                                                </button>
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

<!-- Order Details Modal -->
<div class="modal fade details-modal" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderDetailsModalLabel"><i class="fas fa-info-circle me-2"></i>Order Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="order-details">
                    <p><strong>Order ID:</strong> <span id="detailsOrderId"></span></p>
                    <p><strong>Customer Name:</strong> <span id="detailsCustomer"></span></p>
                    <p><strong>Order Date:</strong> <span id="detailsOrderDate"></span></p>
                    <p><strong>Status:</strong> <span id="detailsStatus"></span></p>
                    <p><strong>Delivery Address:</strong> <span id="detailsAddress"></span></p>
                    <p><strong>Payment Method:</strong> <span id="detailsPayment"></span></p>
                    <p><strong>Total Amount:</strong> $<span id="detailsTotal"></span></p>
                    
                    <h6 class="mt-4 mb-3"><strong>Order Items:</strong></h6>
                    <ul class="product-list" id="orderItemsList">
                        <!-- Items will be populated by JavaScript -->
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade status-modal" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="staff_orders.php">
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
            <form method="POST" action="staff_orders.php">
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
            <form method="POST" action="staff_orders.php">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Order Details Modal Handler
    const detailsModal = document.getElementById('orderDetailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderDetails = JSON.parse(button.getAttribute('data-order-details'));
            
            // Populate the modal with order details
            document.getElementById('detailsOrderId').textContent = orderDetails.Order_ID;
            document.getElementById('detailsCustomer').textContent = orderDetails.Customer_name;
            document.getElementById('detailsOrderDate').textContent = new Date(orderDetails.Order_Date).toLocaleString();
            document.getElementById('detailsStatus').innerHTML = `<span class="badge bg-${
                orderDetails.Status === 'Completed' ? 'success' : 
                orderDetails.Status === 'Processing' ? 'warning' : 
                orderDetails.Status === 'Shipped' ? 'info' : 
                orderDetails.Status === 'Pending' ? 'secondary' : 'danger'
            }">${orderDetails.Status}</span>`;
            document.getElementById('detailsAddress').textContent = orderDetails.Address;
            document.getElementById('detailsPayment').textContent = orderDetails.PaymentMethod;
            document.getElementById('detailsTotal').textContent = parseFloat(orderDetails.Total).toFixed(2);
            
            // Populate order items - simplified to show just product name and quantity
            const itemsList = document.getElementById('orderItemsList');
            itemsList.innerHTML = '';
            
            if (orderDetails.full_items && Array.isArray(orderDetails.full_items)) {
                orderDetails.full_items.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${item.name}</strong> (Qty: ${item.quantity}) - $${parseFloat(item.price).toFixed(2)}`;
                    itemsList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = "Product details not available";
                itemsList.appendChild(li);
            }
        });
    }

    // Update Status Modal Handler
    const updateStatusModal = document.getElementById('updateStatusModal');
    if (updateStatusModal) {
        updateStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const currentStatus = button.getAttribute('data-current-status');
            
            document.getElementById('updateOrderId').value = orderId;
            document.getElementById('statusSelect').value = currentStatus;
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

    // Delete Order Modal Handler
    const deleteOrderModal = document.getElementById('deleteOrderModal');
    if (deleteOrderModal) {
        deleteOrderModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            document.getElementById('deleteOrderId').value = orderId;
            document.getElementById('displayOrderId').textContent = '#' + orderId;
            document.getElementById('deleteCustomer').textContent = button.getAttribute('data-customer');
        });
    }

    // Apply filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        document.getElementById('statusFilter').value = urlParams.get('status');
    }
    if (urlParams.has('date_from')) {
        document.getElementById('dateFrom').value = urlParams.get('date_from');
    }
    if (urlParams.has('date_to')) {
        document.getElementById('dateTo').value = urlParams.get('date_to');
    }
});
</script>
</body>
</html>