<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 全局樣式 */
        :root {
            --sidebar-width: 250px;
            --sidebar-dark: #343a40;
            --sidebar-dark-active: #4b545c;
            --content-bg: #f8f9fa;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            flex-direction: column;
            background-color: var(--content-bg);
        }
        
        /* 導航欄 */
        .navbar {
            flex-shrink: 0;
            background-color: #212529 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        /* 主容器 */
        .container-fluid {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow: hidden;
        }
        
        .row {
            flex: 1;
            margin: 0;
            overflow: hidden;
        }
        
        /* 側邊欄 */
        .sidebar {
            background-color: var(--sidebar-dark);
            color: white;
            height: 100%;
            width: var(--sidebar-width);
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            margin: 0.1rem 0;
            border-radius: 0.25rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: var(--sidebar-dark-active);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        .sidebar .collapse {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
        }
        
        /* 主內容區 */
        main {
            flex: 1;
            overflow-y: auto;
            background-color: var(--content-bg);
            padding: 20px;
        }
        
        /* 卡片樣式 */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        /* 統計卡片 */
        .stat-card {
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
        }
        
        .stat-card h6 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h2 {
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stat-card i {
            opacity: 0.8;
        }
        
        /* 表格樣式 */
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 0.75rem 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        /* 響應式調整 */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            main {
                margin-left: 0;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
        }
        
        /* 圖表容器 */
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
    </style>
</head>
<body>

<!-- 導航欄 -->
<nav class="navbar navbar-expand navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-paw me-2"></i>PetShop Staff
        </a>
    </div>
    <div class="navbar-collapse justify-content-end">
        <ul class="navbar-nav">
            <li class="nav-item">
                <span class="nav-link text-light me-2">
                    <i class="fas fa-user-circle me-1"></i>Welcome, John
                </span>
            </li>
            <li class="nav-item">
                <a href="admin_login.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- 側邊欄 -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="staff_example.png" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                    <h5 class="text-white mb-1">John Doe</h5>
                    <small class="text-muted">Staff Member</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="staff_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="manage_orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="">
                                        <i class="fas fa-history me-2"></i>Order History
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="returns.php">
                                        <i class="fas fa-undo me-2"></i>Returns
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="customer_service.php">
                            <i class="fas fa-headset me-2"></i>Customer Service
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_email.php">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <span class="badge bg-danger float-end">3</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="tasks.php">
                            <i class="fas fa-tasks me-2"></i>My Tasks
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

        <!-- 主內容區 -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>Staff Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i> Today
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync me-1"></i> Refresh
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> New Task
                    </button>
                </div>
            </div>

            <!-- 統計卡片 -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-primary stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">ASSIGNED TASKS</h6>
                                    <h2 class="mb-0">5</h2>
                                </div>
                                <i class="fas fa-tasks fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="tasks.php">View Details</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-success stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">COMPLETED TODAY</h6>
                                    <h2 class="mb-0">12</h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-success bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="tasks.php?filter=completed">View Details</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-warning stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">PENDING ORDERS</h6>
                                    <h2 class="mb-0">8</h2>
                                </div>
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-warning bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="manage_orders.php">View Details</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">URGENT REQUESTS</h6>
                                    <h2 class="mb-0">3</h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-danger bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="tasks.php?filter=urgent">View Details</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- 近期任務 -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-tasks me-2"></i>Recent Tasks
                            </h6>
                            <a href="tasks.php" class="btn btn-sm btn-link">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Process Order #1001</h6>
                                        <small class="text-muted">Due: Today 5:00 PM</small>
                                    </div>
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Respond to Customer Inquiry</h6>
                                        <small class="text-muted">Priority: High</small>
                                    </div>
                                    <span class="badge bg-danger">Urgent</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Update Inventory Records</h6>
                                        <small class="text-muted">Due: Tomorrow</small>
                                    </div>
                                    <span class="badge bg-primary">New</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 近期消息 -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-envelope me-2"></i>Recent Messages
                            </h6>
                            <a href="staff_email.php" class="btn btn-sm btn-link">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order Delivery Question</h6>
                                        <small>1 hour ago</small>
                                    </div>
                                    <p class="mb-1 text-muted">From: customer@example.com</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Product Return Request</h6>
                                        <small>3 hours ago</small>
                                    </div>
                                    <p class="mb-1 text-muted">From: another@customer.com</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Inventory Update</h6>
                                        <small>Yesterday</small>
                                    </div>
                                    <p class="mb-1 text-muted">From: manager@petshop.com</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 近期訂單 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shopping-cart me-2"></i>Recent Orders
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <a href="manage_orders.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> New Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#1001</td>
                                    <td>John Doe</td>
                                    <td>2025-03-01</td>
                                    <td>$120.00</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1002</td>
                                    <td>Jane Smith</td>
                                    <td>2025-03-02</td>
                                    <td>$85.50</td>
                                    <td><span class="badge bg-warning text-dark">Processing</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1003</td>
                                    <td>Robert Johnson</td>
                                    <td>2025-03-03</td>
                                    <td>$210.75</td>
                                    <td><span class="badge bg-info">Shipped</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 側邊欄切換
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });
    
    // 初始化圖表
    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Orders Processed',
                    data: [12, 19, 15, 20, 25, 22],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
    
    // 模擬實時更新
    setInterval(() => {
        const urgentBadge = document.querySelector('.bg-danger h2');
        if (urgentBadge) {
            const current = parseInt(urgentBadge.textContent);
            if (current > 0 && Math.random() > 0.7) {
                urgentBadge.textContent = current - 1;
            } else if (Math.random() > 0.9) {
                urgentBadge.textContent = current + 1;
            }
        }
    }, 5000);
});
</script>

</body>
</html>