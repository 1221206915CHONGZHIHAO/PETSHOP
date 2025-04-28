<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "petshop");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// If customer is logged in
if (isset($_SESSION['customer_id']) && isset($_SESSION['customer_name']) && isset($_SESSION['email'])) {
    $customer_id = $_SESSION['customer_id'];
    
    // Record logout activity
    $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                VALUES ('{$_SESSION['customer_name']}', '{$_SESSION['email']}', 'logout')");
    
    // Delete all records from the cart table for this customer
    $stmt = $conn->prepare("DELETE FROM cart WHERE Customer_ID = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();
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