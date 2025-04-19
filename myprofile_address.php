<?php
// Start session first thing - before any output
session_start();

// Database connection setup
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "petshop"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['customer_ID'];

// Fetch user details from database
$sql = "SELECT * FROM customer WHERE customer_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Handle case where user is not found
    echo "User not found";
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update personal information
    if (isset($_POST['update_personal_info'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        
        $update_sql = "UPDATE customer SET Customer_name = ?, Customer_email = ? WHERE Customer_ID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $name, $email, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Personal information updated successfully!";
            // Update local user data
            $user['Customer_name'] = $name;
            $user['Customer_email'] = $email;
        } else {
            $error_message = "Error updating information: " . $conn->error;
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password - consider using password_verify() if using hashed passwords
        if ($current_password === $user['Customer_password']) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                $pass_sql = "UPDATE customer SET Customer_password = ? WHERE Customer_ID = ?";
                $pass_stmt = $conn->prepare($pass_sql);
                $pass_stmt->bind_param("si", $new_password, $user_id);
                
                if ($pass_stmt->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password: " . $conn->error;
                }
            } else {
                $error_message = "New passwords do not match";
            }
        } else {
            $error_message = "Current password is incorrect";
        }
    }
    
    // Add or update address
    if (isset($_POST['update_address'])) {
        // First check if customer_address table exists
        $check_table_sql = "SHOW TABLES LIKE 'customer_address'";
        $table_result = $conn->query($check_table_sql);
        
        if ($table_result->num_rows == 0) {
            // Create customer_address table if it doesn't exist
            $create_table_sql = "CREATE TABLE customer_address (
                address_id INT AUTO_INCREMENT PRIMARY KEY,
                Customer_ID INT NOT NULL,
                address_line1 VARCHAR(255) NOT NULL,
                address_line2 VARCHAR(255),
                city VARCHAR(100) NOT NULL,
                state VARCHAR(100) NOT NULL,
                postal_code VARCHAR(20) NOT NULL,
                country VARCHAR(100) NOT NULL,
                is_default TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (Customer_ID) REFERENCES customer(Customer_ID) ON DELETE CASCADE
            )";
            $conn->query($create_table_sql);
        }
        
        $address_id = $_POST['address_id'] ?? null;
        $address_line1 = $_POST['address_line1'];
        $address_line2 = $_POST['address_line2'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $postal_code = $_POST['postal_code'];
        $country = $_POST['country'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if ($is_default) {
            // If this address is set as default, remove default status from all other addresses
            $clear_default_sql = "UPDATE customer_address SET is_default = 0 WHERE Customer_ID = ?";
            $clear_stmt = $conn->prepare($clear_default_sql);
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();
        }
        
        if ($address_id) {
            // Update existing address
            $addr_sql = "UPDATE customer_address SET address_line1 = ?, address_line2 = ?, city = ?, 
                        state = ?, postal_code = ?, country = ?, is_default = ? WHERE address_id = ? AND Customer_ID = ?";
            $addr_stmt = $conn->prepare($addr_sql);
            $addr_stmt->bind_param("ssssssiis", $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default, $address_id, $user_id);
        } else {
            // Add new address
            $addr_sql = "INSERT INTO customer_address (Customer_ID, address_line1, address_line2, city, state, postal_code, country, is_default) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $addr_stmt = $conn->prepare($addr_sql);
            $addr_stmt->bind_param("issssssi", $user_id, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default);
        }
        
        if ($addr_stmt->execute()) {
            $success_message = "Address " . ($address_id ? "updated" : "added") . " successfully!";
        } else {
            $error_message = "Error with address: " . $conn->error;
        }
    }
    
    // Delete address
    if (isset($_POST['delete_address'])) {
        $address_id = $_POST['address_id'];
        
        $del_sql = "DELETE FROM customer_address WHERE address_id = ? AND Customer_ID = ?";
        $del_stmt = $conn->prepare($del_sql);
        $del_stmt->bind_param("ii", $address_id, $user_id);
        
        if ($del_stmt->execute()) {
            $success_message = "Address deleted successfully!";
        } else {
            $error_message = "Error deleting address: " . $conn->error;
        }
    }
}

// Check if customer_address table exists
$check_table_sql = "SHOW TABLES LIKE 'customer_address'";
$table_result = $conn->query($check_table_sql);

$addresses = [];
if ($table_result->num_rows > 0) {
    // Fetch user addresses
    $addr_sql = "SELECT * FROM customer_address WHERE Customer_ID = ?";
    $addr_stmt = $conn->prepare($addr_sql);
    $addr_stmt->bind_param("i", $user_id);
    $addr_stmt->execute();
    $addresses_result = $addr_stmt->get_result();
    
    while ($address = $addresses_result->fetch_assoc()) {
        $addresses[] = $address;
    }
}

