<?php
session_start();
error_reporting(E_ALL);
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Check if customer is logged in and account is active
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $check_active = $conn->prepare("SELECT is_active FROM customer WHERE customer_id = ?");
    $check_active->bind_param("i", $customer_id);
    $check_active->execute();
    $check_active->bind_result($is_active);
    $check_active->fetch();
    $check_active->close();
    
    if ($is_active != 1) {
        // Account is deactivated, clear session and redirect
        session_unset();
        session_destroy();
        header("Location: login.php?error=account_deactivated");
        exit();
    }
}

// Fetch customer data
$stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_ID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Fetch customer addresses
$stmt = $conn->prepare("SELECT * FROM customer_address WHERE Customer_ID = ? ORDER BY Is_Default DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$addresses = $stmt->get_result();

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update profile information
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        
        // Handle profile image upload
        $profile_image = $customer['profile_image']; // Keep existing image by default
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
            $upload_dir = 'uploads/profile_images/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $customer_id . '_' . time() . '.' . $file_extension;
            $upload_file = $upload_dir . $new_filename;
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $error_message = "Only JPEG, PNG and GIF images are allowed.";
            } 
            else if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                $error_message = "Image size should not exceed 2MB.";
            }
            else if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_file)) {
                if (!empty($customer['profile_image']) && file_exists($upload_dir . $customer['profile_image'])) {
                    unlink($upload_dir . $customer['profile_image']);
                }
                $profile_image = $new_filename;
            } else {
                $error_message = "Failed to upload image. Please try again.";
            }
        }
        
        if (!isset($error_message)) {
            $stmt = $conn->prepare("UPDATE customer SET Customer_name = ?, profile_image = ? WHERE Customer_ID = ?");
            $stmt->bind_param("ssi", $username, $profile_image, $customer_id);
            
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
                $stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_ID = ?");
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $customer = $result->fetch_assoc();
                $_SESSION['customer_name'] = $customer['Customer_name'];
            } else {
                $error_message = "Failed to update profile: " . $conn->error;
            }
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($current_password != $customer['Customer_password']) {
            $password_error = "Current password is incorrect";
        } elseif ($new_password != $confirm_password) {
            $password_error = "New passwords do not match";
        } 
        elseif (strlen($new_password) < 8) {
            $password_error = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[A-Z]/', $new_password)) {
            $password_error = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $password_error = "Password must contain at least one number.";
        } elseif (preg_match('/\s/', $new_password)) { 
            $password_error = "Password cannot contain spaces.";
        } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $password_error = "Password must contain at least one special character.";
        } else {
            $stmt = $conn->prepare("UPDATE customer SET Customer_password = ? WHERE Customer_ID = ?");
            $stmt->bind_param("si", $new_password, $customer_id);
            
            if ($stmt->execute()) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Failed to change password: " . $conn->error;
            }
        }
    }
    
    // Add or update address
    if (isset($_POST['save_address'])) {
        $address_id = $_POST['address_id'] ?? null;
        $label = $_POST['address_label'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $line1 = $_POST['address_line1'];
        $line2 = $_POST['address_line2'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $postal = $_POST['postal_code'];
        $country = $_POST['country'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if ($is_default) {
            $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 0 WHERE Customer_ID = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        }
        
        if ($address_id) {
            $stmt = $conn->prepare("UPDATE customer_address SET 
                Address_Label = ?, Full_Name = ?, Phone_Number = ?, Address_Line1 = ?, Address_Line2 = ?, 
                City = ?, State = ?, Postal_Code = ?, Country = ?, Is_Default = ? 
                WHERE Address_ID = ? AND Customer_ID = ?");
            $stmt->bind_param("sssssssssiis", $label, $full_name, $phone, $line1, $line2, $city, $state, $postal, $country, $is_default, $address_id, $customer_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO customer_address 
                (Customer_ID, Address_Label, Full_Name, Phone_Number, Address_Line1, Address_Line2, City, State, Postal_Code, Country, Is_Default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssssi", $customer_id, $label, $full_name, $phone, $line1, $line2, $city, $state, $postal, $country, $is_default);
        }
        
        if ($stmt->execute()) {
            $address_success = "Address saved successfully!";
            $stmt = $conn->prepare("SELECT * FROM customer_address WHERE Customer_ID = ? ORDER BY Is_Default DESC");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $addresses = $stmt->get_result();
        } else {
            $address_error = "Failed to save address: " . $conn->error;
        }
    }
    
    // Delete address
    if (isset($_POST['delete_address'])) {
        $address_id = $_POST['address_id'];
        
        $stmt = $conn->prepare("DELETE FROM customer_address WHERE Address_ID = ? AND Customer_ID = ?");
        $stmt->bind_param("ii", $address_id, $customer_id);
        
        if ($stmt->execute()) {
            $address_success = "Address deleted successfully!";
            $stmt = $conn->prepare("SELECT * FROM customer_address WHERE Customer_ID = ? ORDER BY Is_Default DESC");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $addresses = $stmt->get_result();
        } else {
            $address_error = "Failed to delete address: " . $conn->error;
        }
    }
    
    // Set address as default
    if (isset($_POST['set_default'])) {
        $address_id = $_POST['address_id'];
        
        $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 0 WHERE Customer_ID = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 1 WHERE Address_ID = ? AND Customer_ID = ?");
        $stmt->bind_param("ii", $address_id, $customer_id);
        
        if ($stmt->execute()) {
            $address_success = "Default address updated successfully!";
            $stmt = $conn->prepare("SELECT * FROM customer_address WHERE Customer_ID = ? ORDER BY Is_Default DESC");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $addresses = $stmt->get_result();
        } else {
            $address_error = "Failed to update default address: " . $conn->error;
        }
    }
}

// Create masked password for display
$actual_password = $customer['Customer_password'];
$masked_password = str_repeat('*', strlen($actual_password));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile/Address - Hachi Pet Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    .dashboard-container {
      display: flex;
      padding: 20px;
      min-height: calc(100vh - 76px - 91px);
    }
    .sidebar {
      width: 250px;
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      margin-right: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .sidebar-nav { 
      list-style: none; 
      padding: 0; 
    }
    .sidebar-nav li { 
      margin-bottom: 10px; 
    }
    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 10px;
      color: #333;
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s ease;
    }
    .sidebar-nav a:hover { 
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    .sidebar-nav a.active { 
      background-color: var(--primary); 
      color: white;
    }
    .sidebar-nav a i { 
      margin-right: 10px; 
      width: 20px; 
      text-align: center; 
    }
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
    .nav-tabs .nav-link {
      color: #495057;
      border-radius: 0;
      border: none;
      padding: 1rem 1.5rem;
    }
    .nav-tabs .nav-link.active {
      color: var(--primary);
      background-color: transparent;
      border-bottom: 3px solid var(--primary);
    }
    .address-card {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      position: relative;
    }
    .address-card .badge {
      background-color: var(--primary);
      position: absolute;
      top: 10px;
      right: 10px;
    }
    .address-card h3 { margin-bottom: 15px; padding-right: 70px; }
    .address-actions { margin-top: 15px; }
    .add-address-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px dashed #dee2e6;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .add-address-btn:hover { background-color: #f8f9fa; }
    .modal {
      display: none;
      position: fixed;
      z-index: 1050;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 10px;
      max-width: 600px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 15px;
      margin-bottom: 15px;
    }
    .close-button {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
    }
    .form-group { margin-bottom: 15px; }
    .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
    .form-row .form-group { flex: 1; margin-bottom: 0; }
    .btn {
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-secondary { background-color: #6c757d; }
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
    
    .dashboard-menu .active {
      background-color: var(--primary) !important;
      color: white !important;
    }
    .dashboard-menu a:hover {
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    .logout-link {
      color: #dc3545 !important;
    }
    .logout-link:hover {
      background-color: rgba(220, 53, 69, 0.1) !important;
      color: #dc3545 !important;
    }
    
    .user-dropdown .active-dropdown-item {
      background-color: var(--primary) !important;
      color: white !important;
    }
    
    .user-dropdown .dropdown-item:hover {
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    
    .dropdown-item.active, .dropdown-item:active {
      background-color: var(--primary);
      color: white;
    }
    
    .password-requirements {
      font-size: 13px;
      color: var(--gray);
      background-color: rgba(240, 242, 245, 0.8);
      padding: 10px 15px;
      border-radius: 8px;
      margin-top: 8px;
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
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <div class="container">
      <a class="navbar-brand" href="userhomepage.php">
        <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'userhomepage.php' ? 'active' : ''; ?>" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about_us.php' ? 'active' : ''; ?>" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_us.php' ? 'active' : ''; ?>" href="contact_us.php">Contact Us</a></li>
        </ul>

        <ul class="navbar-nav ms-auto nav-icons">
          <li class="nav-item dropdown">
            <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-search"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end search-dropdown" aria-labelledby="searchDropdown">
              <form class="d-flex search-form" action="products.php" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search" required>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
              </form>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link position-relative" href="cart.php">
              <i class="bi bi-cart"></i>
              <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                  <?php echo htmlspecialchars($_SESSION['cart_count']); ?>
                </span>
              <?php endif; ?>
            </a>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center <?php echo in_array(basename($_SERVER['PHP_SELF']), ['user_dashboard.php', 'my_orders.php', 'favorites.php', 'myprofile_address.php']) ? 'active' : ''; ?>" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
              <?php else: ?>
                <i class="bi bi-person"></i>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active-dropdown-item' : ''; ?>" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'my_orders.php' ? 'active-dropdown-item' : ''; ?>" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active-dropdown-item' : ''; ?>" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'myprofile_address.php' ? 'active-dropdown-item' : ''; ?>" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="logout.php?type=customer">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
              <?php else: ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active-dropdown-item' : ''; ?>" href="login.php">Login</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active-dropdown-item' : ''; ?>" href="register.php">Register</a></li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<div class="dashboard-container container">
  <div class="sidebar">
    <ul class="sidebar-nav dashboard-menu">
      <li><a href="user_dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
      <li><a href="my_orders.php"><i class="bi bi-box"></i> My Orders</a></li>
      <li><a href="favorites.php"><i class="bi bi-heart"></i> My Favourite</a></li>
      <li><a href="myprofile_address.php" class="active"><i class="bi bi-person-lines-fill"></i> My Profile/Address</a></li>
      <li><a href="logout.php?type=customer" class="logout-link dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>
  
  <div class="main-content">
    <?php if (isset($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($password_success)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $password_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($password_error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $password_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($address_success)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $address_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($address_error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $address_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
          <i class="bi bi-person me-2"></i>Profile Information
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab" aria-controls="address" aria-selected="false">
          <i class="bi bi-geo-alt me-2"></i>Delivery Addresses
        </button>
      </li>
    </ul>
    
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Profile Information</h2>
          <button type="button" class="btn btn-primary" id="edit-profile-btn">
            <i class="bi bi-pencil me-2"></i>Edit Profile
          </button>
        </div>
        
        <div class="info-card">
          <div class="account-details">
            <div class="user-avatar">
              <?php if (!empty($customer['profile_image']) && file_exists('uploads/profile_images/' . $customer['profile_image'])): ?>
                <img src="uploads/profile_images/<?php echo htmlspecialchars($customer['profile_image']); ?>" alt="Profile Image">
              <?php else: ?>
                <?php 
                $name = $customer['Customer_name'];
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
                  <div class="col-md-9"><?php echo htmlspecialchars($customer['Customer_name']); ?></div>
                </div>
                <div class="row">
                  <div class="col-md-3 fw-bold">Password:</div>
                  <div class="col-md-9 password-container">
                    <span id="passwordDisplay"><?php echo $masked_password; ?></span>
                    <button type="button" class="password-toggle" id="changePasswordBtn">
                      <i class="bi bi-pencil"></i> Change Password
                    </button>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3 fw-bold">Email:</div>
                  <div class="col-md-9"><?php echo htmlspecialchars($customer['Customer_email']); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      
      <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Delivery Addresses</h2>
        </div>
        
        <div class="row">
          <?php if ($addresses->num_rows > 0): ?>
            <?php while ($address = $addresses->fetch_assoc()): ?>
              <div class="col-md-6 mb-4">
                <div class="info-card address-card">
                  <?php if ($address['Is_Default']): ?>
                    <span class="badge bg-primary">Default</span>
                  <?php endif; ?>
                  <h4><?php echo htmlspecialchars($address['Address_Label']); ?></h4>
                  <div>
                    <p class="mb-1"><?php echo htmlspecialchars($address['Full_Name']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($address['Address_Line1']); ?></p>
                    <?php if (!empty($address['Address_Line2'])): ?>
                      <p class="mb-1"><?php echo htmlspecialchars($address['Address_Line2']); ?></p>
                    <?php endif; ?>
                    <p class="mb-1">
                      <?php echo htmlspecialchars($address['City']); ?>,
                      <?php if (!empty($address['State'])): ?>
                        <?php echo htmlspecialchars($address['State']); ?>,
                      <?php endif; ?>
                      <?php echo htmlspecialchars($address['Postal_Code']); ?>
                    </p>
                    <p class="mb-1"><?php echo htmlspecialchars($address['Country']); ?></p>
                    <p class="mb-1">Phone: <?php echo htmlspecialchars($address['Phone_Number']); ?></p>
                  </div>
                  <div class="mt-3">
                    <button type="button" class="btn btn-primary btn-sm edit-address-btn" data-id="<?php echo $address['Address_ID']; ?>">
                      <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <form class="d-inline" method="post">
                      <input type="hidden" name="address_id" value="<?php echo $address['Address_ID']; ?>">
                      <button type="submit" name="delete_address" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this address?')">
                        <i class="bi bi-trash me-1"></i> Delete
                      </button>
                    </form>
                    <?php if (!$address['Is_Default']): ?>
                      <form class="d-inline" method="post">
                        <input type="hidden" name="address_id" value="<?php echo $address['Address_ID']; ?>">
                        <button type="submit" name="set_default" class="btn btn-secondary btn-sm">
                          <i class="bi bi-check-circle me-1"></i> Set as Default
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
          
          <div class="col-md-6 mb-4">
            <div class="add-address-btn" id="add-address-btn">
              <i class="bi bi-plus-lg me-2" style="font-size: 1.5rem;"></i>
              <span>Add New Address</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer style="background: linear-gradient(to bottom,rgb(134, 138, 135),rgba(46, 21, 1, 0.69));">
    <div class="container">
      <div class="row">
        <div class="col-md-5 mb-4 mb-lg-0">
          <div class="footer-about">
            <div class="footer-logo">
              <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
            </div>
            <p>Your trusted partner in pet product. We're dedicated to providing quality products for pet lovers everywhere.</p>
            <div class="social-links">
              <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="bi bi-facebook"></i></a>
              <a href="https://www.instagram.com/smal.l7018/"><i class="bi bi-instagram"></i></a>
            </div>
          </div>
        </div>
        
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
            
      
      <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px;">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" class="back-to-top" id="backToTop" style="background: linear-gradient(145deg, var(--primary), var(--primary-dark));">
    <i class="bi bi-arrow-up"></i>
  </a>

<div id="edit-profile-modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Edit Profile Information</h4>
        <button type="button" class="close-button">&times;</button>
      </div>
      <form id="edit-profile-form" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="profile_image" class="form-label">Profile Image (JPEG, PNG, GIF, max 2MB)</label>
          <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
          <div id="image-preview" class="mt-2"></div>
        </div>
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($customer['Customer_name']); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['Customer_email']); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
          <small class="text-muted">Email cannot be changed</small>
        </div>
        <div class="text-end">
          <button type="button" class="btn btn-secondary me-2 close-modal-btn">Cancel</button>
          <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

<div id="change-password-modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h4>Change Password</h4>
      <button type="button" class="close-button">&times;</button>
    </div>
    <form method="post">
      <div class="form-group">
        <label for="current_password" class="form-label">Current Password</label>
        <input type="password" class="form-control" id="current_password" name="current_password" required>
      </div>
      <div class="form-group">
        <label for="new_password" class="form-label">New Password</label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
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
        </div>
      </div>
      <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        <div id="password-match-indicator" class="mt-2" style="display: none;">
          <div class="requirement" id="match-check">
            <i class="bi bi-x-circle text-danger"></i>
            <i class="bi bi-check-circle text-success d-none"></i>
            <span>Passwords match</span>
          </div>
        </div>
      </div>
      <div class="text-end">
        <button type="button" class="btn btn-secondary me-2 close-modal-btn">Cancel</button>
        <button type="submit" name="change_password" class="btn btn-primary" id="change-password-submit" disabled>Change Password</button>
      </div>
    </form>
  </div>
</div>

<div id="address-modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h4 id="address-modal-title">Add New Address</h4>
      <button type="button" class="close-button">&times;</button>
    </div>
    <form method="post">
      <input type="hidden" id="address_id" name="address_id">
      <div class="form-group">
        <label for="address_label" class="form-label">Address Label (e.g., Home, Work)</label>
        <input type="text" class="form-control" id="address_label" name="address_label" required>
      </div>
      <div class="form-group">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" required>
      </div>
      <div class="form-group">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" required>
      </div>
      <div class="form-group">
        <label for="address_line1" class="form-label">Address Line 1</label>
        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
      </div>
      <div class="form-group">
        <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
        <input type="text" class="form-control" id="address_line2" name="address_line2">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="city" class="form-label">City</label>
          <input type="text" class="form-control" id="city" name="city" required>
        </div>
        <div class="form-group">
          <label for="state" class="form-label">State/Province (Optional)</label>
          <input type="text" class="form-control" id="state" name="state">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="postal_code" class="form-label">Postal/ZIP Code</label>
          <input type="text" class="form-control" id="postal_code" name="postal_code" required>
        </div>
        <div class="form-group">
          <label for="country" class="form-label">Country</label>
          <input type="text" class="form-control" id="country" name="country" required>
        </div>
      </div>
      <div class="form-group">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
          <label class="form-check-label" for="is_default">Set as default address</label>
        </div>
      </div>
      <div class="text-end">
        <button type="button" class="btn btn-secondary me-2 close-modal-btn">Cancel</button>
        <button type="submit" name="save_address" class="btn btn-primary">Save Address</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize AOS Animation
  AOS.init({
    once: true,
    duration: 800,
    offset: 100
  });

  // Navbar Scroll Effect
  const navbar = document.querySelector('.custom-nav');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar.classList.add('navbar-scrolled');
    } else {
      navbar.classList.remove('navbar-scrolled');
    }
  });
  
  // Back to Top Button
  const backToTopButton = document.getElementById('backToTop');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
      backToTopButton.classList.add('active');
    } else {
      backToTopButton.classList.remove('active');
    }
  });

  // Modal Elements
  const modals = document.querySelectorAll('.modal');
  const closeButtons = document.querySelectorAll('.close-button, .close-modal-btn');
  const editProfileBtn = document.getElementById('edit-profile-btn');
  const editProfileModal = document.getElementById('edit-profile-modal');
  const changePasswordBtn = document.getElementById('changePasswordBtn');
  const changePasswordModal = document.getElementById('change-password-modal');
  const addAddressBtn = document.getElementById('add-address-btn');
  const addressModal = document.getElementById('address-modal');
  const addressModalTitle = document.getElementById('address-modal-title');
  const profileImageInput = document.getElementById('profile_image');
  const imagePreview = document.getElementById('image-preview');

  // Password validation elements
  const currentPasswordInput = document.getElementById('current_password'); // UPDATED: Get current password input
  const newPasswordInput = document.getElementById('new_password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const lengthCheck = document.getElementById('length-check');
  const uppercaseCheck = document.getElementById('uppercase-check');
  const numberCheck = document.getElementById('number-check');
  const symbolCheck = document.getElementById('symbol-check');
  const matchCheck = document.getElementById('match-check');
  const passwordMatchIndicator = document.getElementById('password-match-indicator');
  const changePasswordSubmit = document.getElementById('change-password-submit');

  // Function to reset password form on open
  function resetPasswordForm() {
    const passwordForm = changePasswordModal.querySelector('form');
    if (passwordForm) {
      passwordForm.reset();
    }
    validatePassword();
  }

  // Event listeners to open modals
  editProfileBtn.addEventListener('click', function() {
    editProfileModal.style.display = 'block';
  });
  
  changePasswordBtn.addEventListener('click', function() {
    resetPasswordForm();
    changePasswordModal.style.display = 'block';
  });
  
  addAddressBtn.addEventListener('click', function() {
    document.getElementById('address_id').value = '';
    document.getElementById('address_label').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('address_line1').value = '';
    document.getElementById('address_line2').value = '';
    document.getElementById('city').value = '';
    document.getElementById('state').value = '';
    document.getElementById('postal_code').value = '';
    document.getElementById('country').value = '';
    document.getElementById('is_default').checked = false;
    addressModalTitle.textContent = 'Add New Address';
    addressModal.style.display = 'block';
  });

  // Profile image preview
  if (profileImageInput) {
    profileImageInput.addEventListener('change', function() {
      imagePreview.innerHTML = '';
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.maxWidth = '100px';
          img.style.borderRadius = '50%';
          imagePreview.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // --- Password Validation Logic ---
  function checkPasswordLength(password) { return password.length >= 8; }
  function checkPasswordUppercase(password) { return /[A-Z]/.test(password); }
  function checkPasswordNumber(password) { return /[0-9]/.test(password); }
  function checkPasswordSymbol(password) { return /[^A-Za-z0-9]/.test(password); }
  function checkPasswordMatch(password, confirmPassword) { return password === confirmPassword && password.length > 0; }
  
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
  }
  
  function validatePassword() {
    const password = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    const lengthValid = checkPasswordLength(password);
    const uppercaseValid = checkPasswordUppercase(password);
    const numberValid = checkPasswordNumber(password);
    const symbolValid = checkPasswordSymbol(password);
    const matchValid = checkPasswordMatch(password, confirmPassword);
    
    toggleIconVisibility(lengthCheck, lengthValid);
    toggleIconVisibility(uppercaseCheck, uppercaseValid);
    toggleIconVisibility(numberCheck, numberValid);
    toggleIconVisibility(symbolCheck, symbolValid);
    
    if (confirmPassword.length > 0) {
      passwordMatchIndicator.style.display = 'block';
      toggleIconVisibility(matchCheck, matchValid);
    } else {
      passwordMatchIndicator.style.display = 'none';
    }
    
    const allValid = lengthValid && uppercaseValid && numberValid && symbolValid && matchValid;
    changePasswordSubmit.disabled = !allValid;
    
    return allValid;
  }
  
  // UPDATED: Event listeners for ALL password fields to filter spaces
  if (currentPasswordInput) {
    currentPasswordInput.addEventListener('input', function() {
      this.value = this.value.replace(/\s/g, '');
    });
  }

  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
      this.value = this.value.replace(/\s/g, '');
      validatePassword();
    });
  }
  
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
      this.value = this.value.replace(/\s/g, '');
      validatePassword();
    });
  }

  // --- Modal Closing Logic ---
  closeButtons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      modals.forEach(function(modal) {
        modal.style.display = 'none';
      });
    });
  });
  
  window.addEventListener('click', function(event) {
    modals.forEach(function(modal) {
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });
  });
  
  // --- Edit Address Logic ---
  const editAddressBtns = document.querySelectorAll('.edit-address-btn');
  editAddressBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      const addressId = this.getAttribute('data-id');
      const addressCard = this.closest('.address-card');
      if (!addressCard) return;
      
      const label = addressCard.querySelector('h4').textContent;
      const paragraphs = addressCard.querySelectorAll('p');
      
      let fullName = '', addressLine1 = '', addressLine2 = '', city = '', 
          state = '', postalCode = '', country = '', phone = '';
      
      if (paragraphs.length > 0) fullName = paragraphs[0].textContent.trim();
      if (paragraphs.length > 1) addressLine1 = paragraphs[1].textContent.trim();
      
      let p_index = 2;
      if (paragraphs.length > 2 && !paragraphs[p_index].textContent.includes(',')) {
        addressLine2 = paragraphs[p_index].textContent.trim();
        p_index++;
      }
      
      if (paragraphs.length > p_index) {
        const cityLine = paragraphs[p_index].textContent.trim();
        const parts = cityLine.split(',').map(part => part.trim());

        city = parts[0] || '';

        if (parts.length === 3) {
            state = parts[1];
            postalCode = parts[2];
        } else if (parts.length === 2) {
            state = '';
            postalCode = parts[1];
        } else {
            state = '';
            postalCode = '';
        }
        p_index++;
      }

      if (paragraphs.length > p_index) {
        country = paragraphs[p_index].textContent.trim();
        p_index++;
      }
      
      if (paragraphs.length > p_index && paragraphs[p_index].textContent.includes('Phone:')) {
        phone = paragraphs[p_index].textContent.replace('Phone:', '').trim();
      }
      
      const isDefault = addressCard.querySelector('.badge') !== null;
      
      document.getElementById('address_id').value = addressId;
      document.getElementById('address_label').value = label;
      document.getElementById('full_name').value = fullName;
      document.getElementById('phone').value = phone;
      document.getElementById('address_line1').value = addressLine1;
      document.getElementById('address_line2').value = addressLine2;
      document.getElementById('city').value = city;
      document.getElementById('state').value = state;
      document.getElementById('postal_code').value = postalCode;
      document.getElementById('country').value = country;
      document.getElementById('is_default').checked = isDefault;
      
      addressModalTitle.textContent = 'Edit Address';
      addressModal.style.display = 'block';
    });
  });
});
</script>
</body>
</html>