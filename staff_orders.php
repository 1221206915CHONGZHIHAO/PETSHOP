<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php?redirect=customer_list.php");
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

// Fetch orders with customer information
$sql = "SELECT o.Order_ID, c.Customer_name, o.Total, o.Address, o.PaymentMethod, o.Order_Date, o.Status 
        FROM `orders` o
        JOIN Customer c ON o.Customer_ID = c.Customer_id
        ORDER BY o.Order_Date DESC";
$result = $conn->query($sql);

// Fetch order items (products) for each order
$order_items = [];
if ($result->num_rows > 0) {
    while($order = $result->fetch_assoc()) {
        $order_id = $order['Order_ID'];
        $item_sql = "SELECT p.Product_name, oi.Quantity 
                     FROM Order_Items oi
                     JOIN Products p ON oi.Product_ID = p.Product_id
                     WHERE oi.Order_ID = $order_id";
        $item_result = $conn->query($item_sql);
        $items = [];
        while($item = $item_result->fetch_assoc()) {
            $items[] = $item['Product_name'] . " (" . $item['Quantity'] . ")";
        }
        $order['products'] = implode(", ", $items);
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
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .action-modal .modal-header {
            background-color: #4e73df;
            color: white;
        }
        .delete-modal .modal-header {
            background-color: #dc3545;
        }
        .status-modal .modal-header {
            background-color: #ffc107;
            color: #000;
        }
        .order-details {
            padding: 15px;
        }
        .order-details p {
            margin-bottom: 10px;
        }
        .order-details strong {
            display: inline-block;
            width: 120px;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-paw me-2"></i>PetShop Staff
        </a>
    </div>
    <div>
        <span class="text-light me-3">
            <i class="fas fa-user-circle me-1"></i>
            Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>
        </span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <?php
            // Path to the staff avatar image
            $avatar_path = "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
            
            // Check if the avatar exists, if so, display it
            if (file_exists($avatar_path)) {
                echo '<img src="' . $avatar_path . '" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">';
            }
            ?>
            <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></h5>
            <small class="text-muted"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
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
                            <a class="nav-link text-light active" href="staff_orders.php">
                                <i class="fas fa-list me-2"></i>Current Orders
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
                <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i>Order Management</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Manage Orders
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
                                            <td><?php echo htmlspecialchars($order['products']); ?></td>
                                            <td>$<?php echo number_format($order['Total'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($order['Order_Date']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $order['Status'] === 'Completed' ? 'success' : 
                                                         ($order['Status'] === 'Processing' ? 'warning' : 
                                                         ($order['Status'] === 'Shipped' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo htmlspecialchars($order['Status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning update-status-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateStatusModal"
                                                        data-order-id="<?php echo $order['Order_ID']; ?>"
                                                        data-current-status="<?php echo htmlspecialchars($order['Status']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-order-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteOrderModal"
                                                        data-order-id="<?php echo $order['Order_ID']; ?>"
                                                        data-customer="<?php echo htmlspecialchars($order['Customer_name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found</td>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-modal" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="orders.php">
                <input type="hidden" name="delete_order" value="1">
                <input type="hidden" name="order_id" id="deleteOrderId">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOrderModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this order?</p>
                    <p><strong>Order ID:</strong> <span id="displayOrderId"></span></p>
                    <p><strong>Customer:</strong> <span id="deleteCustomer"></span></p>
                    <p class="text-danger"><small>This action cannot be undone!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Status Modal Handler
    const updateStatusModal = document.getElementById('updateStatusModal');
    updateStatusModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-order-id');
        const currentStatus = button.getAttribute('data-current-status');
        
        document.getElementById('updateOrderId').value = orderId;
        
        const statusSelect = document.getElementById('statusSelect');
        statusSelect.value = currentStatus;
    });

    // Delete Order Modal Handler
    const deleteOrderModal = document.getElementById('deleteOrderModal');
    deleteOrderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-order-id');
        document.getElementById('deleteOrderId').value = orderId;
        document.getElementById('displayOrderId').textContent = orderId;
        document.getElementById('deleteCustomer').textContent = button.getAttribute('data-customer');
    });
});
</script>
</body>
</html>