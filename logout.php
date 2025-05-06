<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// If staff is logged in, record logout activity
if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff' && 
    isset($_SESSION['staff_id']) && isset($_SESSION['username']) && isset($_SESSION['email'])) {
    
        $stmt = $conn->prepare("INSERT INTO staff_login_logs (staff_id, username, email, status) 
        VALUES (?, ?, ?, 'logout')");
$stmt->bind_param("iss", $_SESSION['staff_id'], $_SESSION['username'], $_SESSION['email']);

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

// Redirect to homepage
header("Location: userhomepage.php");
exit();
?>