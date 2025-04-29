<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    
    // Delete customer
    $sql = "DELETE FROM customer WHERE Customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Customer deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting customer: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
header("Location: customer_list.php");
exit;
?>