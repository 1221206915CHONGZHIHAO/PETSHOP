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

// Function to completely destroy session
function destroySession() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

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
                // Log customer logout with IP address
                $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status, ip_address) 
                                      VALUES (?, ?, 'logout', ?)");
                $stmt->bind_param("sss", $_SESSION['customer_name'], $_SESSION['email'], $_SERVER['REMOTE_ADDR']);
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
    
    // Destroy the session completely
    destroySession();
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
                // Log staff logout with IP address
                $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status, ip_address) 
                                      VALUES (?, ?, ?, 'logout', ?)");
                $stmt->bind_param("isss", $staff_id, $username, $email, $_SERVER['REMOTE_ADDR']);
                if (!$stmt->execute()) {
                    error_log("Failed to log staff logout: " . $stmt->error);
                }
                $stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            error_log("Error during staff logout logging: " . $e->getMessage());
        }
    }
    
    // Handle Admin Logout
    if (isset($_SESSION['admin_logged_in'])) {
        $username = $_SESSION['username'] ?? 'Admin';
        $email = $_SESSION['email'] ?? 'admin@example.com';
        
        try {
            // Log admin logout with IP address
            $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, email, status, ip_address) 
                                  VALUES (?, ?, 'logout', ?)");
            $stmt->bind_param("sss", $username, $email, $_SERVER['REMOTE_ADDR']);
            if (!$stmt->execute()) {
                error_log("Failed to log admin logout: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error during admin logout logging: " . $e->getMessage());
        }
    }
    
    // Destroy the session completely
    destroySession();
    $redirect_url = "admin_login.php";
}

// If no specific logout type and no sessions found, clear everything as fallback
else {
    destroySession();
    $redirect_url = "login.php";
}

$conn->close();

// Prevent caching of the logout page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>