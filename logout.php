<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get logout type from URL
$logout_type = $_GET['type'] ?? '';

// Handle Customer Logout
if ($logout_type === 'customer') {
    // Backup customer data for logging
    $log_data = [
        'name' => $_SESSION['customer_name'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ];

    // Clear only customer-related session data
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['email']);
    unset($_SESSION['cart']);
    unset($_SESSION['profile_image']);
    unset($_SESSION['cart_count']);
    if (isset($_SESSION['customer_role'])) {
        unset($_SESSION['customer_role']);
    }

    // Log the logout
    if (!empty($log_data['name'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $log_data['name'], $log_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Customer logout logging failed: " . $e->getMessage());
        }
    }

    // Redirect to customer login with success message
    header("Location: login.php?logout=success");
    exit();
}

// Handle Staff Logout
elseif ($logout_type === 'staff') {
    // Backup staff data for logging
    $log_data = [
        'id' => $_SESSION['staff_id'] ?? 0,
        'name' => $_SESSION['staff_username'] ?? '',
        'email' => $_SESSION['staff_email'] ?? ''
    ];

    // Clear only staff-related session data
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_username']);
    unset($_SESSION['staff_email']);
    unset($_SESSION['staff_avatar_path']);
    if (isset($_SESSION['staff_role'])) {
        unset($_SESSION['staff_role']);
    }

    // Log the logout
    if ($log_data['id'] > 0) {
        try {
            $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) VALUES (?, ?, ?, 'logout')");
            $stmt->bind_param("iss", $log_data['id'], $log_data['name'], $log_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Staff logout logging failed: " . $e->getMessage());
        }
    }

    // Always redirect to admin login for staff logout
    header("Location: admin_login.php?logout=success");
    exit();
}

// Handle Admin Logout
elseif ($logout_type === 'admin') {
    // Backup admin data for logging
    $log_data = [
        'username' => $_SESSION['admin_username'] ?? '',
        'email' => $_SESSION['admin_email'] ?? ''
    ];

    // Clear only admin-related session data
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
    if (isset($_SESSION['admin_role'])) {
        unset($_SESSION['admin_role']);
    }

    // Log the logout
    if (!empty($log_data['username'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, email, status) VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $log_data['username'], $log_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Admin logout logging failed: " . $e->getMessage());
        }
    }

    // Redirect to admin login
    header("Location: admin_login.php?logout=success");
    exit();
}

// Default behavior - check which session exists and redirect accordingly
if (isset($_SESSION['customer_id'])) {
    header("Location: userhomepage.php");
} elseif (isset($_SESSION['staff_id'])) {
    header("Location: staff_homepage.php");
} elseif (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_homepage.php");
} else {
    header("Location: login.php");
}
exit();

$conn->close();
?>