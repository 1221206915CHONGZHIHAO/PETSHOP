<?php
include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .tab-pane {
            padding-top: 20px;
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
                                    <a class="nav-link text-light" href="staff_email.php">
                                        <i class="fas fa-envelope me-2"></i>Email Management
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
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-tag me-2"></i>Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
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
                                            <a href="activate_staff.php?id=<?php echo $row['Staff_ID']; ?>" 
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Are you sure you want to reactivate this account?')">
                                                <i class="fas fa-user-check"></i> Reactivate
                                            </a>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var deactivateModal = document.getElementById('deactivateModal');
    deactivateModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var staffId = button.getAttribute('data-staff-id');
        var staffName = button.getAttribute('data-staff-name');
        
        document.getElementById('modalStaffId').textContent = staffId;
        document.getElementById('modalStaffName').textContent = staffName;
        document.getElementById('confirmDeactivateBtn').href = 'deactivate_staff.php?id=' + staffId;
    });
});
</script>
</body>
</html>