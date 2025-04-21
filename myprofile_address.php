<?php
session_start();
require_once 'db_connection.php'; // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update profile information
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        
        $stmt = $conn->prepare("UPDATE customer SET Customer_name = ?, Customer_email = ? WHERE Customer_ID = ?");
        $stmt->bind_param("ssi", $name, $email, $customer_id);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['customer_name'] = $name;
            
            // Refresh customer data
            $stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_ID = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
        } else {
            $error_message = "Failed to update profile: " . $conn->error;
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if ($current_password != $customer['Customer_password']) {
            $password_error = "Current password is incorrect";
        } elseif ($new_password != $confirm_password) {
            $password_error = "New passwords do not match";
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
        
        // If this is being set as default, remove default from other addresses
        if ($is_default) {
            $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 0 WHERE Customer_ID = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        }
        
        if ($address_id) {
            // Update existing address
            $stmt = $conn->prepare("UPDATE customer_address SET 
                Address_Label = ?, 
                Full_Name = ?, 
                Phone_Number = ?, 
                Address_Line1 = ?, 
                Address_Line2 = ?, 
                City = ?, 
                State = ?, 
                Postal_Code = ?, 
                Country = ?, 
                Is_Default = ? 
                WHERE Address_ID = ? AND Customer_ID = ?");
            $stmt->bind_param("sssssssssiis", $label, $full_name, $phone, $line1, $line2, $city, $state, $postal, $country, $is_default, $address_id, $customer_id);
        } else {
            // Add new address
            $stmt = $conn->prepare("INSERT INTO customer_address 
                (Customer_ID, Address_Label, Full_Name, Phone_Number, Address_Line1, Address_Line2, City, State, Postal_Code, Country, Is_Default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssssi", $customer_id, $label, $full_name, $phone, $line1, $line2, $city, $state, $postal, $country, $is_default);
        }
        
        if ($stmt->execute()) {
            $address_success = "Address saved successfully!";
            // Refresh address list
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
            // Refresh address list
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
        
        // First remove default from all addresses
        $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 0 WHERE Customer_ID = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        
        // Set the selected address as default
        $stmt = $conn->prepare("UPDATE customer_address SET Is_Default = 1 WHERE Address_ID = ? AND Customer_ID = ?");
        $stmt->bind_param("ii", $address_id, $customer_id);
        
        if ($stmt->execute()) {
            $address_success = "Default address updated successfully!";
            // Refresh address list
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
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Dashboard specific styles */
    .dashboard-container {
      display: flex;
      padding: 20px;
      min-height: calc(100vh - 76px - 91px); /* Account for navbar and footer height */
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
    
    .sidebar-nav a:hover, .sidebar-nav a.active {
      background-color: #e9ecef;
    }
    
    .sidebar-nav a i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    .main-content {
      flex: 1;
    }
    
    .info-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .account-details {
      display: flex;
      align-items: center;
    }
    
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
    }
    
    .user-info {
      flex: 1;
    }
    
    .user-info .row {
      margin-bottom: 10px;
    }
    
    /* Tab controls */
    .nav-tabs .nav-link {
      color: #495057;
      border-radius: 0;
      border: none;
      padding: 1rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--bs-primary);
      background-color: transparent;
      border-bottom: 3px solid var(--bs-primary);
    }
    
    /* Address card styling */
    .address-card {
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      position: relative;
    }
    
    .address-card .badge {
      background-color: var(--bs-primary);
      position: absolute;
      top: 10px;
      right: 10px;
    }
    
    .address-card h3 {
      margin-bottom: 15px;
      padding-right: 70px;
    }
    
    .address-actions {
      margin-top: 15px;
    }
    
    /* Add button */
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
    
    .add-address-btn:hover {
      background-color: #f8f9fa;
    }
    
    /* Modal styles */
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
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .form-row .form-group {
      flex: 1;
      margin-bottom: 0;
    }
    
    .btn {
      background-color: var(--bs-primary);
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    
    .btn-secondary {
      background-color: #6c757d;
    }
    
    .password-container {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 0;
      top: 0;
      cursor: pointer;
      background: none;
      border: none;
      color: #6c757d;
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark custom-nav">
  <div class="container">
    <!-- Brand on the left -->
    <a class="navbar-brand" href="userhomepage.php">
      <img src="cat_paw.png" alt="Pet Shop" width="50">
      <span>Hachi Pet Shop</span>
    </a>
    
    <!-- Toggler for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main nav links centered -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <!-- Icons on the right -->
      <ul class="navbar-nav ms-auto">
        <!-- Search Icon with Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-search" style="font-size: 1.2rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="searchDropdown" style="min-width: 250px;">
            <form class="d-flex">
              <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
              <button class="btn btn-primary" type="submit">Go</button>
            </form>
          </ul>
        </li>

        <!-- Cart Icon with item count -->
        <li class="nav-item">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart" style="font-size: 1.2rem;"></i>
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo count($_SESSION['cart']); ?>
              </span>
            <?php endif; ?>
          </a>
        </li>

        <!-- User Icon with Dynamic Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- Show username when logged in -->
              <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
            <?php else: ?>
              <i class="bi bi-person" style="font-size: 1.2rem;"></i>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- If user is logged in, show dashboard options -->
              <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favourite</a></li>
              <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            <?php else: ?>
              <!-- If not logged in, show login/register links -->
              <li><a class="dropdown-item" href="login.php">Login</a></li>
              <li><a class="dropdown-item" href="register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dashboard Content -->
<div class="dashboard-container container">
  <!-- Sidebar -->
  <div class="sidebar">
    <ul class="sidebar-nav">
      <li>
        <a href="dashboard.php">
          <i class="bi bi-house"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="my_orders.php">
          <i class="bi bi-box"></i> My Orders
        </a>
      </li>
      <li>
        <a href="favorites.php">
          <i class="bi bi-heart"></i> My Favourite
        </a>
      </li>
      <li>
        <a href="myprofile_address.php" class="active">
          <i class="bi bi-person-lines-fill"></i> My Profile/Address
        </a>
      </li>
      <li>
        <a href="logout.php" class="text-danger">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <!-- Alert Messages -->
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
    
    <!-- Tabs -->
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
      <!-- Profile Tab -->
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
              <?php 
              // Display user initials
              $name = $customer['Customer_name'];
              $initials = strtoupper(substr($name, 0, 1));
              if (strpos($name, ' ') !== false) {
                $name_parts = explode(' ', $name);
                $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
              }
              echo $initials;
              ?>
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
                    <i class="bi bi-pencil"></i> Change
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
      
      <!-- Address Tab -->
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
          
          <!-- Add new address button -->
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

<!-- Footer -->
<footer class="footer bg-dark text-white py-4">
  <div class="container text-center">
    <div class="mb-3">
      <a href="#" class="text-white me-3">Facebook</a>
      <a href="#" class="text-white me-3">Twitter</a>
      <a href="#" class="text-white">Instagram</a>
    </div>
    <p>&copy; 2025 Hachi Pet Shop. All rights reserved.</p>
    <p>Email: <a href="mailto:info@petshop.com" class="text-decoration-none text-white">info@petshop.com</a></p>
  </div>
</footer>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h4>Edit Profile Information</h4>
      <button type="button" class="close-button">&times;</button>
    </div>
    <form method="post">
      <div class="form-group">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($customer['Customer_name']); ?>" required>
      </div>
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer['Customer_email']); ?>" required>
      </div>
      <div class="text-end">
        <button type="button" class="btn btn-secondary me-2 close-modal-btn">Cancel</button>
        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Change Password Modal -->
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
      </div>
      <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>
      <div class="text-end">
        <button type="button" class="btn btn-secondary me-2 close-modal-btn">Cancel</button>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
      </div>
    </form>
  </div>
</div>

<!-- Add/Edit Address Modal -->
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
          <label for="state" class="form-label">State/Province</label>
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

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Modal handling functions
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close-button, .close-modal-btn');
    
    // Edit profile button
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const editProfileModal = document.getElementById('edit-profile-modal');
    
    editProfileBtn.addEventListener('click', function() {
      editProfileModal.style.display = 'block';
    });
    
    // Change password button
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const changePasswordModal = document.getElementById('change-password-modal');
    
    changePasswordBtn.addEventListener('click', function() {
      changePasswordModal.style.display = 'block';
    });
    
    // Add address button
    const addAddressBtn = document.getElementById('add-address-btn');
    const addressModal = document.getElementById('address-modal');
    const addressModalTitle = document.getElementById('address-modal-title');
    
    addAddressBtn.addEventListener('click', function() {
      // Clear form fields
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
    
    // Edit address buttons
    const editAddressBtns = document.querySelectorAll('.edit-address-btn');
    
    editAddressBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        const addressId = this.getAttribute('data-id');
        
        // Fetch address data via AJAX or from a data attribute
        // For simplicity, you might want to store the address data in a JavaScript object or 
        // fetch it from the server via AJAX
        
        // Example of how you might populate the form:
        // document.getElementById('address_id').value = addressId;
        // document.getElementById('address_label').value = addressData.label;
        // ...
        
        addressModalTitle.textContent = 'Edit Address';
        addressModal.style.display = 'block';
      });
    });
    
    // Close modals
    closeButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        modals.forEach(function(modal) {
          modal.style.display = 'none';
        });
      });
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      modals.forEach(function(modal) {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });
  });
</script>
</body>
</html>