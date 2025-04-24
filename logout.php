<?php
session_start();

// If logged in, delete all cart records for this user
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "petshop");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Delete all records from the cart table for this customer
    $stmt = $conn->prepare("DELETE FROM cart WHERE Customer_ID = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Unset all session variables
session_unset();

// Destroy the current session
session_destroy();

// Redirect to homepage
header("Location: userhomepage.php");
exit();
?>
