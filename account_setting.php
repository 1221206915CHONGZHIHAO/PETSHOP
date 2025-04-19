<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "petshop";

// Create database connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Failed to connect to database: " . $conn->connect_error);
}

// Get current user ID from session
$customer_id = $_SESSION['customer_id'];

// Get current user information
$sql = "SELECT Customer_id, Customer_name, Customer_email FROM customer WHERE Customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// Redirect to logout if user not found
if ($result->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

// Store user info in array
$customer = $result->fetch_assoc();
$messages = [];

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Profile information update
    if (isset($_POST['update_profile'])) {
        $new_name = trim($_POST['Customer_name']);
        $new_email = trim($_POST['Customer_email']);
        
        // Validate inputs
        if (empty($new_name)) {
            $messages['error'][] = "Username cannot be empty";
        }
        
        if (empty($new_email)) {
            $messages['error'][] = "Email cannot be empty";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $messages['error'][] = "Please enter a valid email address";
        }
        
        // Check if email already exists (except for current user)
        $check_email = "SELECT Customer_id FROM customer WHERE Customer_email = ? AND Customer_id != ?";
        $stmt_check = $conn->prepare($check_email);
        $stmt_check->bind_param("si", $new_email, $customer_id);
        $stmt_check->execute();
        $email_result = $stmt_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $messages['error'][] = "This email is already in use by another account";
        }
        
        // Update if no errors
        if (!isset($messages['error'])) {
            $update_sql = "UPDATE customer SET Customer_name = ?, Customer_email = ? WHERE Customer_id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("ssi", $new_name, $new_email, $customer_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['customer_name'] = $new_name;
                $messages['success'][] = "Profile information updated successfully!";
                
                // Refresh user data
                $stmt->execute();
                $result = $stmt->get_result();
                $customer = $result->fetch_assoc();
            } else {
                $messages['error'][] = "Failed to update profile. Please try again.";
            }
        }
    }
    
    // Password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate current password
        $password_sql = "SELECT Customer_password FROM customer WHERE Customer_id = ?";
        $stmt_pass = $conn->prepare($password_sql);
        $stmt_pass->bind_param("i", $customer_id);
        $stmt_pass->execute();
        $pass_result = $stmt_pass->get_result();
        $user_data = $pass_result->fetch_assoc();
        
        if ($user_data['Customer_password'] !== $current_password) {
            $messages['password_error'][] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($new_password) < 6) {
            $messages['password_error'][] = "New password must be at least 6 characters long";
        }
        
        if ($new_password !== $confirm_password) {
            $messages['password_error'][] = "New passwords do not match";
        }
        
        // Update password if no errors
        if (!isset($messages['password_error'])) {
            $update_pass_sql = "UPDATE customer SET Customer_password = ? WHERE Customer_id = ?";
            $stmt_update_pass = $conn->prepare($update_pass_sql);
            $stmt_update_pass->bind_param("si", $new_password, $customer_id);
            
            if ($stmt_update_pass->execute()) {
                $messages['password_success'][] = "Password changed successfully!";
            } else {
                $messages['password_error'][] = "Failed to update password. Please try again.";
            }
        }
    }
}

$conn->close();

// Get first letter of username for avatar placeholder
$first_letter = strtoupper(substr($customer['Customer_name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Settings</title>
    <link rel="stylesheet" href="account_setting.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    tab.classList.add('active');
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Password visibility toggle
            const togglePasswordBtns = document.querySelectorAll('.toggle-password');
            togglePasswordBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const passwordField = this.previousElementSibling;
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Change the icon based on password visibility
                    if (type === 'password') {
                        this.innerHTML = '<i class="fa-regular fa-eye"></i>';
                    } else {
                        this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                    }
                });
            });
        });
    </script>
</head>
<body>

