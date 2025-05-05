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
$query = "SELECT Staff_name, position, Staff_Email FROM staff WHERE Staff_ID = ?";
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
$_SESSION['position'] = $staff['position'];
$_SESSION['staff_email'] = $staff['Staff_Email'];

// Fetch data for summary cards
$summaryData = [];
$result = $db->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
$summaryData['pending_orders'] = $result->fetch_assoc()['pending_orders'];

$result = $db->query("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 10");
$summaryData['low_stock'] = $result->fetch_assoc()['low_stock'];

// Fetch recent orders (limit to 5)
$recentOrders = [];
$result = $db->query("SELECT 
    o.order_id, 
    c.customer_name, 
    o.Total, 
    o.order_date, 
    o.status
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
    LIMIT 5");
while ($row = $result->fetch_assoc()) {
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
            background-color: #343a40;
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
                    </ul>
                </div>
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
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i> Today
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync me-1"></i> Refresh
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
                                    <th>Actions</th>
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
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-print"></i>
                                        </button>
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
});
</script>

</body>
</html>