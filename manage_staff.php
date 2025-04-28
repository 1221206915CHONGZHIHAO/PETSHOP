<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login Logs | PetShop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-login { color: #28a745; }
        .status-logout { color: #dc3545; }
        .status-failed { color: #ffc107; }
    </style>
</head>
<body>

<!-- Reuse the same navbar and sidebar from admin_homepage.php -->
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
        <!-- Sidebar (same structure but with the new menu item) -->
        <nav id="sidebar" class="col-md-2 d-md-block bg-dark sidebar">
            <div class="position-sticky">
                <h4 class="text-light text-center py-3"><i class="fas fa-paw me-2"></i>Admin Menu</h4>
                <ul class="nav flex-column">
                    <!-- Keep all existing menu items -->
                    <!-- Add the new customer logs menu -->
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="customer_logs.php">
                            <i class="fas fa-user-clock me-2"></i>Customer Logs
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-clock me-2"></i>Customer Login Activity</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Simple Date Filter -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="date" class="form-control" id="logDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Logs Table - Simplified -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>johndoe</td>
                                    <td>john@example.com</td>
                                    <td><span class="status-login"><i class="fas fa-sign-in-alt"></i> Login</span></td>
                                    <td>2025-03-15 09:30:22</td>
                                </tr>
                                <tr>
                                    <td>janesmith</td>
                                    <td>jane@example.com</td>
                                    <td><span class="status-logout"><i class="fas fa-sign-out-alt"></i> Logout</span></td>
                                    <td>2025-03-15 10:15:42</td>
                                </tr>
                                <tr>
                                    <td>alicej</td>
                                    <td>alice@example.com</td>
                                    <td><span class="status-login"><i class="fas fa-sign-in-alt"></i> Login</span></td>
                                    <td>2025-03-15 11:22:18</td>
                                </tr>
                                <tr>
                                    <td>(failed attempt)</td>
                                    <td>unknown@example.com</td>
                                    <td><span class="status-failed"><i class="fas fa-exclamation-triangle"></i> Failed</span></td>
                                    <td>2025-03-15 12:05:33</td>
                                </tr>
                                <tr>
                                    <td>robertb</td>
                                    <td>robert@example.com</td>
                                    <td><span class="status-logout"><i class="fas fa-sign-out-alt"></i> Logout</span></td>
                                    <td>2025-03-15 14:30:10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Date filter functionality
    document.getElementById('logDate').addEventListener('change', function() {
        // In a real implementation, this would reload the table with filtered data
        console.log('Date filter changed to:', this.value);
    });
});
</script>
</body>
</html>