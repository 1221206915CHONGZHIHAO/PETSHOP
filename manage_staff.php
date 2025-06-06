<?php
include('db_connection.php');

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .deactivate-modal .modal-header {
            background-color: #ffc107;
            color: #000;
        }
        .deactivate-modal .modal-title i {
            margin-right: 8px;
        }
        .deactivate-modal .staff-name {
            color: #ffc107;
            font-weight: 600;
        }
        .reactivate-modal .modal-header {
            background-color: #198754;
            color: #fff;
        }
        .reactivate-modal .modal-title i {
            margin-right: 8px;
        }
        .reactivate-modal .staff-name {
            color: #198754;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .tab-pane {
            padding-top: 20px;
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
        <span class="text-light me-3"><i class="fas fa-user-circle me-1"></i> Welcome, <?php echo $_SESSION['username'] ?? 'ADMIN'; ?></span>
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
                <a class="nav-link text-light active" data-bs-toggle="collapse" href="#staffMenu">
                    <i class="fas fa-users me-2"></i>Staff Management
                </a>
                <div class="collapse show" id="staffMenu">
                    <ul class="nav flex-column ps-4">
                        <li class="nav-item">
                            <a class="nav-link text-light active" href="manage_staff.php">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Staff Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_staff.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add New Staff
                    </a>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="staffTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-staff" type="button" role="tab">
                        <i class="fas fa-user-check me-1"></i> Active Staff
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive-staff" type="button" role="tab">
                        <i class="fas fa-user-slash me-1"></i> Inactive Staff
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="staffTabsContent">
                <!-- Active Staff Tab -->
                <div class="tab-pane fade show active" id="active-staff" role="tabpanel" aria-labelledby="active-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sql_active = "SELECT * FROM Staff WHERE status = 'Active'";
                                $result_active = $conn->query($sql_active);
                                while ($row = $result_active->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['Staff_ID']; ?></td>
                                        <td><?php echo $row['Staff_name']; ?></td>
                                        <td><?php echo $row['Staff_Username']; ?></td>
                                        <td><?php echo $row['Staff_Email']; ?></td>
                                        <td><?php echo $row['position']; ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <a href="edit_staff.php?id=<?php echo $row['Staff_ID']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-warning deactivate-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deactivateModal"
                                                    data-staff-id="<?php echo $row['Staff_ID']; ?>"
                                                    data-staff-name="<?php echo htmlspecialchars($row['Staff_name']); ?>">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Inactive Staff Tab -->
                <div class="tab-pane fade" id="inactive-staff" role="tabpanel" aria-labelledby="inactive-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sql_inactive = "SELECT * FROM Staff WHERE status = 'Inactive'";
                                $result_inactive = $conn->query($sql_inactive);
                                while ($row = $result_inactive->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['Staff_ID']; ?></td>
                                        <td><?php echo $row['Staff_name']; ?></td>
                                        <td><?php echo $row['Staff_Username']; ?></td>
                                        <td><?php echo $row['Staff_Email']; ?></td>
                                        <td><?php echo $row['position']; ?></td>
                                        <td><span class="badge bg-danger">Inactive</span></td>
                                        <td>
                                            <a href="edit_staff.php?id=<?php echo $row['Staff_ID']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success reactivate-btn"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reactivateModal"
                                                    data-staff-id="<?php echo $row['Staff_ID']; ?>"
                                                    data-staff-name="<?php echo htmlspecialchars($row['Staff_name']); ?>">
                                                <i class="fas fa-user-check"></i> Reactivate
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade deactivate-modal" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deactivation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate the following staff member?</p>
                <p><strong>ID:</strong> <span id="modalStaffId"></span></p>
                <p><strong>Name:</strong> <span class="staff-name" id="modalStaffName"></span></p>
                <p class="text-danger"><small>This staff member will no longer be able to access the system!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <a id="confirmDeactivateBtn" href="#" class="btn btn-warning">
                    <i class="fas fa-user-slash"></i> Confirm Deactivate
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reactivate Confirmation Modal -->
<div class="modal fade reactivate-modal" id="reactivateModal" tabindex="-1" aria-labelledby="reactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="reactivateModalLabel">
                    <i class="fas fa-user-check me-2"></i> Confirm Reactivation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reactivate the following staff member?</p>
                <p><strong>ID:</strong> <span id="modalReactivateStaffId"></span></p>
                <p><strong>Name:</strong> <span class="staff-name" id="modalReactivateStaffName"></span></p>
                <p class="text-success"><small>This staff member will regain access to the system!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <a id="confirmReactivateBtn" href="#" class="btn btn-success">
                    <i class="fas fa-user-check"></i> Confirm Reactivate
                </a>
            </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Deactivate modal handler
    var deactivateModal = document.getElementById('deactivateModal');
    deactivateModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var staffId = button.getAttribute('data-staff-id');
        var staffName = button.getAttribute('data-staff-name');
        
        document.getElementById('modalStaffId').textContent = staffId;
        document.getElementById('modalStaffName').textContent = staffName;
        document.getElementById('confirmDeactivateBtn').href = 'deactivate_staff.php?id=' + staffId;
    });

    // Reactivate modal handler
    var reactivateModal = document.getElementById('reactivateModal');
    reactivateModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var staffId = button.getAttribute('data-staff-id');
        var staffName = button.getAttribute('data-staff-name');
        
        document.getElementById('modalReactivateStaffId').textContent = staffId;
        document.getElementById('modalReactivateStaffName').textContent = staffName;
        document.getElementById('confirmReactivateBtn').href = 'activate_staff.php?id=' + staffId;
    });
});
</script>
</body>
</html>