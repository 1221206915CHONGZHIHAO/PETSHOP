<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logout type
$logout_type = $_GET['type'] ?? '';

// Handle Customer Logout
if ($logout_type === 'customer') {
    // Store customer data for logging
    $customer_data = [
        'name' => $_SESSION['customer_name'] ?? null,
        'email' => $_SESSION['email'] ?? null
    ];

    // Clear customer session data
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['email']);
    unset($_SESSION['cart']);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        unset($_SESSION['role']);
    }

    // Log logout
    if ($customer_data['name']) {
        try {
            $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $customer_data['name'], $customer_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Customer logout error: " . $e->getMessage());
        }
    }

    header("Location: login.php");
    exit();
}

// Handle Staff Logout
elseif ($logout_type === 'staff') {
    // Store staff data for logging
    $staff_data = [
        'id' => $_SESSION['staff_id'] ?? null,
        'name' => $_SESSION['staff_name'] ?? null,
        'email' => $_SESSION['staff_email'] ?? null
    ];

    // Clear staff session data
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_email']);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
        unset($_SESSION['role']);
    }

    // Log logout
    if ($staff_data['id']) {
        try {
            $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) VALUES (?, ?, ?, 'logout')");
            $stmt->bind_param("iss", $staff_data['id'], $staff_data['name'], $staff_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Staff logout error: " . $e->getMessage());
        }
    }

    // Redirect to admin login (shared page)
    header("Location: admin_login.php");
    exit();
}

// Handle Admin Logout
elseif ($logout_type === 'admin') {
    // Store admin data for logging
    $admin_data = [
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null
    ];

    // Clear admin session data
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        unset($_SESSION['role']);
    }

    // Log logout
    if ($admin_data['username']) {
        try {
            $stmt = $conn->prepare("INSERT INTO admin_login_logs (username, email, status) VALUES (?, ?, 'logout')");
            $stmt->bind_param("ss", $admin_data['username'], $admin_data['email']);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Admin logout error: " . $e->getMessage());
        }
    }

    // Redirect to admin login
    header("Location: admin_login.php");
    exit();
}

// Default redirect
header("Location: login.php");
exit();

$conn->close();
?>