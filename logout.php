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
    
    $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                          VALUES (?, ?, ?, 'logout')");
    $stmt->bind_param("iss", $staff_id, $username, $email);
    $stmt->execute();
    $stmt->close();
}

// If admin is logged in
if (isset($_SESSION['admin_logged_in'])) {
    $username = $_SESSION['username'] ?? 'Admin';
    $email = $_SESSION['email'] ?? 'admin@example.com';
    
    $stmt = $conn->prepare("INSERT INTO staff_login_logs (username, email, status) 
                          VALUES (?, ?, 'logout')");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->close();
}

// If customer is logged in
if (isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']) && isset($_SESSION['email'])) {
    $customer_id = $_SESSION['customer_id'];
    
    $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                VALUES ('{$_SESSION['customer_name']}', '{$_SESSION['email']}', 'logout')");
    
    // Only customers go to login.php
    $redirect_url = "login.php";
}

// Unset all session variables
session_unset();

// Destroy the current session
session_destroy();

$conn->close();

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>