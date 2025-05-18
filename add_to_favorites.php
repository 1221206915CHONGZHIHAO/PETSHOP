<?php
// Start a session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    // User is not logged in, return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'require_login' => true,
        'message' => 'Please login to add items to favorites'
    ]);
    exit;
}

// Get the product ID from POST
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate product ID
if ($product_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

// Check if product already in user's favorites (wishlist)
$check_sql = "SELECT * FROM wishlist WHERE Customer_ID = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Product already in favorites
    $check_stmt->close();
    $conn->close();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'already_added' => true,
        'message' => 'Product already in favorites'
    ]);
    exit;
}

// Add product to wishlist
$insert_sql = "INSERT INTO wishlist (Customer_ID, product_id, added_at) VALUES (?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);

if ($insert_stmt->execute()) {
    $insert_stmt->close();
    $conn->close();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Product added to favorites'
    ]);
} else {
    $insert_stmt->close();
    $conn->close();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add product to favorites'
    ]);
}
?>