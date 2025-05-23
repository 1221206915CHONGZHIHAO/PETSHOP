<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'petshop');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch staff details
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, Staff_username, position, Staff_Email FROM staff WHERE Staff_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Update session
$_SESSION['staff_name'] = $staff['Staff_name'];
$_SESSION['staff_username'] = $staff['Staff_username'];
$_SESSION['position'] = $staff['position'];
$_SESSION['staff_email'] = $staff['Staff_Email'];

// Fetch data for summary cards
$summaryData = [];
$result = $db->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
$summaryData['pending_orders'] = $result->fetch_assoc()['pending_orders'];

$result = $db->query("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 10");
$summaryData['low_stock'] = $result->fetch_assoc()['low_stock'];

// Fetch recent orders (limit to 5) with order items
$recentOrders = [];
$result = $db->query("SELECT 
    o.order_id, 
    c.customer_name, 
    o.Total, 
    o.order_date, 
    o.status,
    o.address,
    o.paymentMethod
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
    LIMIT 5");
while ($row = $result->fetch_assoc()) {
    // Fetch order items for each order
    $order_id = $row['order_id'];
    $item_query = "SELECT p.product_name, oi.quantity, oi.unit_price 
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.product_id
                  WHERE oi.order_id = $order_id";
    $item_result = $db->query($item_query);
    $items = [];
    while($item = $item_result->fetch_assoc()) {
        $items[] = [
            'name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'price' => $item['unit_price']
        ];
    }
    $row['items'] = $items;
    $recentOrders[] = $row;
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    canvas {
        display: block;
        height: 300px !important;
        width: 100% !important;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
    .dropdown-toggle::after {
        display: none;
    }
    #sidebar {
        background-color: var(--dark);
        min-height: 100vh;
        transition: transform 0.3s ease;
    }
    @media (max-width: 992px) {
        #sidebar {
            position: fixed;
            z-index: 1000;
            transform: translateX(-100%);
        }
        #sidebar.show {
            transform: translateX(0);
        }
    }
    .product-list {
        list-style-type: none;
        padding-left: 0;
    }
    .product-list li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
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
// Replace the avatar display section in staff_homepage.php with:
$avatar_path = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : 
              (!empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg");

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
                <a class="nav-link text-light active" href="staff_homepage.php">
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
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_orders.php?show_disabled=1">
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

        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">PENDING ORDERS</h6>
                                    <h2 class="mb-0"><?php echo $summaryData['pending_orders']; ?></h2>
                                </div>
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">LOW STOCK ITEMS</h6>
                                    <h2 class="mb-0"><?php echo $summaryData['low_stock']; ?></h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Recent Orders (Last 5)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>$<?php echo number_format($order['Total'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = [
                                            'completed' => 'bg-success',
                                            'pending' => 'bg-warning text-dark',
                                            'shipping' => 'bg-info',
                                            'cancelled' => 'bg-danger'
                                        ];
                                        $status = strtolower($order['status']);
                                        ?>
                                        <span class="badge <?php echo $badgeClass[$status] ?? 'bg-secondary'; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
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
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-md-7">
                        <h4 class="text-white mb-3">Contact Us</h4>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>123 Pet Street, Animal City</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="fas fa-phone"></i>
                                    <span>+1 (555) 123-4567</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="fas fa-envelope"></i>
                                    <span>info@hachipetshop.com</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="fas fa-clock"></i>
                                    <span>Mon-Fri: 9AM - 6PM</span>
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
<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
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


<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 &&
            !document.getElementById('sidebar').contains(e.target) &&
            !document.getElementById('sidebarToggle').contains(e.target) &&
            document.getElementById('sidebar').classList.contains('show')) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Order Details Modal Handler
    const detailsModal = document.getElementById('orderDetailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const orderDetails = JSON.parse(button.getAttribute('data-order-details'));
            
            // Populate the modal with order details
            document.getElementById('detailsOrderId').textContent = orderDetails.order_id;
            document.getElementById('detailsCustomer').textContent = orderDetails.customer_name;
            document.getElementById('detailsOrderDate').textContent = new Date(orderDetails.order_date).toLocaleString();
            document.getElementById('detailsStatus').innerHTML = `<span class="badge bg-${
                orderDetails.status.toLowerCase() === 'completed' ? 'success' : 
                orderDetails.status.toLowerCase() === 'pending' ? 'warning' : 
                orderDetails.status.toLowerCase() === 'shipping' ? 'info' : 
                orderDetails.status.toLowerCase() === 'cancelled' ? 'danger' : 'secondary'
            }">${orderDetails.status}</span>`;
            document.getElementById('detailsAddress').textContent = orderDetails.address;
            document.getElementById('detailsPayment').textContent = orderDetails.paymentMethod;
            document.getElementById('detailsTotal').textContent = orderDetails.Total.toFixed(2);
            
            // Populate order items
            const itemsList = document.getElementById('orderItemsList');
            itemsList.innerHTML = '';
            orderDetails.items.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `<strong>${item.name}</strong> - ${item.quantity} x $${item.price.toFixed(2)} = $${(item.quantity * item.price).toFixed(2)}`;
                itemsList.appendChild(li);
            });
        });
    }
});
</script>

</body>
</html>