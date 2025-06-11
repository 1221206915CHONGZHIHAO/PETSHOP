<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get logout type from URL
$logout_type = $_GET['type'] ?? '';

// Store current sessions before any changes
$active_sessions = [
    'customer' => isset($_SESSION['customer_id']),
    'staff' => isset($_SESSION['staff_id']),
    'admin' => isset($_SESSION['admin_logged_in'])
];


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
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        unset($_SESSION['role']);
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

    // Redirect to customer login
    header("Location: login.php");
    exit();
}

// Handle Staff Logout
elseif ($logout_type === 'staff') {
    // Backup staff data for logging
    $log_data = [
        'id' => $_SESSION['staff_id'] ?? 0,
        'name' => $_SESSION['staff_name'] ?? '',
        'email' => $_SESSION['staff_email'] ?? ''
    ];

    // Clear only staff-related session data
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_email']);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
        unset($_SESSION['role']);
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

    // Redirect to shared admin/staff login
    header("Location: admin_login.php");
    exit();
}

// Handle Admin Logout
elseif ($logout_type === 'admin') {
    // Backup admin data for logging
    $log_data = [
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? ''
    ];

    // Clear only admin-related session data
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        unset($_SESSION['role']);
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

    // Redirect to shared admin/staff login
    header("Location: admin_login.php");
    exit();
}

// Default behavior for invalid/missing logout type
if ($active_sessions['customer']) {
    header("Location: userhomepage.php");
} elseif ($active_sessions['staff']) {
    header("Location: staff_homepage.php");
} elseif ($active_sessions['admin']) {
    header("Location: admin_homepage.php");
} else {
    header("Location: login.php");
}
exit();

$conn->close();
?>