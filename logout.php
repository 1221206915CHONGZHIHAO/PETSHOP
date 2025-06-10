<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the logout type from URL parameter
$logout_type = isset($_GET['type']) ? $_GET['type'] : '';

// Initialize redirect URL
$redirect_url = "login.php"; // Default to customer login

// Handle Customer Logout
if ($logout_type === 'customer' || (empty($logout_type) && isset($_SESSION['customer_id']))) {
    if (isset($_SESSION['customer_id'])) {
        try {
            // Log customer logout
            $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) 
                                  VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $_SESSION['customer_name'], $_SESSION['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error during customer logout logging: " . $e->getMessage());
        }
        
        // Unset customer session
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['email']);
        unset($_SESSION['cart']);
        
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
            unset($_SESSION['role']);
        }
    }
    $redirect_url = "login.php";
}

// Handle Staff Logout (explicit or when staff session exists)
elseif ($logout_type === 'staff' || isset($_SESSION['staff_id'])) {
    if (isset($_SESSION['staff_id'])) {
        try {
            // Log staff logout
            $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                                  VALUES (?, ?, ?, 'logout')");
            $stmt->bind_param("iss", $_SESSION['staff_id'], $_SESSION['staff_name'], $_SESSION['staff_email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error during staff logout logging: " . $e->getMessage());
        }
        
        // Unset staff session
        unset($_SESSION['staff_id']);
        unset($_SESSION['staff_name']);
        unset($_SESSION['staff_email']);
        
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
            unset($_SESSION['role']);
        }
    }
    $redirect_url = "admin_login.php"; // Staff redirects to admin login
}

// Handle Admin Logout (must be explicit)
elseif ($logout_type === 'admin' && isset($_SESSION['admin_logged_in'])) {
    try {
        // Log admin logout
        $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, email, status) 
                              VALUES (?, ?, 'logout')");
        $stmt->bind_param("ss", $_SESSION['username'], $_SESSION['email']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error during admin logout logging: " . $e->getMessage());
    }
    
    // Unset admin session
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        unset($_SESSION['role']);
    }
    
    $redirect_url = "admin_login.php";
}

// Default fallback
else {
    $redirect_url = "login.php";
}

$conn->close();

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>