<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// If staff is logged in, record logout activity
if (isset($_SESSION['staff_id'])) {
    // Use staff_name/staff_email if available, otherwise fall back to username/email
    $staff_id = $_SESSION['staff_id'];
    $username = $_SESSION['staff_name'] ?? $_SESSION['username'] ?? 'Unknown';
    $email = $_SESSION['staff_email'] ?? $_SESSION['email'] ?? 'unknown@example.com';
    
    $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                          VALUES (?, ?, ?, 'logout')");
    $stmt->bind_param("iss", $staff_id, $username, $email);
    $stmt->execute();
    $stmt->close();
}

// If customer is logged in (keep existing customer code exactly the same)
if (isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']) && isset($_SESSION['email'])) {
    $customer_id = $_SESSION['customer_id'];
    
    // Record logout activity
    $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                VALUES ('{$_SESSION['customer_name']}', '{$_SESSION['email']}', 'logout')");
}

// Unset all session variables
session_unset();

// Destroy the current session
session_destroy();

$conn->close();

// Redirect to login page instead of userhomepage
header("Location: login.php");
exit();
?>