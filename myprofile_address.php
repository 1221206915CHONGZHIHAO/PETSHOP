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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile/Address</title>
      <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
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
    
    /* Password toggle styling */
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
              <li><a class="dropdown-item" href="account_setting.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
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
        <a href="dashboard.php" class="active">
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
        <a href="myprofile_address.php">
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
        <!-- Main Content Area -->
        <div class="main-content">
            <h1 class="page-title">Account Details</h1>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="tab-container">
                <div class="tab-header">
                    <button class="tab-button active" data-tab="personal-info">
                        <i class="fas fa-user-circle"></i> Personal Info
                    </button>
                    <button class="tab-button" data-tab="address">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </button>
                </div>

                <!-- Personal Info Tab -->
                <div id="personal-info" class="tab-content active">
                    <div class="profile-header">
                        <h2>Personal Info</h2>
                        <button class="edit-button" id="edit-profile-btn">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>

                    <div class="profile-info">
                        <div class="profile-row">
                            <div class="profile-avatar">
                                <?php
                                    $initials = '';
                                    $nameParts = explode(' ', $customer['Customer_name']);
                                    foreach ($nameParts as $part) {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                    }
                                    echo $initials;
                                ?>
                            </div>
                            <div class="profile-details">
                                <div class="info-row">
                                    <label>Name:</label>
                                    <div class="value"><?php echo $customer['Customer_name']; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <label>Password:</label>
                            <div class="value">******** <a href="#" id="change-password-link">[Change password]</a></div>
                        </div>

                        <div class="info-row">
                            <label>Email:</label>
                            <div class="value"><?php echo $customer['Customer_email']; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Address Tab -->
                <div id="address" class="tab-content">
                    <div class="profile-header">
                        <h2>Delivery Addresses</h2>
                    </div>

                    <?php if (isset($address_success)): ?>
                        <div class="alert alert-success">
                            <?php echo $address_success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($address_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $address_error; ?>
                        </div>
                    <?php endif; ?>

                    <div id="addresses-container">
                        <?php if ($addresses->num_rows > 0): ?>
                            <?php while ($address = $addresses->fetch_assoc()): ?>
                                <div class="address-card" data-address-id="<?php echo $address['Address_ID']; ?>">
                                    <h3>
                                        <span><?php echo htmlspecialchars($address['Address_Label']); ?></span>
                                        <?php if ($address['Is_Default']): ?>
                                            <span class="badge">Default</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p><?php echo htmlspecialchars($address['Full_Name']); ?></p>
                                    <p><?php echo htmlspecialchars($address['Address_Line1']); ?></p>
                                    <?php if (!empty($address['Address_Line2'])): ?>
                                        <p><?php echo htmlspecialchars($address['Address_Line2']); ?></p>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars($address['City']); ?>, <?php echo htmlspecialchars($address['Postal_Code']); ?></p>
                                    <p>
                                        <?php if (!empty($address['State'])): ?>
                                            <?php echo htmlspecialchars($address['State']); ?>, 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['Country']); ?>
                                    </p>
                                    <p>Phone: <?php echo htmlspecialchars($address['Phone_Number']); ?></p>
                                    <div class="address-actions">
                                        <button class="btn btn-secondary edit-address-btn" data-id="<?php echo $address['Address_ID']; ?>">Edit</button>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="address_id" value="<?php echo $address['Address_ID']; ?>">
                                            <button type="submit" name="delete_address" class="btn btn-secondary" onclick="return confirm('Are you sure you want to delete this address?')">Delete</button>
                                        </form>
                                        <?php if (!$address['Is_Default']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="address_id" value="<?php echo $address['Address_ID']; ?>">
                                                <button type="submit" name="set_default" class="btn">Set as Default</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>You haven't added any addresses yet.</p>
                        <?php endif; ?>

                        <!-- Add new address button -->
                        <div class="add-button" id="add-address-btn">
                            <i class="fas fa-plus"></i> Add New Address
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="edit-profile-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Personal Information</h2>
                <button class="close-button">&times;</button>
            </div>
            <form id="edit-profile-form" method="post">
                <div class="form-group">
                    <label for="edit-name">Name:</label>
                    <input type="text" id="edit-name" name="name" class="form-control" value="<?php echo $customer['Customer_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit-email">Email:</label>
                    <input type="email" id="edit-email" name="email" class="form-control" value="<?php echo $customer['Customer_email']; ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="update_profile" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="change-password-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Password</h2>
                <button class="close-button">&times;</button>
            </div>
            <?php if (isset($password_error)): ?>
                <div class="alert alert-danger">
                    <?php echo $password_error; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($password_success)): ?>
                <div class="alert alert-success">
                    <?php echo $password_success; ?>
                </div>
            <?php endif; ?>
            <form id="change-password-form" method="post">
                <div class="form-group">
                    <label for="current-password">Current Password:</label>
                    <input type="password" id="current-password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new-password">New Password:</label>
                    <input type="password" id="new-password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password:</label>
                    <input type="password" id="confirm-password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="change_password" class="btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Address Modal -->
    <div id="address-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="address-modal-title">Add New Address</h2>
                <button class="close-button">&times;</button>
            </div>
            <form id="address-form" method="post">
                <input type="hidden" id="address-id" name="address_id">
                <div class="form-group">
                    <label for="address-label">Address Label (e.g. Home, Work):</label>
                    <input type="text" id="address-label" name="address_label" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address-name">Full Name:</label>
                    <input type="text" id="address-name" name="full_name" class="form-control" value="<?php echo $customer['Customer_name']; ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="address-phone">Phone Number:</label>
                        <input type="text" id="address-phone" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address-line1">Address Line 1:</label>
                    <input type="text" id="address-line1" name="address_line1" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address-line2">Address Line 2 (Optional):</label>
                    <input type="text" id="address-line2" name="address_line2" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="address-city">City:</label>
                        <input type="text" id="address-city" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address-state">State:</label>
                        <input type="text" id="address-state" name="state" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="address-postal">Postal Code:</label>
                        <input type="text" id="address-postal" name="postal_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address-country">Country:</label>
                        <input type="text" id="address-country" name="country" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_default" id="is-default">
                        Set as default address
                    </label>
                </div>
                <div class="form-group">
                    <button type="submit" name="save_address" class="btn">Save Address</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for modal and tab interaction -->
    <script>
        // Tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                button.classList.add('active');
                const tab = button.getAttribute('data-tab');
                document.getElementById(tab).classList.add('active');
            });
        });

        // Modals
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const changePasswordLink = document.getElementById('change-password-link');
        const addAddressBtn = document.getElementById('add-address-btn');
        const modals = document.querySelectorAll('.modal');
        const closeButtons = document.querySelectorAll('.close-button');

        editProfileBtn.addEventListener('click', () => {
            document.getElementById('edit-profile-modal').style.display = 'block';
        });

        changePasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('change-password-modal').style.display = 'block';
        });

        addAddressBtn.addEventListener('click', () => {
            document.getElementById('address-modal-title').innerText = 'Add New Address';
            document.getElementById('address-form').reset();
            document.getElementById('address-id').value = '';
            document.getElementById('address-modal').style.display = 'block';
        });

        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                modals.forEach(modal => modal.style.display = 'none');
            });
        });

        // Address edit buttons
        const editButtons = document.querySelectorAll('.edit-address-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.address-card');
                const id = btn.dataset.id;
                const label = card.querySelector('h3 span').innerText.trim();
                const lines = card.querySelectorAll('p');
                const fullName = lines[0].innerText.trim();
                const addressLine1 = lines[1].innerText.trim();
                const addressLine2 = lines.length === 6 ? lines[2].innerText.trim() : '';
                const cityPostal = lines.length === 6 ? lines[3].innerText.trim() : lines[2].innerText.trim();
                const stateCountry = lines.length === 6 ? lines[4].innerText.trim() : lines[3].innerText.trim();
                const phone = lines.length === 6 ? lines[5].innerText.split(': ')[1].trim() : lines[4].innerText.split(': ')[1].trim();

                const [city, postal] = cityPostal.split(',').map(s => s.trim());
                const [state, country] = stateCountry.split(',').map(s => s.trim());

                document.getElementById('address-modal-title').innerText = 'Edit Address';
                document.getElementById('address-id').value = id;
                document.getElementById('address-label').value = label;
                document.getElementById('address-name').value = fullName;
                document.getElementById('address-line1').value = addressLine1;
                document.getElementById('address-line2').value = addressLine2;
                document.getElementById('address-city').value = city;
                document.getElementById('address-state').value = state;
                document.getElementById('address-postal').value = postal;
                document.getElementById('address-country').value = country;
                document.getElementById('address-phone').value = phone;

                document.getElementById('address-modal').style.display = 'block';
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        };
    </script>
</body>
</html>
