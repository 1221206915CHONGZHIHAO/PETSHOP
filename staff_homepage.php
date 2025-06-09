<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'petshop');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch staff details
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, Staff_username, position, Staff_Email, img_URL FROM staff WHERE Staff_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Update session
$_SESSION['staff_name'] = $staff['Staff_name'];
$_SESSION['staff_username'] = $staff['Staff_username'];
$_SESSION['position'] = $staff['position'];
$_SESSION['staff_email'] = $staff['Staff_Email'];
$_SESSION['avatar_path'] = $staff['img_URL'];

// Handle order status toggle (disable/enable)
if (isset($_GET['toggle_status'])) {
    $order_id = intval($_GET['order_id']);
    $action = $_GET['action'];
    
    // Validate action
    if (!in_array($action, ['disable', 'enable'])) {
        $_SESSION['error_message'] = "Invalid action";
        header("Location: staff_homepage.php");
        exit();
    }
    
    // Prepare the new status
    $new_status = ($action === 'disable') ? 'Disabled' : 'Pending';
    
    // Update the order status
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE Order_ID = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id has been " . ($action === 'disable' ? 'disabled' : 'enabled');
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $db->error;
    }
    
    $stmt->close();
    header("Location: staff_homepage.php");
    exit();
}

// Handle export functionality
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Order ID', 'Customer', 'Products', 'Total', 'Date', 'Status'));
    
    $dateFilter = isset($_GET['week_filter']) ? "WHERE orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND orders.status != 'Disabled'" : "WHERE orders.status != 'Disabled'";
    
    $result = $db->query("SELECT 
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
    exit();
}

// Handle week filter
$dateFilter = isset($_GET['week_filter']) ? "WHERE orders.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND orders.status != 'Disabled'" : "WHERE orders.status != 'Disabled'";
$summaryWhere = isset($_GET['week_filter']) ? "AND order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND status != 'Disabled'" : "AND status != 'Disabled'";

// Fetch data for summary cards
$summaryData = [];
$result = $db->query("SELECT COUNT(*) as total_orders FROM orders $dateFilter");
$summaryData['total_orders'] = $result->fetch_assoc()['total_orders'];

$result = $db->query("SELECT SUM(Total) as total_revenue FROM orders $dateFilter");
$summaryData['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

$result = $db->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending' $summaryWhere");
$summaryData['pending_orders'] = $result->fetch_assoc()['pending_orders'];

$result = $db->query("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 10");
$summaryData['low_stock'] = $result->fetch_assoc()['low_stock'];

// Fetch data for sales chart (last 6 months, exclude disabled)
$salesData = [];
$result = $db->query("SELECT 
    DATE_FORMAT(order_date, '%b') as month,
    SUM(Total) as amount 
    FROM orders 
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND status != 'Disabled'
    GROUP BY MONTH(order_date)
    ORDER BY order_date ASC");
while ($row = $result->fetch_assoc()) {
    $salesData['labels'][] = $row['month'];
    $salesData['data'][] = $row['amount'];
}

// Fetch recent orders (always limit to 5, exclude disabled)
$recentOrders = [];
$result = $db->query("SELECT 
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
    WHERE orders.status != 'Disabled'
    GROUP BY orders.Order_ID
    ORDER BY orders.order_date DESC
    LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recentOrders[] = $row;
}

// Fetch shop settings
$shopSettings = [];
$settingsQuery = $db->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
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
        .badge-disabled {
            background-color: var(--gray);
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
        <main class="col-lg-10 ms-sm-auto px-md-4">
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
                                        <a href="?toggle_status=1&order_id=<?php echo $order['order_id']; ?>&action=disable" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to disable this order?')">
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

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
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

    // Time filter functionality
    const weekFilter = <?php echo isset($_GET['week_filter']) ? 'true' : 'false'; ?>;
    const weekDropdown = document.getElementById('weekDropdown');
    
    // Update button text based on current filter
    if (weekFilter) {
        weekDropdown.innerHTML = '<i class="fas fa-calendar me-1"></i>This Week';
    } else {
        weekDropdown.innerHTML = '<i class="fas fa-calendar me-1"></i>All Time';
    }
    
    // Add click handlers for filter options
    document.querySelectorAll('.time-filter-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const isWeekFilter = this.getAttribute('data-filter') === 'week';
            
            // Update UI
            weekDropdown.innerHTML = `<i class="fas fa-calendar me-1"></i>${isWeekFilter ? 'This Week' : 'All Time'}`;
            
            // Reload page with new filter
            if (isWeekFilter) {
                window.location.href = 'staff_homepage.php?week_filter=1';
            } else {
                window.location.href = 'staff_homepage.php';
            }
        });
    });

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($salesData['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']); ?>,
            datasets: [{
                label: 'Sales',
                data: <?php echo json_encode($salesData['data'] ?? [5000, 8000, 12000, 9000, 15000, 18000]); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                tension: 0.3
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
                    beginAtZero: false,
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