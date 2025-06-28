<?php
require_once 'admin_session_check.php';

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle order status toggle (disable/enable)
if (isset($_GET['toggle_status'])) {
    $order_id = intval($_GET['order_id']);
    $action = $_GET['action'];
    
    // Validate action
    if (!in_array($action, ['disable', 'enable'])) {
        $_SESSION['error_message'] = "Invalid action";
        // Preserve week_filter in redirect
        $redirect_url = "admin_homepage.php";
        if (isset($_GET['week_filter'])) {
            $redirect_url .= "?week_filter=1";
        }
        header("Location: " . $redirect_url);
        exit;
    }
    
    // Prepare the new status
    $new_status = ($action === 'disable') ? 'Disabled' : 'Pending';
    
    // Update the order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE Order_ID = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id has been " . ($action === 'disable' ? 'disabled' : 'enabled');
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $conn->error;
    }
    
    $stmt->close();
    
    // Build redirect URL with preserved parameters
    $redirect_url = "admin_homepage.php";
    $params = [];
    
    // Preserve week_filter if it was set
    if (isset($_GET['week_filter'])) {
        $params[] = "week_filter=1";
    }
    
    // Add any other parameters you want to preserve here
    // Example: if (isset($_GET['some_param'])) { $params[] = "some_param=" . urlencode($_GET['some_param']); }
    
    if (!empty($params)) {
        $redirect_url .= "?" . implode("&", $params);
    }
    
    header("Location: " . $redirect_url);
    exit;
}

// Handle export functionality
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Order ID', 'Customer', 'Products', 'Total', 'Date', 'Status'));
    
    $dateFilter = isset($_GET['week_filter']) ? "WHERE orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND orders.status != 'Disabled'" : "WHERE orders.status != 'Disabled'";
    
    $result = $conn->query("SELECT 
        orders.Order_ID as order_id, 
        c.customer_name, 
        orders.Total, 
        orders.order_date, 
        orders.status,
        GROUP_CONCAT(CONCAT(p.product_name, ' (', oi.quantity, ')')) as products
        FROM orders
        JOIN customer c ON orders.Customer_ID = c.customer_id
        JOIN order_items oi ON orders.Order_ID = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        $dateFilter
        GROUP BY orders.Order_ID
        ORDER BY orders.order_date DESC
        LIMIT 5");
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['order_id'],
            $row['customer_name'],
            $row['products'],
            $row['Total'],
            $row['order_date'],
            $row['status']
        ));
    }
    
    fclose($output);
    exit;
}

// Handle week filter
$dateFilter = isset($_GET['week_filter']) ? "WHERE orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND orders.status != 'Disabled'" : "WHERE orders.status != 'Disabled'";
$summaryWhere = isset($_GET['week_filter']) ? "AND order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND status != 'Disabled'" : "AND status != 'Disabled'";

// Fetch data for summary cards - FIXED: Removed table alias 'o' since we're not joining tables here
$result = $conn->query("SELECT COUNT(*) as total_orders FROM orders $dateFilter");
$summaryData['total_orders'] = $result->fetch_assoc()['total_orders'];

