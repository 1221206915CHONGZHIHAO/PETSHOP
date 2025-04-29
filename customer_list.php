<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php?redirect=customer_list.php");
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch customers with their addresses
$sql = "SELECT c.Customer_id, c.Customer_name, c.Customer_email, 
               a.Address_line1, a.Address_line2, a.City, a.State, a.Postal_Code, a.Country
        FROM customer c
        LEFT JOIN customer_address a ON c.Customer_id = a.Customer_id AND a.Is_Default = 1";
$result = $conn->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .card {
            margin-top: -1px;
        }
        .address-line {
            margin-bottom: 5px;
        }
        /* Sidebar fix */
        #sidebar {
            min-height: 100vh;
        }
        /* Ensure content does not overlap sidebar */
        .main-content {
            margin-left: 250px; /* Match the sidebar width */
            padding: 20px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        .card {
    max-width: 1000px; /* 控制最大宽度，可调整如800px、900px等 */
    margin: 20px auto; /* 上下 20px 外边距，左右自动居中 */
}

    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">PetShop Admin</a>
    </div>
    <div>
        <span class="text-light me-3">Welcome, Admin</span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="promotion.php">
                            <i class="fas fa-tag me-2"></i>Promotions
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
        <div class="main-content">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Registered Customers</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['Customer_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($customer['Customer_email'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($customer['Address_line1'])): ?>
                                                <div class="address-line"><?php echo htmlspecialchars($customer['Address_line1']); ?></div>
                                                <?php if (!empty($customer['Address_line2'])): ?>
                                                    <div class="address-line"><?php echo htmlspecialchars($customer['Address_line2']); ?></div>
                                                <?php endif; ?>
                                                <div class="address-line">
                                                    <?php echo htmlspecialchars($customer['City'] ?? ''); ?>
                                                    <?php if (!empty($customer['State'])): ?>, <?php echo htmlspecialchars($customer['State']); ?><?php endif; ?>
                                                    <?php if (!empty($customer['Postal_Code'])): ?>, <?php echo htmlspecialchars($customer['Postal_Code']); ?><?php endif; ?>
                                                </div>
                                                <div class="address-line"><?php echo htmlspecialchars($customer['Country'] ?? ''); ?></div>
                                            <?php else: ?>
                                                No address found
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No registered customers found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- end main-content -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