// Extract initials from customer name
$name = $user['Customer_name'];
$initials = strtoupper(substr($name, 0, 1));
if (strpos($name, ' ') !== false) {
    $name_parts = explode(' ', $name);
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - My Profile/Address</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: flex;
            gap: 20px;
        }
        .sidebar {
            width: 220px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            color: #333;
            text-decoration: none;
            gap: 12px;
        }
        .sidebar-item.active {
            color: #4a6ee0;
            font-weight: bold;
        }
        .sidebar-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-content {
            flex: 1;
        }
        .page-title {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }
        .page-subtitle {
            color: #777;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .info-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: bold;
        }
        .edit-btn, .add-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #888;
            font-size: 20px;
        }
        .add-btn {
            color: #aaa;
            font-size: 24px;
        }
        .profile-avatar {
            width: 70px;
            height: 70px;
            background-color: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #777;
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 15px;
        }
        .info-label {
            color: #888;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-value {
            color: #333;
            font-size: 16px;
        }
        .change-link {
            color: #4a9ee0;
            text-decoration: none;
            margin-left: 5px;
            cursor: pointer;
        }
        .logout-btn {
            color: #ff6b6b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
        }
        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #4a6ee0;
            color: white;
        }
        .btn-danger {
            background-color: #ff6b6b;
            color: white;
        }
        .address-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .address-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }
        .default-badge {
            background-color: #4a6ee0;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 10px;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <a href="dashboard.php" class="sidebar-item">
                <div class="sidebar-icon">🏠</div>
                <div>Dashboard</div>
            </a>
            <a href="orders.php" class="sidebar-item">
                <div class="sidebar-icon">📦</div>
                <div>My Orders</div>
            </a>
            <a href="favorites.php" class="sidebar-item">
                <div class="sidebar-icon">❤️</div>
                <div>My Favourite</div>
            </a>
            <a href="profile.php" class="sidebar-item active">
                <div class="sidebar-icon">👤</div>
                <div>My Profile/Address</div>
            </a>
            <a href="logout.php" class="sidebar-item logout-btn">
                <div class="sidebar-icon">↪️</div>
                <div>Logout</div>
            </a>
        </div>
        
        <div class="main-content">
            <h1 class="page-title">Account Details</h1>
            <p class="page-subtitle">Find you account details here. You have the ability to edit.</p>
            
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="info-section">
                <div class="section-header">
                    <div class="section-title">
                        <span>👤</span>
                        <span>Personal Info</span>
                    </div>
                    <button class="edit-btn" onclick="openModal('personalInfoModal')">✏️</button>
                </div>
                
                <div class="profile-avatar">
                    <?php echo $initials; ?>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['Customer_name']); ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Password:</div>
                    <div class="info-value">******** <a onclick="openModal('passwordModal')" class="change-link">[ Change password ]</a></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['Customer_email']); ?></div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="section-header">
                    <div class="section-title">
                        <span>📍</span>
                        <span>Address</span>
                    </div>
                    <button class="add-btn" onclick="openModal('addressModal'); clearAddressForm();">+</button>
                </div>
                
                <?php if (count($addresses) > 0): ?>
                    <?php foreach($addresses as $address): ?>
                        <div class="address-card">
                            <div class="address-actions">
                                <button class="edit-btn" onclick="editAddress(<?php echo $address['address_id']; ?>)">✏️</button>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                    <button type="submit" name="delete_address" class="edit-btn">🗑️</button>
                                </form>
                            </div>
                            <strong>
                                <?php echo htmlspecialchars($address['address_line1']); ?>
                                <?php if($address['is_default']): ?>
                                    <span class="default-badge">Default</span>
                                <?php endif; ?>
                            </strong><br>
                            <?php if(!empty($address['address_line2'])): ?>
                                <?php echo htmlspecialchars($address['address_line2']); ?><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($address['city']); ?>, 
                            <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br>
                            <?php echo htmlspecialchars($address['country']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No addresses found. Click the + button to add an address.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Personal Info Modal -->
    <div id="personalInfoModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('personalInfoModal')">&times;</span>
            <h2>Edit Personal Information</h2>
            <form method="post">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['Customer_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['Customer_email']); ?>" required>
                </div>
                <button type="submit" name="update_personal_info" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
    
    <!-- Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('passwordModal')">&times;</span>
            <h2>Change Password</h2>
            <form method="post">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
    
    <!-- Address Modal -->
    <div id="addressModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('addressModal')">&times;</span>
            <h2 id="addressModalTitle">Add Address</h2>
            <form method="post">
                <input type="hidden" id="address_id" name="address_id" value="">
                <div class="form-group">
                    <label for="address_line1">Address Line 1</label>
                    <input type="text" id="address_line1" name="address_line1" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="address_line2">Address Line 2 (Optional)</label>
                    <input type="text" id="address_line2" name="address_line2" class="form-control">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="is_default" name="is_default"> Set as default address
                    </label>
                </div>
                <button type="submit" name="update_address" class="btn btn-primary">Save Address</button>
            </form>
        </div>
    </div>
    
    <script>
        // Open modal
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        
        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // Clear address form
        function clearAddressForm() {
            document.getElementById('addressModalTitle').innerText = 'Add Address';
            document.getElementById('address_id').value = '';
            document.getElementById('address_line1').value = '';
            document.getElementById('address_line2').value = '';
            document.getElementById('city').value = '';
            document.getElementById('state').value = '';
            document.getElementById('postal_code').value = '';
            document.getElementById('country').value = '';
            document.getElementById('is_default').checked = false;
        }
        
        // Edit address
        function editAddress(addressId) {
            // Fetch address data with AJAX
            fetch('get_address.php?id=' + addressId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('addressModalTitle').innerText = 'Edit Address';
                    document.getElementById('address_id').value = data.address_id;
                    document.getElementById('address_line1').value = data.address_line1;
                    document.getElementById('address_line2').value = data.address_line2 || '';
                    document.getElementById('city').value = data.city;
                    document.getElementById('state').value = data.state;
                    document.getElementById('postal_code').value = data.postal_code;
                    document.getElementById('country').value = data.country;
                    document.getElementById('is_default').checked = data.is_default == 1;
                    openModal('addressModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load address data');
                });
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>