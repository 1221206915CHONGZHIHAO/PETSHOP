<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize redirect URL (default to admin login)
$redirect_url = "admin_login.php";

// If staff is logged in, record logout activity
if (isset($_SESSION['staff_id'])) {
    $staff_id = $_SESSION['staff_id'];
    $username = $_SESSION['staff_name'] ?? $_SESSION['username'] ?? 'Unknown';
    $email = $_SESSION['staff_email'] ?? $_SESSION['email'] ?? 'unknown@example.com';
    
    try {
        // First verify the staff exists in the database
        $check_stmt = $conn->prepare("SELECT Staff_ID FROM staff WHERE Staff_ID = ?");
        $check_stmt->bind_param("i", $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Staff exists, proceed with logout logging
            $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                                  VALUES (?, ?, ?, 'logout')");
            $stmt->bind_param("iss", $staff_id, $username, $email);
            if (!$stmt->execute()) {
                error_log("Failed to log staff logout: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Attempt to log logout for non-existent staff ID: " . $staff_id);
        }
        $check_stmt->close();
    } catch (Exception $e) {
        error_log("Error during staff logout logging: " . $e->getMessage());
    }
}

// If admin is logged in
if (isset($_SESSION['admin_logged_in'])) {
    $username = $_SESSION['username'] ?? 'Admin';
    $email = $_SESSION['email'] ?? 'admin@example.com';
    
    try {
        // Use a separate table for admin logs if possible
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
}

// If customer is logged in
if (isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']) && isset($_SESSION['email'])) {
    $customer_id = $_SESSION['customer_id'];
    
    try {
        // Verify customer exists before logging
        $check_stmt = $conn->prepare("SELECT Customer_ID FROM customer WHERE Customer_ID = ?");
        $check_stmt->bind_param("i", $customer_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Use prepared statement instead of direct query
            $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) 
                                  VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $_SESSION['customer_name'], $_SESSION['email']);
            if (!$stmt->execute()) {
                error_log("Failed to log customer logout: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Attempt to log logout for non-existent customer ID: " . $customer_id);
        }
        $check_stmt->close();
        
        // Only customers go to login.php
        $redirect_url = "login.php";
    } catch (Exception $e) {
        error_log("Error during customer logout logging: " . $e->getMessage());
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

$conn->close();

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>