<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("HTTP/1.1 403 Forbidden");
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get order ID from POST
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($order_id <= 0) {
    header("HTTP/1.1 400 Bad Request");
    die(json_encode(['success' => false, 'message' => 'Invalid order ID']));
}

// Start transaction
$conn->begin_transaction();

try {
    // First delete order items
    $stmt = $conn->prepare("DELETE FROM Order_Items WHERE Order_ID = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    // Then delete the order
    $stmt = $conn->prepare("DELETE FROM `Order` WHERE Order_ID = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'message' => 'Error deleting order']);
}

$conn->close();
?>