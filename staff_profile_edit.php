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

// Fetch staff details including img_URL and Staff_username
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, Staff_username, position, Staff_Email, Staff_password, img_URL FROM staff WHERE Staff_ID = ?";
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
        }

        // Confirm new password
        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match";
        }

        $password_changed = true;
    }

    // Handle profile picture upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['avatar'] = "Only JPG, PNG, and GIF files are allowed";
        } elseif ($_FILES['avatar']['size'] > $max_size) {
            $errors['avatar'] = "File size must be less than 2MB";
        } else {
            $upload_dir = 'staff_avatars/';
            
            // Create directory if it doesn't exist and make sure it's writable
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
                
                // Delete old image if it exists
                if (!empty($staff['img_URL']) && file_exists($staff['img_URL'])) {
                    unlink($staff['img_URL']);
                }
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                    // Update database with image path (relative path for display)
                    $display_path = 'staff_avatars/' . $avatar_filename;
                    $update_img_query = "UPDATE staff SET img_URL = ? WHERE Staff_ID = ?";
                    $stmt = $db->prepare($update_img_query);
                    $stmt->bind_param("si", $display_path, $staff_id);
                    
                    if ($stmt->execute()) {
                        $upload_success = true;
                        $staff['img_URL'] = $display_path;
                        $_SESSION['avatar_path'] = $display_path;
                    } else {
                        $errors['database'] = "Failed to update profile picture: " . $db->error;
                        // Remove the uploaded file if database update failed
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
        $upload_errors = [
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
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
    Welcome, <?php echo htmlspecialchars($staff['Staff_username'] ?? $_SESSION['staff_name']); ?>
</span>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="d-flex flex-column align-items-center mb-4">
                    <?php
                    // Check for avatar in this order: 1. Session avatar_path, 2. staff_avatars folder, 3. Default initials
// Replace the avatar display section with:
$avatar_path = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : 
              (!empty($staff['img_URL']) ? $staff['img_URL'] : "staff_avatars/" . $_SESSION['staff_id'] . ".jpg");

if (file_exists($avatar_path)): ?>
    <img src="<?php echo $avatar_path; ?>" id="avatarPreview" alt="Profile Image">
<?php else: ?>
    <!-- Initials display -->
<?php endif; ?>
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
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                           id="email" name="email" value="<?php echo htmlspecialchars($staff['Staff_Email']); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                    <?php else: ?>
                                        <div class="invalid-feedback">Please provide a valid email.</div>
                                    <?php endif; ?>
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
                                            <i class="bi bi-eye"></i>
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
                                            <i class="bi bi-eye"></i>
                                        </button>
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
                                            <i class="bi bi-eye"></i>
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
                            <button type="submit" class="btn btn-primary">
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
                            <div class="user-avatar-edit" onclick="document.getElementById('avatar').click()">
                                <i class="fas fa-camera me-1"></i> Change
                            </div>
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

    // Form validation
    const form = document.getElementById('profileForm');
    form.addEventListener('submit', function(event) {
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

    // Avatar preview
    const avatarInput = document.getElementById('avatar');
    const avatarContainer = document.querySelector('.user-avatar');
    
    avatarInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // First, check if there's already an image preview
                let avatarPreview = document.getElementById('avatarPreview');
                
                if (avatarPreview) {
                    // If there is an image already, just update its source
                    avatarPreview.src = e.target.result;
                } else {
                    // If there isn't an image (showing initials instead), create one
                    const initialsContainer = document.getElementById('initialsContainer');
                    if (initialsContainer) {
                        // Remove the initials container
                        initialsContainer.remove();
                    }
                    
                    // Create new image element
                    avatarPreview = document.createElement('img');
                    avatarPreview.id = 'avatarPreview';
                    avatarPreview.src = e.target.result;
                    avatarPreview.alt = 'Profile Image';
                    
                    // Add it to the avatar container (before the edit button)
                    const editButton = avatarContainer.querySelector('.user-avatar-edit');
                    avatarContainer.insertBefore(avatarPreview, editButton);
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

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