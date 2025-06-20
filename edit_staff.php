<?php
include('db_connection.php');

// Initialize variables
$error = '';
$password_error = '';
$success = '';

// Get shop settings
$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

// Get staff member data
$id = $_GET['id'];
$sql = "SELECT * FROM Staff WHERE Staff_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $position = $_POST['position'];
    $status = $_POST['status'];
    $resetPassword = isset($_POST['resetPassword']) ? true : false;
    $newPassword = $_POST['newPassword'] ?? '';

    // Validate password if reset is requested
    if ($resetPassword) {
        if (empty($newPassword)) {
            $password_error = "Please enter a new password";
        } elseif (strlen($newPassword) < 8) {
            $password_error = "Password must be at least 8 characters long";
        } elseif (!preg_match('/[A-Z]/', $newPassword)) {
            $password_error = "Password must contain at least one uppercase letter";
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $password_error = "Password must contain at least one number";
        } elseif (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            $password_error = "Password must contain at least one special character";
        }
    }

    // Only proceed if no password errors
    if (!$resetPassword || empty($password_error)) {
        // Prepare the base update query
        $update_sql = "UPDATE Staff SET position=?, status=?";
        $params = [$position, $status];
        $types = "ss"; // position and status are strings
        
        // Add password to update if reset is requested
        if ($resetPassword) {
            $update_sql .= ", Staff_Password=?";
            $params[] = $newPassword;
            $types .= "s"; // password is string
        }
        
        $update_sql .= " WHERE Staff_ID=?";
        $params[] = $id;
        $types .= "i"; // id is integer
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Staff member updated successfully";
            header("Location: manage_staff.php");
            exit();
        } else {
            $error = "Error updating staff member: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .confirmation-modal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .confirmation-modal .modal-title {
            color: #343a40;
            font-weight: 600;
        }
        .confirmation-modal .modal-body {
            padding: 2rem;
            font-size: 1.1rem;
        }
        .confirmation-modal .modal-footer {
            border-top: none;
            justify-content: center;
            padding-bottom: 2rem;
        }
        .confirmation-modal .btn-confirm {
            background-color: #28a745;
            border-color: #28a745;
            padding: 0.5rem 1.5rem;
        }
        .confirmation-modal .btn-cancel {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 0.5rem 1.5rem;
        }
        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
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
        .password-requirements {
            margin-top: 8px;
            font-size: 13px;
            color: var(--gray);
            background-color: rgba(240, 242, 245, 0.8);
            padding: 10px 15px;
            border-radius: 8px;
        }
        .requirement {
            margin-bottom: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            color: #6c757d;
        }
        .requirement i {
            margin-right: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            display: none;
        }
        .requirement.text-success {
            color: var(--primary) !important;
        }
        .disabled-field {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed;
        }
        .disabled-label {
            color: #6c757d;
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
                        <a class="nav-link text-light active" data-bs-toggle="collapse" href="#staffMenu">
                            <i class="fas fa-users me-2"></i>Staff Management
                        </a>
                        <div class="collapse show" id="staffMenu">
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
                <h1 class="h2"><i class="fas fa-user-edit me-2"></i>Edit Staff Member</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage_staff.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Staff List
                    </a>
                </div>
            </div>



            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Staff ID: <?php echo $result['Staff_ID']; ?>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_name" class="form-label disabled-label">Full Name</label>
                                <input type="text" class="form-control disabled-field" id="Staff_name" name="Staff_name" 
                                       value="<?php echo htmlspecialchars($result['Staff_name']); ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="Staff_Username" class="form-label disabled-label">Username</label>
                                <input type="text" class="form-control disabled-field" id="Staff_Username" name="Staff_Username" 
                                       value="<?php echo htmlspecialchars($result['Staff_Username']); ?>" readonly disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_Email" class="form-label disabled-label">Email Address</label>
                                <input type="email" class="form-control disabled-field" id="Staff_Email" name="Staff_Email" 
                                       value="<?php echo htmlspecialchars($result['Staff_Email']); ?>" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <select class="form-select" id="position" name="position" required>
                                    <option value="Manager" <?= ($result['position'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                                    <option value="Sales Associate" <?= ($result['position'] == 'Sales Associate') ? 'selected' : '' ?>>Sales Associate</option>
                                    <option value="Inventory Specialist" <?= ($result['position'] == 'Inventory Specialist') ? 'selected' : '' ?>>Inventory Specialist</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active" <?= ($result['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($result['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Password reset section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="resetPassword" name="resetPassword">
                                    <label class="form-check-label" for="resetPassword">
                                        Reset Password
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Password field (hidden by default) -->
                        <div class="row mb-3" id="passwordField" style="display: none;">
                            <div class="col-md-6">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="text" class="form-control" id="newPassword" name="newPassword">
                                <div class="password-requirements mt-2">
                                    <div class="requirement" id="length-check">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>At least 8 characters</span>
                                    </div>
                                    <div class="requirement" id="uppercase-check">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>At least 1 uppercase letter</span>
                                    </div>
                                    <div class="requirement" id="number-check">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>At least 1 number</span>
                                    </div>
                                    <div class="requirement" id="symbol-check">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>At least 1 special character</span>
                                    </div>
                                </div>
                                <small class="text-muted password-note">
                                    <i class="fas fa-info-circle me-1"></i>
                                    The password will be stored as plain text in the database
                                </small>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="manage_staff.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Confirm Password Reset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>You are about to reset this staff member's password.</p>
                <p class="fw-bold">This action cannot be undone.</p>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-lock me-2"></i>
                    The new password will be stored as plain text in the database.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cancel text-white" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success btn-confirm text-white" id="confirmReset">
                    <i class="fas fa-check me-1"></i> Confirm Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-2">
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
// Form validation
(function () {
    'use strict'
    
    var forms = document.querySelectorAll('.needs-validation')
    
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})();

// Show/hide password field based on checkbox
document.getElementById('resetPassword').addEventListener('change', function() {
    document.getElementById('passwordField').style.display = this.checked ? 'block' : 'none';
});

// Enhanced confirmation dialog
document.querySelector('form').addEventListener('submit', function(e) {
    if (document.getElementById('resetPassword').checked) {
        const password = document.getElementById('newPassword').value;
        
        if (!password) {
            // Show error alert if password is empty
            const alertHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Please enter a new password
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Insert alert before the form
            const form = document.querySelector('form');
            form.insertAdjacentHTML('beforebegin', alertHTML);
            
            // Scroll to the alert
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            e.preventDefault();
            return;
        }
        
        // Show beautiful modal instead of default confirm
        e.preventDefault();
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmationModal.show();
        
        // Handle confirm button click
        document.getElementById('confirmReset').addEventListener('click', function() {
            confirmationModal.hide();
            document.querySelector('form').submit();
        }, { once: true });
    }
});

// Password validation
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    
    if (password.length === 0) {
        // Hide all icons when password is empty
        document.querySelectorAll('.requirement i').forEach(icon => {
            icon.style.display = 'none';
        });
        return;
    }
    
    // Show all icons when typing starts
    document.querySelectorAll('.requirement i').forEach(icon => {
        icon.style.display = 'inline-block';
    });
    
    // Check length requirement
    toggleIconVisibility(document.getElementById('length-check'), password.length >= 8);
    
    // Check uppercase requirement
    toggleIconVisibility(document.getElementById('uppercase-check'), /[A-Z]/.test(password));
    
    // Check number requirement
    toggleIconVisibility(document.getElementById('number-check'), /[0-9]/.test(password));
    
    // Check symbol requirement
    toggleIconVisibility(document.getElementById('symbol-check'), /[^A-Za-z0-9]/.test(password));
});

function toggleIconVisibility(element, isValid) {
    const icon = element.querySelector('i');
    
    if (isValid) {
        icon.classList.remove('fa-times-circle', 'text-danger');
        icon.classList.add('fa-check-circle', 'text-success');
    } else {
        icon.classList.remove('fa-check-circle', 'text-success');
        icon.classList.add('fa-times-circle', 'text-danger');
    }
}
</script>
</body>
</html>