<div class="account-container">
    <!-- Header with gradient background -->
    <div class="account-header">
        <h1>My Account</h1>
        <div class="header-subtitle">Manage your profile and preferences</div>
    </div>
    
    <div class="account-content">
        <div class="tab-navigation">
            <button class="tab active" data-tab="profile-tab">
                <i class="fas fa-user"></i> Profile
            </button>
            <button class="tab" data-tab="security-tab">
                <i class="fas fa-lock"></i> Security
            </button>
            <button class="tab" data-tab="preferences-tab">
                <i class="fas fa-sliders-h"></i> Preferences
            </button>
        </div>
        
        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content active">
            <h2 class="section-title">Your Profile</h2>
            
            <?php if (isset($messages['success'])): ?>
                <?php foreach ($messages['success'] as $success): ?>
                    <div class="alert success">
                        <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                        <?php echo $success; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (isset($messages['error'])): ?>
                <?php foreach ($messages['error'] as $error): ?>
                    <div class="alert error">
                        <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                        <?php echo $error; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Avatar section -->
            <div class="setting-card">
                <div class="avatar-section">
                    <div class="avatar-container">
                        <div class="avatar-placeholder"><?php echo $first_letter; ?></div>
                    </div>
                    <div class="avatar-actions">
                        <p>Personalize your account with a profile picture</p>
                        <div class="avatar-buttons">
                            <button class="btn btn-secondary avatar-btn">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            <button class="btn btn-secondary avatar-btn">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile information form -->
            <div class="setting-card">
                <form method="post" action="account_setting.php" class="settings-form">
                    <div class="form-group">
                        <label for="Customer_name">Username</label>
                        <input 
                            type="text" 
                            id="Customer_name" 
                            name="Customer_name" 
                            class="form-control"
                            value="<?php echo htmlspecialchars($customer['Customer_name']); ?>" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="Customer_email">Email Address</label>
                        <input 
                            type="email" 
                            id="Customer_email" 
                            name="Customer_email" 
                            class="form-control"
                            value="<?php echo htmlspecialchars($customer['Customer_email']); ?>" 
                            required
                        >
                        <span class="form-hint">We'll never share your email with anyone else.</span>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Tab -->
        <div id="security-tab" class="tab-content">
            <h2 class="section-title">Security Settings</h2>
            
            <?php if (isset($messages['password_success'])): ?>
                <?php foreach ($messages['password_success'] as $success): ?>
                    <div class="alert success">
                        <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                        <?php echo $success; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (isset($messages['password_error'])): ?>
                <?php foreach ($messages['password_error'] as $error): ?>
                    <div class="alert error">
                        <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                        <?php echo $error; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Password change form -->
            <div class="setting-card">
                <h3><i class="fas fa-key"></i> Change Password</h3>
                <p class="form-hint" style="margin-bottom: 20px;">Strong passwords help protect your account</p>
                
                <form method="post" action="account_setting.php" class="settings-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="form-control"
                                required
                            >
                            <span class="toggle-password"><i class="fa-regular fa-eye"></i></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="form-control"
                                required
                            >
                            <span class="toggle-password"><i class="fa-regular fa-eye"></i></span>
                        </div>
                        <span class="form-hint">Must be at least 6 characters long</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control"
                                required
                            >
                            <span class="toggle-password"><i class="fa-regular fa-eye"></i></span>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-check"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Account security settings -->
            <div class="setting-card">
                <h3><i class="fas fa-shield-alt"></i> Login Security</h3>
                
                <div class="switch-container">
                    <span class="switch-label">Enable Two-Factor Authentication</span>
                    <label class="switch">
                        <input type="checkbox" id="2fa-toggle">
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="switch-container">
                    <span class="switch-label">Email notifications for login attempts</span>
                    <label class="switch">
                        <input type="checkbox" id="login-notify" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Preferences Tab -->
        <div id="preferences-tab" class="tab-content">
            <h2 class="section-title">Your Preferences</h2>
            
            <div class="setting-card">
                <h3><i class="fas fa-bell"></i> Notifications</h3>
                
                <div class="switch-container">
                    <span class="switch-label">Email Notifications</span>
                    <label class="switch">
                        <input type="checkbox" id="email-notify" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="switch-container">
                    <span class="switch-label">Order Updates</span>
                    <label class="switch">
                        <input type="checkbox" id="order-updates" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <div class="switch-container">
                    <span class="switch-label">Special Offers and Promotions</span>
                    <label class="switch">
                        <input type="checkbox" id="promo-updates">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="setting-card">
                <h3><i class="fas fa-palette"></i> Appearance</h3>
                
                <div class="switch-container">
                    <span class="switch-label">Dark Mode</span>
                    <label class="switch">
                        <input type="checkbox" id="dark-mode">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="navigation-links">
        <a href="userhomepage.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

</body>
</html>