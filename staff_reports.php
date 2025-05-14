<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php?redirect=staff_reports.php");
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
                YEARWEEK(order_date) AS week,
                SUM(Total) AS total_sales,
                COUNT(Order_ID) AS order_count
            FROM orders
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
            GROUP BY YEARWEEK(order_date)
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
                DATE_FORMAT(order_date, '%Y-%m') AS month,
                SUM(Total) AS total_sales,
                COUNT(Order_ID) AS order_count
            FROM orders
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
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
                YEAR(order_date) AS year,
                SUM(Total) AS total_sales,
                COUNT(Order_ID) AS order_count
            FROM orders
            GROUP BY YEAR(order_date)
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
    Welcome, <?php echo htmlspecialchars($staff_username); ?>
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
                            echo strtoupper(substr($staff_username, 0, 1)); 
                            ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <h5 class="text-white mb-1"><?php echo htmlspecialchars($staff_username); ?></h5>
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
                        <a class="nav-link text-light active" href="staff_reports.php">
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
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Sales Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="staff_reports.php?report_type=<?php echo $report_type; ?>&export=1" class="btn btn-sm btn-outline-primary" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i>Export to CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Type Selector -->
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'weekly' ? 'active' : ''; ?>" 
                       href="staff_reports.php?report_type=weekly">
                        <i class="fas fa-calendar-week me-1"></i>Weekly
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'monthly' ? 'active' : ''; ?>" 
                       href="staff_reports.php?report_type=monthly">
                        <i class="fas fa-calendar-alt me-1"></i>Monthly
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $report_type === 'yearly' ? 'active' : ''; ?>" 
                       href="staff_reports.php?report_type=yearly">
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
                                        $<?php 
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
                    <div class="card text-white bg-info h-100 report-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">AVG. ORDER VALUE</h6>
                                    <h2 class="mb-0">
                                        $<?php 
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
                                            <td>$<?php echo number_format($row['sales'], 2); ?></td>
                                            <td><?php echo number_format($row['orders']); ?></td>
                                            <td>$<?php echo number_format($row['orders'] > 0 ? $row['sales'] / $row['orders'] : 0, 2); ?></td>
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
                label: 'Sales ($)',
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
                            return '$' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
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