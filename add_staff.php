<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['Staff_name'];
    $username = $_POST['Staff_Username'];
    $email = $_POST['Staff_Email']; 
    $password = $_POST['Staff_Password'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    // Check if email already exists in Staff or Customer tables
    $emailCheckQuery = "SELECT COUNT(*) as count FROM (
                        SELECT Staff_Email as email FROM Staff WHERE Staff_Email = ?
                        UNION ALL
                        SELECT Customer_email as email FROM Customer WHERE Customer_email = ?
                    ) as combined";
    $stmt = $conn->prepare($emailCheckQuery);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $emailExists = $row['count'] > 0;

    if ($emailExists) {
        $error = "This email is already registered in our system. Please use a different email.";
    } 
    // Password validation
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one number.";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = "Password must contain at least one special character.";
    } else {
        $sql = "INSERT INTO Staff (Staff_name, Staff_Username, Staff_Email, Staff_Password, position, status) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $username, $email, $password, $position, $status);

        if ($stmt->execute()) {
            header("Location: manage_staff.php?success=1");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

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
    <title>Add Staff - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
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
        }
        .requirement i {
            margin-right: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .requirement.text-success {
            color: var(--primary) !important;
        }
        .email-feedback {
            display: none;
            font-size: 0.875em;
        }
        .email-checking {
            color: #6c757d;
        }
        .email-valid {
            color: #198754;
        }
        .email-invalid {
            color: #dc3545;
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
                <h1 class="h2"><i class="fas fa-user-plus me-2"></i>Add New Staff</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage_staff.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Staff List
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-edit me-2"></i>Staff Information
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="Staff_name" name="Staff_name" required>
                                <div class="invalid-feedback">
                                    Please provide a valid name.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="Staff_Username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="Staff_Username" name="Staff_Username" required>
                                <div class="invalid-feedback">
                                    Please choose a username.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="Staff_Email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="Staff_Email" name="Staff_Email" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email.
                                </div>
                                <div id="emailFeedback" class="email-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <select class="form-select" id="position" name="position" required>
                                    <option value="" selected disabled>Select position</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Sales Associate">Sales Associate</option>
                                    <option value="Inventory Specialist">Inventory Specialist</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="Staff_Password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="Staff_Password" name="Staff_Password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please provide a password.
                                </div>
                                <div class="password-requirements mt-2">
                                    <div class="requirement" id="length-check">
                                        <i class="fas fa-times-circle text-danger"></i>
                                        <i class="fas fa-check-circle text-success d-none"></i>
                                        <span>At least 8 characters</span>
                                    </div>
                                    <div class="requirement" id="uppercase-check">
                                        <i class="fas fa-times-circle text-danger"></i>
                                        <i class="fas fa-check-circle text-success d-none"></i>
                                        <span>At least 1 uppercase letter</span>
                                    </div>
                                    <div class="requirement" id="number-check">
                                        <i class="fas fa-times-circle text-danger"></i>
                                        <i class="fas fa-check-circle text-success d-none"></i>
                                        <span>At least 1 number</span>
                                    </div>
                                    <div class="requirement" id="symbol-check">
                                        <i class="fas fa-times-circle text-danger"></i>
                                        <i class="fas fa-check-circle text-success d-none"></i>
                                        <span>At least 1 special character</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <div class="invalid-feedback">
                                    Passwords must match.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Save Staff
                            </button>
                        </div>
                    </form>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
(function () {
    'use strict'
    
    var forms = document.querySelectorAll('.needs-validation')
    
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                // Check password match
                var password = document.getElementById('Staff_Password');
                var confirmPassword = document.getElementById('confirmPassword');
                
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
                
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})();

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    var passwordInput = document.getElementById('Staff_Password');
    var icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Password validation
document.getElementById('Staff_Password').addEventListener('input', function() {
    const password = this.value;
    
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
    const crossIcon = element.querySelector('.fa-times-circle');
    const checkIcon = element.querySelector('.fa-check-circle');
    
    if (isValid) {
        crossIcon.classList.add('d-none');
        checkIcon.classList.remove('d-none');
        element.classList.add('text-success');
        element.classList.remove('text-danger');
    } else {
        crossIcon.classList.remove('d-none');
        checkIcon.classList.add('d-none');
        element.classList.add('text-danger');
        element.classList.remove('text-success');
    }
}

// Email availability check
document.getElementById('Staff_Email').addEventListener('blur', function() {
    const email = this.value;
    const emailFeedback = document.getElementById('emailFeedback');
    
    if (!email) return;
    
    if (!validateEmail(email)) {
        emailFeedback.textContent = 'Please enter a valid email address';
        emailFeedback.className = 'email-feedback email-invalid';
        emailFeedback.style.display = 'block';
        return;
    }
    
    emailFeedback.textContent = 'Checking email availability...';
    emailFeedback.className = 'email-feedback email-checking';
    emailFeedback.style.display = 'block';
    
    // AJAX check for email availability
    fetch('check_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            emailFeedback.textContent = 'This email is already registered';
            emailFeedback.className = 'email-feedback email-invalid';
        } else {
            emailFeedback.textContent = 'Email is available';
            emailFeedback.className = 'email-feedback email-valid';
        }
    })
    .catch(error => {
        emailFeedback.textContent = 'Error checking email availability';
        emailFeedback.className = 'email-feedback email-invalid';
    });
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
</script>
</body>
</html>