$result = $conn->query("SELECT SUM(Total) as total_revenue FROM orders $dateFilter");
$summaryData['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending' $summaryWhere");
$summaryData['pending_orders'] = $result->fetch_assoc()['pending_orders'];

$result = $conn->query("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 11 AND stock_quantity >= 1");
$summaryData['low_stock'] = $result->fetch_assoc()['low_stock'];

// Fetch data for sales chart (apply week filter if selected)
$salesData = ['labels' => [], 'data' => []];

if (isset($_GET['week_filter'])) {
    // For week view - show days of current week
    $days = [];
    $dayNames = [];
    for ($i = 6; $i >= 0; $i--) {
        $timestamp = strtotime("-$i days");
        $days[] = date('Y-m-d', $timestamp);
        $dayNames[] = date('D', $timestamp); // Short day names (Mon, Tue, etc.)
    }
    
    // Initialize with zero values
    $salesData['labels'] = $dayNames;
    $salesData['data'] = array_fill(0, count($days), 0);
    
    // Get actual order data for the week
    $result = $conn->query("SELECT 
        DATE(order_date) as day,
        SUM(Total) as amount 
        FROM orders 
        WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
        AND status != 'Disabled'
        GROUP BY DATE(order_date)
        ORDER BY day ASC");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dayIndex = array_search($row['day'], $days);
            if ($dayIndex !== false) {
                $salesData['data'][$dayIndex] = $row['amount'];
            }
        }
    }
} else {
    // For all time view - show months (as before)
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $months[] = date('M', strtotime("-$i months"));
    }
    
    // Initialize with zero values
    $salesData['labels'] = $months;
    $salesData['data'] = array_fill(0, count($months), 0);
    
    $result = $conn->query("SELECT 
        DATE_FORMAT(order_date, '%b') as month,
        MONTH(order_date) as month_num,
        SUM(Total) as amount 
        FROM orders 
        WHERE status != 'Disabled'
        AND order_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY MONTH(order_date)
        ORDER BY order_date ASC");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthIndex = array_search($row['month'], $months);
            if ($monthIndex !== false) {
                $salesData['data'][$monthIndex] = $row['amount'];
            }
        }
    }
}
$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}
// Fetch recent orders (last 5)
$recentOrders = [];
$recentQuery = $conn->query("SELECT 
    orders.Order_ID as order_id, 
    c.customer_name, 
    orders.Total, 
    orders.order_date, 
    orders.status,
    IFNULL(GROUP_CONCAT(
        CASE 
            WHEN p.product_name IS NULL THEN CONCAT('Deleted Product (', oi.quantity, ')')
            ELSE CONCAT(p.product_name, ' (', oi.quantity, ')')
        END
    ), 'No products') as products
    FROM orders
    JOIN customer c ON orders.Customer_ID = c.customer_id
    LEFT JOIN order_items oi ON orders.Order_ID = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    $dateFilter
    GROUP BY orders.Order_ID
    ORDER BY orders.order_date DESC
    LIMIT 5");

if ($recentQuery) {
    $recentOrders = $recentQuery->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    .badge-disabled {
        background-color: var(--gray);
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
    

    /* Time filter styles */
.dropdown-toggle {
    min-width: 120px;
    text-align: left;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dropdown-menu {
    min-width: 120px;
}

.dropdown-item.active {
    background-color:rgb(25, 148, 4);
    color: #212529;
    font-weight: 500;
}

.dropdown-item.active::after {
    content: "✓";
    margin-left: 10px;
}

.btn-outline-secondary {
    border-color:rgb(16, 119, 3);
}

.btn-outline-secondary:hover {
    background-color:rgb(14, 73, 3);
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
        <a href="logout.php?type=admin" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                        <a class="nav-link text-light active" href="admin_homepage.php">
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
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="orders.php?show_disabled=1">
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

        <!-- Main Content -->
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
                <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="?export=1<?php echo isset($_GET['week_filter']) ? '&week_filter=1' : ''; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download me-1"></i> Export
                        </a>
                    </div>
            <div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="weekDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-calendar me-1"></i><?php echo isset($_GET['week_filter']) ? 'This Week' : 'All Time'; ?>
    </button>
    <ul class="dropdown-menu" aria-labelledby="weekDropdown">
        <li><a class="dropdown-item time-filter-option <?php echo isset($_GET['week_filter']) ? 'active' : ''; ?>" href="#" data-filter="week">This Week</a></li>
        <li><a class="dropdown-item time-filter-option <?php echo !isset($_GET['week_filter']) ? 'active' : ''; ?>" href="#" data-filter="all">All Time</a></li>
    </ul>
</div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL ORDERS</h6>
                                    <h2 class="mb-0"><?php echo $summaryData['total_orders']; ?></h2>
                                </div>
                                <i class="fas fa-shopping-cart fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL REVENUE</h6>
                                    <h2 class="mb-0">RM<?php echo number_format($summaryData['total_revenue'], 2); ?></h2>
                                </div>
                                <i class="fas fa-dollar-sign fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <div class="card text-white bg-danger h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">LOW STOCK</h6>
                                    <h2 class="mb-0"><?php echo $summaryData['low_stock']; ?></h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-chart-line me-2"></i>Sales Overview
                        </div>
                        <div class="card-body chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['products']); ?></td>
                                    <td>RM<?php echo number_format($order['Total'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = [
                                            'completed' => 'bg-success',
                                            'pending' => 'bg-warning text-dark',
                                            'shipped' => 'bg-info',
                                            'processing' => 'bg-primary',
                                            'cancelled' => 'bg-danger',
                                            'disabled' => 'bg-secondary'
                                        ];
                                        $status = strtolower($order['status']);
                                        ?>
                                        <span class="badge <?php echo $badgeClass[$status] ?? 'bg-secondary'; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?toggle_status=1&order_id=<?php echo $order['order_id']; ?>&action=disable<?php echo isset($_GET['week_filter']) ? '&week_filter=1' : ''; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to disable this order?')">
                                            <i class="fas fa-ban"></i>
                                        </a>
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


<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Get the current filter state from URL
    const urlParams = new URLSearchParams(window.location.search);
    const weekFilter = urlParams.has('week_filter');
    const weekDropdown = document.getElementById('weekDropdown');
    
    // Update button text based on current filter
    if (weekFilter) {
        weekDropdown.innerHTML = '<i class="fas fa-calendar me-1"></i>This Week';
    } else {
        weekDropdown.innerHTML = '<i class="fas fa-calendar me-1"></i>All Time';
    }
    
    // Time filter functionality
    document.querySelectorAll('.time-filter-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const isWeekFilter = this.getAttribute('data-filter') === 'week';
            
            // Create URL with all current parameters except week_filter
            const url = new URL(window.location.href);
            if (isWeekFilter) {
                url.searchParams.set('week_filter', '1');
            } else {
                url.searchParams.delete('week_filter');
            }
            
            // Reload the page with the new filter
            window.location.href = url.toString();
        });
    });

// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesLabels = <?php echo json_encode($salesData['labels']); ?>;
const salesData = <?php echo json_encode($salesData['data']); ?>;

new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesLabels,
        datasets: [{
            label: 'Sales',
            data: salesData,
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 2,
            tension: 0.1 // Reduced tension for straighter lines between points
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { 
                mode: 'index', 
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return 'RM' + context.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true, // Changed to true to start at 0
                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                ticks: { 
                    callback: function(value) {
                        return 'RM' + value.toLocaleString();
                    }
                }
            },
            x: { grid: { display: false } }
        }
    }
});
});
</script>

</body>
</html>