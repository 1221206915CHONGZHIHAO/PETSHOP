<?php
include('db_connection.php');

$id = $_GET['id'];
$sql = "SELECT * FROM Staff WHERE Staff_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['Staff_name'];
    $username = $_POST['Staff_Username'];
    $email = $_POST['Staff_Email'];
    $position = $_POST['position'];
    $status = $_POST['status'];
    $resetPassword = isset($_POST['resetPassword']) ? true : false;
    $newPassword = $_POST['newPassword'] ?? '';

    // Validate password if reset is requested
    if ($resetPassword && empty($newPassword)) {
        $password_error = "Please enter a new password";
    } elseif ($resetPassword && strlen($newPassword) < 6) {
        $password_error = "Password must be at least 6 characters";
    }

    // Only proceed if no password errors
    if (!$resetPassword || empty($password_error)) {
        $update_sql = "UPDATE Staff SET Staff_name=?, Staff_Username=?, Staff_Email=?, position=?, status=?";
        
        // Add password to update if reset is requested
        if ($resetPassword) {
            $update_sql .= ", Staff_Password=?";
        }
        
        $update_sql .= " WHERE Staff_ID=?";
        
        $stmt = $conn->prepare($update_sql);
        
        // Bind parameters based on whether password is being reset
        if ($resetPassword) {
            $stmt->bind_param("ssssssi", $name, $username, $email, $position, $status, $newPassword, $id);
        } else {
            $stmt->bind_param("sssssi", $name, $username, $email, $position, $status, $id);
        }

        if ($stmt->execute()) {
            header("Location: manage_staff.php?success=1");
            exit();
        } else {
            $error = "Error: " . $conn->error;
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
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-edit me-2"></i>Edit Staff Member</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage_staff.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Staff List
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($password_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $password_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Staff ID: <?php echo $result['Staff_ID']; ?>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="Staff_name" name="Staff_name" 
                                       value="<?php echo htmlspecialchars($result['Staff_name']); ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a valid name.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="Staff_Username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="Staff_Username" name="Staff_Username" 
                                       value="<?php echo htmlspecialchars($result['Staff_Username']); ?>" required>
                                <div class="invalid-feedback">
                                    Please choose a username.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_Email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="Staff_Email" name="Staff_Email" 
                                       value="<?php echo htmlspecialchars($result['Staff_Email']); ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email.
                                </div>
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
                                    <option value="On Leave" <?= ($result['status'] == 'On Leave') ? 'selected' : '' ?>>On Leave</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="resetPassword" name="resetPassword">
                                    <label class="form-check-label" for="resetPassword">Reset Password</label>
                                </div>
                            </div>
                        </div>

                        <!-- Password field (hidden by default) -->
                        <div class="row mb-3" id="passwordField" style="display: none;">
                            <div class="col-md-6">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="text" class="form-control" id="newPassword" name="newPassword">
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
          <!-- Footer with same style as user homepage -->
  <footer style="background: linear-gradient(to bottom,rgb(134, 138, 135),rgba(46, 21, 1, 0.69));">
    <div class="container">
      <div class="row">
        <!-- Footer About -->
        <div class="col-md-5 mb-4 mb-lg-0">
          <div class="footer-about">
            <div class="footer-logo">
              <img src="Hachi_Logo.png" alt="Hachi Pet Shop" height="60">
            </div>
            <p>Your trusted partner in pet product. We're dedicated to providing quality products for pet lovers everywhere.</p>
            <div class="social-links">
              <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="fab fa-facebook"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
        
        <!-- Contact Info -->
        <div class="col-md-7">
          <h4 class="text-white mb-3">Contact Us</h4>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-map-marker-alt"></i>
                <span>123 Pet Street, Animal City</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-phone"></i>
                <span>+1 (555) 123-4567</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-envelope"></i>
                <span>info@hachipetshop.com</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-clock"></i>
                <span>Mon-Fri: 9AM - 6PM</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer Bottom -->
      <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px;">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-md-0 text-white">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
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
</script>
</body>
</html>