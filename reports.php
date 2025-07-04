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

// Default report type (weekly)
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'weekly';
$report_data = [];
$chart_labels = [];
$chart_data = [];

// Generate report based on type
switch ($report_type) {
    case 'weekly':
        // Get sales data for the last 8 weeks
        $result = $conn->query("
    SELECT 
        YEARWEEK(Order_Date) AS week,
        SUM(Total) AS total_sales,
        COUNT(Order_ID) AS order_count
    FROM `Orders`
    WHERE Order_Date >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
    AND status != 'Disabled'
    GROUP BY YEARWEEK(Order_Date)
    ORDER BY week DESC
");
        
        while ($row = $result->fetch_assoc()) {
            $year = substr($row['week'], 0, 4);
            $week = substr($row['week'], 4, 2);
            $report_data[] = [
                'period' => 'Week ' . $week . ', ' . $year,
                'sales' => $row['total_sales'],
                'orders' => $row['order_count']
            ];
            $chart_labels[] = 'W' . $week;
            $chart_data[] = $row['total_sales'];
        }
        break;
        
    case 'monthly':
        // Get sales data for the last 12 months
       $result = $conn->query("
    SELECT 
        DATE_FORMAT(Order_Date, '%Y-%m') AS month,
        SUM(Total) AS total_sales,
        COUNT(Order_ID) AS order_count
    FROM `Orders`
    WHERE Order_Date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    AND status != 'Disabled'
    GROUP BY DATE_FORMAT(Order_Date, '%Y-%m')
    ORDER BY month DESC
");
        
        while ($row = $result->fetch_assoc()) {
            $report_data[] = [
                'period' => date('F Y', strtotime($row['month'] . '-01')),
                'sales' => $row['total_sales'],
                'orders' => $row['order_count']
            ];
            $chart_labels[] = date('M', strtotime($row['month'] . '-01'));
            $chart_data[] = $row['total_sales'];
        }
        break;
        
    case 'yearly':
        // Get sales data for all years
       $result = $conn->query("
    SELECT 
        YEAR(Order_Date) AS year,
        SUM(Total) AS total_sales,
        COUNT(Order_ID) AS order_count
    FROM `Orders`
    WHERE status != 'Disabled'
    GROUP BY YEAR(Order_Date)
    ORDER BY year DESC
");
        
        while ($row = $result->fetch_assoc()) {
            $report_data[] = [
                'period' => $row['year'],
                'sales' => $row['total_sales'],
                'orders' => $row['order_count']
            ];
            $chart_labels[] = $row['year'];
            $chart_data[] = $row['total_sales'];
        }
        break;
}

// Handle export request
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . $report_type . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    fputcsv($output, ['Period', 'Total Sales', 'Number of Orders', 'Average Order Value']);
    
    // Write data rows
    foreach ($report_data as $row) {
        $avg = $row['orders'] > 0 ? $row['sales'] / $row['orders'] : 0;
        fputcsv($output, [
            $row['period'],
            number_format($row['sales'], 2),
            number_format($row['orders']),
            number_format($avg, 2)
        ]);
    }
    
    fclose($output);
    exit();
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
    <title>Sales Reports | PetShop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        canvas {
            display: block;
            height: 400px !important;
            width: 100% !important;
        }
        .report-card {
            transition: all 0.3s ease;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link.active {
            background-color: #4e73df;
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
                        <a class="nav-link text-light active" href="reports.php">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Sales Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="reports.php?report_type=<?php echo $report_type; ?>&export=1" class="btn btn-sm btn-outline-primary" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i>Export to CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Type Selector -->
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'weekly' ? 'active' : ''; ?>" 
                       href="reports.php?report_type=weekly">
                        <i class="fas fa-calendar-week me-1"></i>Weekly
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'monthly' ? 'active' : ''; ?>" 
                       href="reports.php?report_type=monthly">
                        <i class="fas fa-calendar-alt me-1"></i>Monthly
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'yearly' ? 'active' : ''; ?>" 
                       href="reports.php?report_type=yearly">
                        <i class="fas fa-calendar me-1"></i>Yearly
                    </a>
                </li>
            </ul>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary h-100 report-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL SALES</h6>
                                    <h2 class="mb-0">
                                        RM<?php 
                                            $total_sales = array_sum(array_column($report_data, 'sales'));
                                            echo number_format($total_sales, 2); 
                                        ?>
                                    </h2>
                                </div>
                                <i class="fas fa-dollar-sign fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success h-100 report-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL ORDERS</h6>
                                    <h2 class="mb-0">
                                        <?php 
                                            $total_orders = array_sum(array_column($report_data, 'orders'));
                                            echo number_format($total_orders); 
                                        ?>
                                    </h2>
                                </div>
                                <i class="fas fa-shopping-cart fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success h-100 report-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">AVERAGE ORDER VALUE</h6>
                                    <h2 class="mb-0">
                                        RM<?php 
                                            $avg_order = $total_orders > 0 ? $total_sales / $total_orders : 0;
                                            echo number_format($avg_order, 2); 
                                        ?>
                                    </h2>
                                </div>
                                <i class="fas fa-calculator fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2"></i>Sales Trend
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Detailed Report
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Period</th>
                                    <th>Total Sales</th>
                                    <th>Number of Orders</th>
                                    <th>Average Order Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report_data)): ?>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['period']); ?></td>
                                            <td>RM<?php echo number_format($row['sales'], 2); ?></td>
                                            <td><?php echo number_format($row['orders']); ?></td>
                                            <td>RM<?php echo number_format($row['orders'] > 0 ? $row['sales'] / $row['orders'] : 0, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No data available</td>
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

    // Initialize chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($chart_labels)); ?>,
            datasets: [{
                label: 'Sales (RM)',
                data: <?php echo json_encode(array_reverse($chart_data)); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'RM' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>