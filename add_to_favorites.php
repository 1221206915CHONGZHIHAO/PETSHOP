<?php
session_start();
require_once 'db_connection.php'; // Your database connection file

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'require_login' => true]);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if already in wishlist
$check_sql = "SELECT * FROM wishlist WHERE Customer_ID = ? AND product_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
$stmt->execute();
$result = $stmt->get_result();
$exists = ($result->num_rows > 0);
$stmt->close();

if ($action === 'add') {
    if ($exists) {
        echo json_encode(['success' => false, 'already_added' => true]);
        exit;
    }
    
    $insert_sql = "INSERT INTO wishlist (Customer_ID, product_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
} else {
    if (!$exists) {
        echo json_encode(['success' => false, 'message' => 'Product not in wishlist']);
        exit;
    }
    
    $delete_sql = "DELETE FROM wishlist WHERE Customer_ID = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
    $success = $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => $success]);
}
?>