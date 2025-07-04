<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php?redirect=customer_list.php");
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

// Handle deactivate/reactivate action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $customer_id = $conn->real_escape_string($_GET['id']);
    $action = $conn->real_escape_string($_GET['action']);
    
    if ($action === 'deactivate') {
        $sql = "UPDATE customer SET is_active = 0 WHERE Customer_id = '$customer_id'";
    } elseif ($action === 'reactivate') {
        $sql = "UPDATE customer SET is_active = 1 WHERE Customer_id = '$customer_id'";
    }
    
    if (isset($sql)) {
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Customer account updated successfully";
            header("Location: customer_list.php");
            exit;
        } else {
            $_SESSION['error'] = "Error updating customer: " . $conn->error;
        }
    }
}

// Fetch customers with their addresses and status
$sql = "SELECT c.Customer_id, c.Customer_name, c.Customer_email, c.is_active,
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
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        /* Ensure charts render correctly */
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
        
        /* Sidebar styling */
        #sidebar {
            min-height: 100vh;
        }
        
        /* Main content area */
        main {
            padding-top: 1rem;
        }
        
        /* Table styling */
        .table-responsive {
            overflow-x: auto;
        }
        .table th {
            white-space: nowrap;
        }
        
        /* Address formatting */
        .address-line {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        
        /* Card header styling */
        .card-header {
            font-weight: 500;
        }
        
        /* Status styling */
        .status-active {
            color: #28a745;
            font-weight: 500;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: 500;
        }
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            #sidebar.show {
                transform: translateX(0);
            }
            main {
                margin-left: 0 !important;
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
                        <a class="nav-link text-light active" data-bs-toggle="collapse" href="#customerMenu">
                            <i class="fas fa-user-friends me-2"></i>Customer Management
                        </a>
                        <div class="collapse show" id="customerMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light active" href="customer_list.php">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Customer List</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                </div>
            </div>

            <!-- Display messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Registered Customers
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['Customer_name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($customer['Customer_email'] ?? ''); ?></td>
                                        <td>
                                            <?php if ($customer['is_active'] == 1): ?>
                                                <span class="status-active"><i class="fas fa-circle me-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="status-inactive"><i class="fas fa-circle me-1"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
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
                                        <td>
                                            <?php if ($customer['is_active'] == 1): ?>
                                                <a href="#" 
                                                   class="btn btn-sm btn-danger action-btn deactivate-btn" 
                                                   data-id="<?php echo $customer['Customer_id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($customer['Customer_name']); ?>">
                                                    <i class="fas fa-ban"></i> Deactivate
                                                </a>
                                            <?php else: ?>
                                                <a href="#" 
                                                   class="btn btn-sm btn-success action-btn reactivate-btn" 
                                                   data-id="<?php echo $customer['Customer_id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($customer['Customer_name']); ?>">
                                                    <i class="fas fa-check"></i> Reactivate
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No registered customers found</td>
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

<!-- Confirmation Modal -->
<div class="modal fade confirmation-modal" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="confirmation-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h4 id="confirmationMessage">Are you sure you want to perform this action?</h4>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <i class="fas fa-check-circle me-2"></i>
            <span id="toastMessage">Action completed successfully!</span>
        </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle functionality
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});

// Confirmation modal and action handling
document.addEventListener('DOMContentLoaded', function() {
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    let currentAction = '';
    let currentId = '';
    let currentName = '';
    
    // Deactivate button click handler
    document.querySelectorAll('.deactivate-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentAction = 'deactivate';
            currentId = this.getAttribute('data-id');
            currentName = this.getAttribute('data-name');
            
            // Update modal content
            document.getElementById('confirmationModalTitle').textContent = 'Confirm Deactivation';
            document.getElementById('confirmationMessage').textContent = `Are you sure you want to deactivate ${currentName}?`;
            
            // Show modal
            confirmationModal.show();
        });
    });
    
    // Reactivate button click handler
    document.querySelectorAll('.reactivate-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentAction = 'reactivate';
            currentId = this.getAttribute('data-id');
            currentName = this.getAttribute('data-name');
            
            // Update modal content
            document.getElementById('confirmationModalTitle').textContent = 'Confirm Reactivation';
            document.getElementById('confirmationMessage').textContent = `Are you sure you want to reactivate ${currentName}?`;
            
            // Show modal
            confirmationModal.show();
        });
    });
    
    // Confirm action button click handler
    confirmActionBtn.addEventListener('click', function() {
        confirmationModal.hide();
        
        // Perform the action
        window.location.href = `customer_list.php?action=${currentAction}&id=${currentId}`;
        
        // Show success toast (this will only show if the redirect doesn't happen immediately)
        document.getElementById('toastMessage').textContent = 
            currentAction === 'deactivate' 
            ? `${currentName} has been deactivated successfully!` 
            : `${currentName} has been reactivated successfully!`;
        toast.show();
    });
});
</script>
</body>
</html>