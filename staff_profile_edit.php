<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: admin_login.php");
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

// Fetch staff details including img_URL and Staff_username
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, Staff_username, position, Staff_Email, Staff_password, img_URL FROM staff WHERE Staff_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

$shopSettings = [];
$settingsQuery = $db->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

if (!$staff) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $username = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate username
    if (empty($username)) {
        $errors['name'] = "Username is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $username)) {
        $errors['name'] = "Only letters and white space allowed";
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    // Check if password is being changed
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify current password (plain text comparison)
        if (empty($current_password)) {
            $errors['current_password'] = "Current password is required to change password";
        } elseif ($current_password !== $staff['Staff_password']) {
            $errors['current_password'] = "Current password is incorrect";
        }

        // Validate new password
        if (empty($new_password)) {
            $errors['new_password'] = "New password is required";
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = "Password must be at least 8 characters long";
        } elseif (!preg_match('/[A-Z]/', $new_password)) {
            $errors['new_password'] = "Password must contain at least one uppercase letter";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $errors['new_password'] = "Password must contain at least one number";
        } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $errors['new_password'] = "Password must contain at least one special character";
        }
        

        // Confirm new password
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match";
        }

        $password_changed = true;
    }

    // Handle profile picture upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['avatar']['tmp_name']);
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_type, $allowed_types)) {
            $errors['avatar'] = "Only JPG, PNG, and GIF files are allowed";
        } elseif ($_FILES['avatar']['size'] > $max_size) {
            $errors['avatar'] = "File size must be less than 2MB";
        } else {
            $upload_dir = 'staff_avatars/';

            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $errors['avatar'] = "Failed to create upload directory";
                }
            } elseif (!is_writable($upload_dir)) {
                $errors['avatar'] = "Upload directory is not writable";
            }

            if (empty($errors['avatar'])) {
                $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $avatar_filename = $staff_id . '_' . uniqid() . '.' . $file_extension;
                $avatar_path = $upload_dir . $avatar_filename;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                    // Update DB path (relative path)
                    $display_path = $avatar_path;
                    $update_img_query = "UPDATE staff SET img_URL = ? WHERE Staff_ID = ?";
                    $stmt = $db->prepare($update_img_query);
                    $stmt->bind_param("si", $display_path, $staff_id);

                    if ($stmt->execute()) {
                        // Delete old image only after DB update succeeds
                        if (!empty($staff['img_URL']) && file_exists($staff['img_URL'])) {
                            unlink($staff['img_URL']);
                        }

                        $upload_success = true;
                        $staff['img_URL'] = $display_path;
                        $_SESSION['avatar_path'] = $display_path;
                    } else {
                        $errors['database'] = "Failed to update profile picture: " . $db->error;
                        // Remove the new uploaded image if DB update failed
                        if (file_exists($avatar_path)) {
                            unlink($avatar_path);
                        }
                    }
                } else {
                    $errors['avatar'] = "Failed to upload image";
                }
            }
        }
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other file upload errors
        $upload_errors = [
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.'
        ];
        $error_code = $_FILES['avatar']['error'];
        $errors['avatar'] = "File upload error: " . ($upload_errors[$error_code] ?? 'Unknown error');
    }

    // Update database if no errors (excluding avatar errors if no file was uploaded)
    if (empty(array_diff_key($errors, ['avatar' => '']))) {
        $update_query = "UPDATE staff SET Staff_username = ?, Staff_Email = ?";
        $params = [$username, $email];
        $types = "ss";
        
        if ($password_changed) {
            $update_query .= ", Staff_password = ?";
            $params[] = $new_password;
            $types .= "s";
        }
        
        $update_query .= " WHERE Staff_ID = ?";
        $params[] = $staff_id;
        $types .= "i";
        
        $stmt = $db->prepare($update_query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success = true;
            // Update session variables
            $_SESSION['staff_name'] = $username;
            $_SESSION['staff_email'] = $email;
            // Refresh staff data
            $staff['Staff_username'] = $username;
            $staff['Staff_Email'] = $email;
            if ($password_changed) {
                $staff['Staff_password'] = $new_password;
            }
        } else {
            $errors['database'] = "Failed to update profile: " . $db->error;
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
    <title>Edit Profile - PetShop Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
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
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            margin-right: 20px;
            overflow: hidden;
            position: relative;
        }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-avatar-edit {
            position: absolute;
            bottom: 0;
            right: 0;
            background: rgba(0,0,0,0.5);
            color: white;
            width: 100%;
            text-align: center;
            padding: 5px;
            cursor: pointer;
        }
        .user-info { flex: 1; }
        .user-info .row { margin-bottom: 10px; }
        .password-container { position: relative; }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: #6c757d;
            z-index: 5;
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
        .form-control {
            padding-right: 35px; /* Make space for the eye icon */
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
        /* Animation for validation icons */
        .requirement i.bi-check-circle {
            animation: fadeInScale 0.3s ease;
        }
        @keyframes fadeInScale {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        .input-group-text i {
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .input-group-text:hover i {
            color: green;
        }
        button[type="submit"]:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        :root {
            --primary: #4e9f3d;
            --primary-light: #8fd14f;
            --primary-dark: #38761d;
            --secondary: #1e3a8a;
            --accent: #ff7e2e;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #f0f2f5;
        }
        .email-disabled-container {
            position: relative;
        }
        
        .email-disabled-container .block-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #dc3545;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            font-size: 1.2rem;
        }
        
        .email-disabled-container:hover .block-icon {
            opacity: 1;
        }
        
        .email-disabled-container input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
            padding-right: 40px; /* Make space for the icon */
        }
        
        /* Rest of your existing CSS remains the same */
        .main-content { flex: 1; }
        .info-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <img src="Hachi_Logo.png" alt="PetShop Staff" height="40">
        </a>
    </div>
    <div>
        <span class="text-light me-3">
            <i class="fas fa-user-circle me-1"></i>
            Welcome, <?php echo htmlspecialchars($staff['Staff_username'] ?? $_SESSION['staff_name']); ?>
        </span>
        <a href="logout.php?type=staff" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="d-flex flex-column align-items-center mb-4">
                    <?php
                    $avatar_path = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : 
                                  (!empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg");

                    if (file_exists($avatar_path)): ?>
                        <img src="<?php echo $avatar_path; ?>" id="avatarPreview" alt="Profile Image" class="rounded-circle mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle mb-2 bg-secondary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <span class="text-white" style="font-size: 24px;">
                            <?php 
                            $username = $staff['Staff_username'] ?? $_SESSION['staff_name'];
                            echo strtoupper(substr($username, 0, 1)); 
                            ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <h5 class="text-white mb-1"><?php echo htmlspecialchars($staff['Staff_username'] ?? $_SESSION['staff_name']); ?></h5>
                    <small class="text-muted text-center"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
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
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_orders.php?show_disabled=1">
                                        <i class="fas fa-ban me-2"></i>Disabled Orders
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
                <h1 class="h2"><i class="fas fa-user-edit me-2"></i>Edit Profile</h1>
                <div>
                    <a href="settings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Settings
                    </a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Profile updated successfully!
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
                <div class="col-md-8">
                    <form id="profileForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-user me-2"></i>Basic Information
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Username</label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo htmlspecialchars($staff['Staff_username'] ?? $staff['Staff_name']); ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Please provide your username.</div>
                                    <?php endif; ?>
                                </div>
                               <div class="mb-3 email-disabled-container">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" 
                                        id="email" name="email" 
                                        value="<?php echo htmlspecialchars($staff['Staff_Email']); ?>" 
                                        readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <input type="text" class="form-control" id="position" 
                                           value="<?php echo htmlspecialchars($staff['position']); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                               id="current_password" name="current_password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['current_password']); ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Leave blank if you don't want to change password</small>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                               id="new_password" name="new_password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                    <div class="password-requirements mt-2">
                                        <div class="requirement" id="length-check">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            <i class="bi bi-check-circle text-success d-none"></i>
                                            <span>At least 8 characters</span>
                                        </div>
                                        <div class="requirement" id="uppercase-check">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            <i class="bi bi-check-circle text-success d-none"></i>
                                            <span>At least 1 uppercase letter</span>
                                        </div>
                                        <div class="requirement" id="number-check">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            <i class="bi bi-check-circle text-success d-none"></i>
                                            <span>At least 1 number</span>
                                        </div>
                                        <div class="requirement" id="symbol-check">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            <i class="bi bi-check-circle text-success d-none"></i>
                                            <span>At least 1 special character</span>
                                        </div>
                                        <div class="requirement" id="space-check">
                                            <i class="bi bi-x-circle text-danger"></i>
                                            <i class="bi bi-check-circle text-success d-none"></i>
                                            <span>No spaces allowed</span>
                                        </div>
                                    </div>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['new_password']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="password-container">
                                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                               id="confirm_password" name="confirm_password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-image me-2"></i>Profile Picture
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Upload New Image</label>
                                    <input class="form-control <?php echo isset($errors['avatar']) ? 'is-invalid' : ''; ?>" 
                                           type="file" id="avatar" name="avatar" accept="image/*">
                                    <?php if (isset($errors['avatar'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['avatar']); ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size: 2MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-user-circle me-2"></i>Profile Preview
                        </div>
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <?php
                                // Check for image in this order: 1. Database img_URL, 2. Legacy path, 3. Show initials
                                $avatar_path = !empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
                                if (file_exists($avatar_path)): ?>
                                    <img src="<?php echo $avatar_path; ?>" id="avatarPreview" alt="Profile Image">
                                <?php else: ?>
                                    <div id="initialsContainer">
                                        <span id="avatarInitials">
                                            <?php 
                                            $name = $staff['Staff_username'] ?? $staff['Staff_name'];
                                            $initials = strtoupper(substr($name, 0, 1));
                                            if (strpos($name, ' ') !== false) {
                                                $name_parts = explode(' ', $name);
                                                $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
                                            }
                                            echo $initials;
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h4 id="namePreview"><?php echo htmlspecialchars($staff['Staff_username'] ?? $staff['Staff_name']); ?></h4>
                            <p class="text-muted mb-1" id="emailPreview"><?php echo htmlspecialchars($staff['Staff_Email']); ?></p>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($staff['position']); ?></span>
                        </div>
                    </div>
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

    // Password validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const numberCheck = document.getElementById('number-check');
    const symbolCheck = document.getElementById('symbol-check');
    const submitButton = document.getElementById('submitButton');

    let isLengthValid = false;
    let isUppercaseValid = false;
    let isNumberValid = false;
    let isSymbolValid = false;
    let isPasswordMatch = false;

    // Validation functions
    function checkPasswordLength(password) {
        const isValid = password.length >= 8;
        isLengthValid = isValid;
        return isValid;
    }

    function checkPasswordUppercase(password) {
        const isValid = /[A-Z]/.test(password);
        isUppercaseValid = isValid;
        return isValid;
    }

    function checkPasswordNumber(password) {
        const isValid = /[0-9]/.test(password);
        isNumberValid = isValid;
        return isValid;
    }

    function checkPasswordSymbol(password) {
        const isValid = /[^A-Za-z0-9]/.test(password);
        isSymbolValid = isValid;
        return isValid;
    }

    function checkPasswordMatch(password, confirmPassword) {
        const isValid = password === confirmPassword && password !== '';
        isPasswordMatch = isValid;
        return isValid;
    }

    function validateForm() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Only validate password if password fields are being changed
        if (currentPassword || newPassword || confirmPassword) {
            const isPasswordValid = isLengthValid && isUppercaseValid && isNumberValid && isSymbolValid && isPasswordMatch;
            submitButton.disabled = !isPasswordValid;
        } else {
            submitButton.disabled = false;
        }
    }

    // Toggle icon visibility
    function toggleIconVisibility(element, isValid) {
        const crossIcon = element.querySelector('.bi-x-circle');
        const checkIcon = element.querySelector('.bi-check-circle');
        
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
        validateForm();
    }

    // Validate password on input
   // Update the password validation code
newPasswordInput.addEventListener('input', function() {
    const password = newPasswordInput.value;
    
    // Check all requirements
    toggleIconVisibility(lengthCheck, checkPasswordLength(password));
    toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
    toggleIconVisibility(numberCheck, checkPasswordNumber(password));
    toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
    toggleIconVisibility(spaceCheck, !/\s/.test(password)); // Add this line
    
    checkPasswordMatch(password, confirmPasswordInput.value);
});

// Add this variable at the top with others
const spaceCheck = document.getElementById('space-check');

    // Validate confirm password on input
    confirmPasswordInput.addEventListener('input', function() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        checkPasswordMatch(password, confirmPassword);
        validateForm();
    });

    // Form validation
    const form = document.getElementById('profileForm');
    form.addEventListener('submit', function(event) {
        // Check password requirements if password is being changed
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (currentPassword || newPassword || confirmPassword) {
            if (!(isLengthValid && isUppercaseValid && isNumberValid && isSymbolValid && isPasswordMatch)) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
        }
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    // Live preview for name
    const nameInput = document.getElementById('name');
    const namePreview = document.getElementById('namePreview');
    nameInput.addEventListener('input', function() {
        namePreview.textContent = this.value;
        updateInitials(this.value);
    });

    // Live preview for email
    const emailInput = document.getElementById('email');
    const emailPreview = document.getElementById('emailPreview');
    emailInput.addEventListener('input', function() {
        emailPreview.textContent = this.value;
    });

    // Also validate when the page loads in case form was submitted with errors
    if (newPasswordInput.value) {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        toggleIconVisibility(lengthCheck, checkPasswordLength(password));
        toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
        toggleIconVisibility(numberCheck, checkPasswordNumber(password));
        toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
        checkPasswordMatch(password, confirmPassword);
    }

    // Initial validation
    validateForm();
    
    // Only show success message after upload
    <?php if ($upload_success): ?>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh the avatar preview after successful upload
        const avatarPath = '<?php echo $staff["img_URL"] ?? ""; ?>';
        if (avatarPath) {
            const avatarPreview = document.getElementById('avatarPreview');
            if (avatarPreview) {
                avatarPreview.src = avatarPath + '?t=' + new Date().getTime(); // Cache buster
            } else {
                const initialsContainer = document.getElementById('initialsContainer');
                if (initialsContainer) {
                    initialsContainer.remove();
                    const avatarContainer = document.querySelector('.user-avatar');
                    const newImg = document.createElement('img');
                    newImg.id = 'avatarPreview';
                    newImg.src = avatarPath;
                    newImg.alt = 'Profile Image';
                    avatarContainer.appendChild(newImg);
                }
            }
        }
    });
    <?php endif; ?>
});

