<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the logout type from URL parameter (e.g., logout.php?type=customer or logout.php?type=admin)
$logout_type = isset($_GET['type']) ? $_GET['type'] : '';

// Initialize redirect URL
$redirect_url = "login.php"; // Default to customer login

// Handle Customer Logout
if ($logout_type === 'customer' || (empty($logout_type) && isset($_SESSION['customer_id']))) {
    if (isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']) && isset($_SESSION['email'])) {
        $customer_id = $_SESSION['customer_id'];
        
        try {
            // Verify customer exists before logging
            $check_stmt = $conn->prepare("SELECT Customer_ID FROM customer WHERE Customer_ID = ?");
            $check_stmt->bind_param("i", $customer_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Log customer logout
                $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) 
                                      VALUES (?, ?, 'logout')");
                $stmt->bind_param("ss", $_SESSION['customer_name'], $_SESSION['email']);
                if (!$stmt->execute()) {
                    error_log("Failed to log customer logout: " . $stmt->error);
                }
                $stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            error_log("Error during customer logout logging: " . $e->getMessage());
        }
    }
    
    // Only unset customer-related session variables
    unset($_SESSION['role']);
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['email']);
    
    $redirect_url = "login.php";
}

// Handle Admin/Staff Logout
elseif ($logout_type === 'admin' || isset($_SESSION['admin_logged_in']) || isset($_SESSION['staff_id'])) {
    
    // Handle Staff Logout
    if (isset($_SESSION['staff_id'])) {
        $staff_id = $_SESSION['staff_id'];
        $username = $_SESSION['staff_name'] ?? $_SESSION['username'] ?? 'Unknown';
        $email = $_SESSION['staff_email'] ?? $_SESSION['email'] ?? 'unknown@example.com';
        
        try {
            $check_stmt = $conn->prepare("SELECT Staff_ID FROM staff WHERE Staff_ID = ?");
            $check_stmt->bind_param("i", $staff_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                                      VALUES (?, ?, ?, 'logout')");
                $stmt->bind_param("iss", $staff_id, $username, $email);
                if (!$stmt->execute()) {
                    error_log("Failed to log staff logout: " . $stmt->error);
                }
                $stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            error_log("Error during staff logout logging: " . $e->getMessage());
        }
        
        // Unset staff session variables
        unset($_SESSION['staff_id']);
        unset($_SESSION['staff_name']);
        unset($_SESSION['staff_email']);
    }
    
    // Handle Admin Logout
    if (isset($_SESSION['admin_logged_in'])) {
        $username = $_SESSION['username'] ?? 'Admin';
        $email = $_SESSION['email'] ?? 'admin@example.com';
        
        try {
            $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, email, status) 
                                  VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $username, $email);
            if (!$stmt->execute()) {
                error_log("Failed to log admin logout: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error during admin logout logging: " . $e->getMessage());
        }
        
        // Unset admin session variables
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['username']);
        unset($_SESSION['admin_email']);
    }
    
    $redirect_url = "admin_login.php";
}

// If no specific logout type and no sessions found, clear everything as fallback
else {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

$conn->close();

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>