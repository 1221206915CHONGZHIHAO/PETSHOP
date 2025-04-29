<?php
// Start the session
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0,
    'require_login' => false
];

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    $response['success'] = false;
    $response['message'] = 'Please login or sign up first to add items to your cart';
    $response['require_login'] = true;
    echo json_encode($response);
    exit;
}

// Check if product_id is set
if (!isset($_POST['product_id'])) {
    $response['message'] = 'Product ID is required';
    echo json_encode($response);
    exit;
}

// Get product ID and quantity
$product_id = intval($_POST['product_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Validate quantity
if ($quantity <= 0) {
    $response['message'] = 'Quantity must be greater than zero';
    echo json_encode($response);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

// Check if product exists and get details
$stmt = $conn->prepare("SELECT product_id, product_name, price, stock_quantity, image_url FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Product not found';
    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit;
}

$product = $result->fetch_assoc();

// Check if product is in stock
if ($product['stock_quantity'] < $quantity) {
    $response['message'] = 'Not enough stock available';
    $stmt->close();
    $conn->close();
    echo json_encode($response);
    exit;
}

// Since we've already confirmed user is logged in, handle cart in database
handleDatabaseCart($product, $quantity, $_SESSION['customer_id'], $conn, $response);

// Close connection
$conn->close();

// Return JSON response
echo json_encode($response);
exit;

// Function to handle database cart for logged-in users - updated to handle cart count correctly
function handleDatabaseCart($product, $quantity, $customer_id, $conn, &$response) {
    $product_id = $product['product_id'];
    $price = $product['price'];
    
    // Check if product already in cart
    $check_cart = $conn->prepare("SELECT Cart_ID, Quantity FROM cart WHERE Customer_ID = ? AND Inventory_ID = ?");
    $check_cart->bind_param("ii", $customer_id, $product_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();
    
    if ($cart_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['Quantity'] + $quantity;
        
        $update_cart = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
        $update_cart->bind_param("ii", $new_quantity, $cart_item['Cart_ID']);
        $update_cart->execute();
        
        if ($update_cart->affected_rows <= 0 && $conn->error) {
            $response['message'] = 'Failed to update cart: ' . $conn->error;
            $check_cart->close();
            $update_cart->close();
            return;
        }
        
        $update_cart->close();
        
        // Update session cart with the TOTAL quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'quantity' => $new_quantity,
                'price' => $price,
                'name' => $product['product_name'],
                'image' => $product['image_url']
            ];
        }
    } else {
        // Add new cart item
        $insert_cart = $conn->prepare("INSERT INTO cart (Customer_ID, Inventory_ID, Price, Quantity) VALUES (?, ?, ?, ?)");
        $insert_cart->bind_param("iddi", $customer_id, $product_id, $price, $quantity);
        $insert_cart->execute();
        
        if ($insert_cart->affected_rows <= 0 && $conn->error) {
            $response['message'] = 'Failed to add item to cart: ' . $conn->error;
            $check_cart->close();
            $insert_cart->close();
            return;
        }
        
        $insert_cart->close();
        
        // Add to session cart
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'name' => $product['product_name'],
            'image' => $product['image_url']
        ];
    }
    
    $check_cart->close();
    
    // Get total cart count (sum of all quantities)
    $count_cart = $conn->prepare("SELECT COUNT(*) AS cart_count FROM cart WHERE Customer_ID = ?");
    $count_cart->bind_param("i", $customer_id);
    $count_cart->execute();
    $count_result = $count_cart->get_result();
    $count_row = $count_result->fetch_assoc();
    $cart_count = $count_row['cart_count'] ? (int)$count_row['cart_count'] : 0;
    $count_cart->close();
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully';
    $response['cart_count'] = $cart_count;
    
    // Store cart count in session for display
    $_SESSION['cart_count'] = $cart_count;
    
    // Initialize session cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}
?>