function togglePassword(id, button) {
    const input = document.getElementById(id);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}

// Prevent space in password fields
function preventSpace(event) {
    if (event.key === ' ') {
        event.preventDefault();
        return false;
    }
}

// Add event listeners to all password fields
document.getElementById('current_password').addEventListener('keydown', preventSpace);
document.getElementById('new_password').addEventListener('keydown', preventSpace);
document.getElementById('confirm_password').addEventListener('keydown', preventSpace);

// Also prevent pasting of text with spaces
function preventPasteWithSpaces(event) {
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');
    if (/\s/.test(pastedText)) {
        event.preventDefault();
        alert('Password cannot contain spaces');
        return false;
    }
}

document.getElementById('new_password').addEventListener('paste', preventPasteWithSpaces);
document.getElementById('confirm_password').addEventListener('paste', preventPasteWithSpaces);

// Function to update initials display from username
function updateInitials(name) {
    const avatarInitials = document.getElementById('avatarInitials');
    if (avatarInitials) {
        let initials = name.length > 0 ? name.charAt(0).toUpperCase() : '';
        if (name.includes(' ')) {
            const parts = name.split(' ');
            initials = parts[0].charAt(0).toUpperCase();
            if (parts.length > 1) {
                initials += parts[parts.length - 1].charAt(0).toUpperCase();
            }
        }
        avatarInitials.textContent = initials;
    }
}
</script>
</body>
</html>