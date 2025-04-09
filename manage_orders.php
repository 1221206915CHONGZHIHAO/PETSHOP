<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same CSS as your dashboard */
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
        
        main {
            flex: 1;
            overflow-y: auto;
            background-color: var(--content-bg);
            padding: 20px;
        }
        
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
    </style>
</head>
<body>

<!-- Navigation Bar -->
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
                <a href="login.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="staff_example.png" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                    <h5 class="text-white mb-1">John Doe</h5>
                    <small class="text-muted">Staff Member</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light active" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light active" href="manage_orders.php">
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

        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-shopping-cart me-2"></i>Manage Orders
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Orders</a></li>
                            <li><a class="dropdown-item" href="#">Pending</a></li>
                            <li><a class="dropdown-item" href="#">Processing</a></li>
                            <li><a class="dropdown-item" href="#">Completed</a></li>
                            <li><a class="dropdown-item" href="#">Cancelled</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                        <i class="fas fa-plus me-1"></i> New Order
                    </button>
                </div>
            </div>

            <!-- Order Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-primary stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL ORDERS</h6>
                                    <h2 class="mb-0">42</h2>
                                </div>
                                <i class="fas fa-shopping-cart fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">View All</a>
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
                            <a class="small text-white stretched-link" href="#">View Details</a>
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
                            <a class="small text-white stretched-link" href="#">Process Now</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">RETURN REQUESTS</h6>
                                    <h2 class="mb-0">3</h2>
                                </div>
                                <i class="fas fa-undo fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-danger bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="returns.php">Handle Returns</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search orders by ID, customer, or product...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" placeholder="From date">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" placeholder="To date">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list me-2"></i>Current Orders
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">CSV</a></li>
                            <li><a class="dropdown-item" href="#">Excel</a></li>
                            <li><a class="dropdown-item" href="#">PDF</a></li>
                        </ul>
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
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#1005</td>
                                    <td>Michael Brown</td>
                                    <td>2025-03-05</td>
                                    <td>3</td>
                                    <td>$75.25</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" title="Process">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1004</td>
                                    <td>Sarah Wilson</td>
                                    <td>2025-03-04</td>
                                    <td>5</td>
                                    <td>$142.80</td>
                                    <td><span class="badge bg-info">Processing</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" title="Ship">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1003</td>
                                    <td>Robert Johnson</td>
                                    <td>2025-03-03</td>
                                    <td>2</td>
                                    <td>$45.99</td>
                                    <td><span class="badge bg-success">Shipped</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1002</td>
                                    <td>Emily Davis</td>
                                    <td>2025-03-02</td>
                                    <td>1</td>
                                    <td>$29.99</td>
                                    <td><span class="badge bg-secondary">Cancelled</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#1001</td>
                                    <td>David Wilson</td>
                                    <td>2025-03-01</td>
                                    <td>4</td>
                                    <td>$98.75</td>
                                    <td><span class="badge bg-success">Delivered</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- New Order Modal -->
<div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="newOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newOrderModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Create New Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customerSelect" class="form-label">Customer</label>
                            <select class="form-select" id="customerSelect">
                                <option selected>Select customer</option>
                                <option>John Doe (john@example.com)</option>
                                <option>Jane Smith (jane@example.com)</option>
                                <option>Michael Brown (michael@example.com)</option>
                                <option>+ Add new customer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="orderDate" class="form-label">Order Date</label>
                            <input type="date" class="form-control" id="orderDate" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Products</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Search products...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Premium Dog Food (5kg)</td>
                                        <td>$29.99</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" value="1" min="1" style="width: 70px;">
                                        </td>
                                        <td>$29.99</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Add Product
                        </button>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="shippingMethod" class="form-label">Shipping Method</label>
                            <select class="form-select" id="shippingMethod">
                                <option selected>Standard Shipping (3-5 days)</option>
                                <option>Express Shipping (1-2 days)</option>
                                <option>Store Pickup</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod">
                                <option selected>Credit Card</option>
                                <option>PayPal</option>
                                <option>Bank Transfer</option>
                                <option>Cash on Delivery</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="orderNotes" class="form-label">Order Notes</label>
                        <textarea class="form-control" id="orderNotes" rows="3" placeholder="Any special instructions..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Subtotal: $29.99</h5>
                            <h5>Shipping: $5.00</h5>
                            <h4>Total: $34.99</h4>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary">Create Order</button>
                        </div>
                    </div>
                </form>
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
    
    // Status badge colors
    const statusBadges = document.querySelectorAll('.badge');
    statusBadges.forEach(badge => {
        if (badge.textContent === 'Pending') {
            badge.classList.add('bg-warning', 'text-dark');
        } else if (badge.textContent === 'Processing') {
            badge.classList.add('bg-info');
        } else if (badge.textContent === 'Shipped' || badge.textContent === 'Delivered') {
            badge.classList.add('bg-success');
        } else if (badge.textContent === 'Cancelled') {
            badge.classList.add('bg-secondary');
        }
    });
    
    // Simulate order processing
    const processButtons = document.querySelectorAll('.btn-outline-success');
    processButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const statusBadge = row.querySelector('.badge');
            statusBadge.textContent = 'Processing';
            statusBadge.className = 'badge bg-info';
            this.innerHTML = '<i class="fas fa-truck"></i>';
            this.classList.replace('btn-outline-success', 'btn-outline-info');
            this.title = 'Ship';
        });
    });
    
    // Simulate order shipping
    const shipButtons = document.querySelectorAll('.btn-outline-info');
    shipButtons.forEach(button => {
        if (button.title === 'Ship') {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const statusBadge = row.querySelector('.badge');
                statusBadge.textContent = 'Shipped';
                statusBadge.className = 'badge bg-success';
                this.innerHTML = '<i class="fas fa-print"></i>';
                this.classList.replace('btn-outline-info', 'btn-outline-secondary');
                this.title = 'Print';
            });
        }
    });
});
</script>

</body>
</html>