<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'petshop');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Initialize variables
$errors = [];
$success = false;
$upload_success = false;

// Fetch staff details
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, position, Staff_Email, Staff_password, img_URL FROM staff WHERE Staff_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate current password (plain text comparison)
    if (empty($current_password)) {
        $errors['current_password'] = "Current password is required";
    } elseif ($current_password !== $staff['Staff_password']) {
        $errors['current_password'] = "Current password is incorrect";
    }

    // Validate new password
    if (empty($new_password)) {
        $errors['new_password'] = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $errors['new_password'] = "Password must be at least 8 characters long";
    }

    // Confirm new password
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // Update password if no errors
    if (empty($errors)) {
        $update_query = "UPDATE staff SET Staff_password = ? WHERE Staff_ID = ?";
        $stmt = $db->prepare($update_query);
        $stmt->bind_param("si", $new_password, $staff_id);
        
        if ($stmt->execute()) {
            $success = true;
            // Update the staff array with new password
            $staff['Staff_password'] = $new_password;
        } else {
            $errors['database'] = "Failed to update password: " . $db->error;
        }
    }
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['profileImage']['type'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file_type, $allowed_types)) {
        $errors['profileImage'] = "Only JPG, PNG, and GIF files are allowed";
    } elseif ($_FILES['profileImage']['size'] > $max_size) {
        $errors['profileImage'] = "File size must be less than 2MB";
    } else {
        $upload_dir = 'staff_avatars/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
        $avatar_filename = $staff_id . '.' . $file_extension;
        $avatar_path = $upload_dir . $avatar_filename;
        
        // Delete old image if it exists
        if (!empty($staff['img_URL']) && file_exists($staff['img_URL'])) {
            unlink($staff['img_URL']);
        }
        
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $avatar_path)) {
            // Update database with image path
            $update_query = "UPDATE staff SET img_URL = ? WHERE Staff_ID = ?";
            $stmt = $db->prepare($update_query);
            $stmt->bind_param("si", $avatar_path, $staff_id);
            
            if ($stmt->execute()) {
                $upload_success = true;
                $staff['img_URL'] = $avatar_path;
                $_SESSION['avatar_path'] = $avatar_path;
            } else {
                $errors['database'] = "Failed to update profile picture: " . $db->error;
            }
        } else {
            $errors['profileImage'] = "Failed to upload image";
        }
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Settings - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .main-content { flex: 1; }
        .info-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .account-details { display: flex; align-items: center; }
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-right: 20px;
            overflow: hidden;
        }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-info { flex: 1; }
        .user-info .row { margin-bottom: 10px; }
        .password-container { position: relative; }
        .password-toggle {
            position: absolute;
            right: 0;
            top: 0;
            cursor: pointer;
            background: none;
            border: none;
            color: #6c757d;
        }
        #sidebar {
            background-color: #343a40;
            min-height: 100vh;
            transition: transform 0.3s ease;
        }
        @media (max-width: 992px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                transform: translateX(-100%);
            }
            #sidebar.show {
                transform: translateX(0);
            }
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            color: #dc3545;
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
        }
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
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
            Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>
        </span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <?php
                    // Use img_URL from database if available, otherwise fall back to legacy path
                    $avatar_path = !empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
                    
                    if (file_exists($avatar_path)): ?>
                        <img src="<?php echo $avatar_path; ?>" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle mb-2 bg-secondary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <span class="text-white" style="font-size: 24px;">
                                <?php 
                                $name = $_SESSION['staff_name'];
                                echo strtoupper(substr($name, 0, 1)); 
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
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
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>

                    <li class="nav-item mt-3">
                        <a class="nav-link text-light active" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-cog me-2"></i>Staff Settings</h1>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Password changed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($upload_success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Profile picture updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (!empty($errors['database'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($errors['database']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Account Details</h2>
                        <a href="staff_profile_edit.php" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Edit Profile
                        </a>
                    </div>
                    <div class="info-card">
                        <div class="account-details">
                            <div class="user-avatar">
                                <?php
                                // Check for image in this order: 1. Database img_URL, 2. Legacy path, 3. Show initials
                                $avatar_path = !empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
                                if (file_exists($avatar_path)): ?>
                                    <img src="<?php echo $avatar_path; ?>" alt="Profile Image" id="avatarImage">
                                <?php else: ?>
                                    <?php 
                                    $name = $staff['Staff_name'];
                                    $initials = strtoupper(substr($name, 0, 1));
                                    if (strpos($name, ' ') !== false) {
                                        $name_parts = explode(' ', $name);
                                        $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
                                    }
                                    echo $initials;
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <div class="row">
                                    <div class="col-md-3 fw-bold">Name:</div>
                                    <div class="col-md-9"><?php echo htmlspecialchars($staff['Staff_name']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 fw-bold">Password:</div>
                                    <div class="col-md-9 password-container">
                                        <span id="passwordDisplay"><?php echo str_repeat('*', strlen($staff['Staff_password'])); ?></span>
                                        <button class="password-toggle" onclick="togglePassword()">
                                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 fw-bold">Email:</div>
                                    <div class="col-md-9"><?php echo htmlspecialchars($staff['Staff_Email'] ?? ''); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 fw-bold">Position:</div>
                                    <div class="col-md-9"><?php echo htmlspecialchars($staff['position']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-lock me-2"></i>Change Password
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                               id="currentPassword" name="current_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('currentPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['current_password']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                               id="newPassword" name="new_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['new_password']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                               id="confirmPassword" name="confirm_password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-image me-2"></i>Profile Picture
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="profileImage" class="form-label">Upload New Image</label>
                                    <input class="form-control <?php echo isset($errors['profileImage']) ? 'is-invalid' : ''; ?>" 
                                           type="file" id="profileImage" name="profileImage" accept="image/*">
                                    <?php if (isset($errors['profileImage'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['profileImage']); ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size: 2MB</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Picture</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 &&
            !document.getElementById('sidebar').contains(e.target) &&
            !document.getElementById('sidebarToggle').contains(e.target) &&
            document.getElementById('sidebar').classList.contains('show')) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Profile picture preview
    document.getElementById('profileImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarImage = document.getElementById('avatarImage');
                if (avatarImage) {
                    avatarImage.src = e.target.result;
                } else {
                    const avatarDiv = document.querySelector('.user-avatar');
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Profile Image';
                    img.id = 'avatarImage';
                    avatarDiv.innerHTML = '';
                    avatarDiv.appendChild(img);
                }
            };
            reader.readAsDataURL(file);
        }
    });
});

function togglePassword(inputId, button) {
    if (inputId && button) {
        // For password input fields
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    } else {
        // For password display
        const passwordDisplay = document.getElementById('passwordDisplay');
        const passwordToggleIcon = document.getElementById('passwordToggleIcon');
        if (passwordDisplay.textContent.includes('*')) {
            passwordDisplay.textContent = '<?php echo addslashes($staff['Staff_password']); ?>';
            passwordToggleIcon.classList.remove('bi-eye');
            passwordToggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordDisplay.textContent = '<?php echo str_repeat("*", strlen($staff['Staff_password'])); ?>';
            passwordToggleIcon.classList.remove('bi-eye-slash');
            passwordToggleIcon.classList.add('bi-eye');
        }
    }
}
</script>
</body>
</html>