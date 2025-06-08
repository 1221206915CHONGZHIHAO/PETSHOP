<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the logout type from the URL parameter (e.g., logout.php?type=customer)
$logout_type = isset($_GET['type']) ? $_GET['type'] : '';

// Default redirect URL
$redirect_url = "login.php";

// --- Handle Customer Logout ---
if ($logout_type === 'customer' && isset($_SESSION['customer_id'])) {
    
    // 1. Log the logout action to the database
    try {
        $stmt = $conn->prepare("INSERT INTO customer_login_logs (username, email, status) VALUES (?, ?, 'logout')");
        $stmt->bind_param("ss", $_SESSION['customer_name'], $_SESSION['email']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error during customer logout logging: " . $e->getMessage());
    }
    
    // 2. Unset ONLY customer-related session variables (including the cart)
    unset($_SESSION['role']);
    unset($_SESSION['customer_id']);
    unset($_SESSION['customer_name']);
    unset($_SESSION['email']);
    unset($_SESSION['cart']); //  Fixes the original cart issue
    unset($_SESSION['cart_count']); // Fixes the original cart issue

    $redirect_url = "login.php";

// --- Handle Admin/Staff Logout ---
} elseif ($logout_type === 'admin' && (isset($_SESSION['admin_logged_in']) || isset($_SESSION['staff_id']))) {

    // (Optional) Add your admin/staff logout logging here if needed
    
    // Unset ONLY admin and staff-related session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_email']);
    
    $redirect_url = "admin_login.php";
}

$conn->close();

// 3. Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>