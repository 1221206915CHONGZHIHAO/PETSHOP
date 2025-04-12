<?php
// Start the session
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0
];

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

// Check if user is logged in
if (!isset($_SESSION['Customer_ID'])) {
    // If not logged in, we'll store the cart in session
    handleSessionCart($product_id, $quantity, $response);
} else {
    // If logged in, we'll store the cart in database
    handleDatabaseCart($product_id, $quantity, $_SESSION['Customer_ID'], $response);
}

// Return JSON response
echo json_encode($response);
exit;

// Function to handle session-based cart
function handleSessionCart($product_id, $quantity, &$response) {
    // Database connection to get product details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "petshop";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed';
        return;
    }
    
    // Check if product exists and get details
    $stmt = $conn->prepare("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Product not found';
        $stmt->close();
        $conn->close();
        return;
    }
    
    $product = $result->fetch_assoc();
    
    // Check if product is in stock
    if ($product['stock_quantity'] < $quantity) {
        $response['message'] = 'Not enough stock available';
        $stmt->close();
        $conn->close();
        return;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    $product_in_cart = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] === $product_id) {
            // Update quantity
            $item['quantity'] += $quantity;
            $product_in_cart = true;
            break;
        }
    }
    
    // If product not in cart, add it
    if (!$product_in_cart) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'product_name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    // Close database connection
    $stmt->close();
    $conn->close();
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully';
    $response['cart_count'] = count($_SESSION['cart']);
}

// Function to handle database cart for logged-in users
function handleDatabaseCart($product_id, $quantity, $customer_id, &$response) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "petshop";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed';
        return;
    }
    
    // Check if product exists and get details
    $stmt = $conn->prepare("SELECT product_id, price, stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Product not found';
        $stmt->close();
        $conn->close();
        return;
    }
    
    $product = $result->fetch_assoc();
    
    // Check if product is in stock
    if ($product['stock_quantity'] < $quantity) {
        $response['message'] = 'Not enough stock available';
        $stmt->close();
        $conn->close();
        return;
    }
    
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
        $update_cart->bind_param("di", $new_quantity, $cart_item['Cart_ID']);
        $update_cart->execute();
        
        if ($update_cart->affected_rows <= 0) {
            $response['message'] = 'Failed to update cart';
            $check_cart->close();
            $update_cart->close();
            $stmt->close();
            $conn->close();
            return;
        }
        
        $update_cart->close();
    } else {
        // Add new cart item
        $insert_cart = $conn->prepare("INSERT INTO cart (Customer_ID, Inventory_ID, Price, Quantity) VALUES (?, ?, ?, ?)");
        $insert_cart->bind_param("iidd", $customer_id, $product_id, $product['price'], $quantity);
        $insert_cart->execute();
        
        if ($insert_cart->affected_rows <= 0) {
            $response['message'] = 'Failed to add item to cart';
            $check_cart->close();
            $insert_cart->close();
            $stmt->close();
            $conn->close();
            return;
        }
        
        $insert_cart->close();
    }
    
    $check_cart->close();
    
    // Get cart count
    $count_cart = $conn->prepare("SELECT COUNT(*) AS cart_count FROM cart WHERE Customer_ID = ?");
    $count_cart->bind_param("i", $customer_id);
    $count_cart->execute();
    $count_result = $count_cart->get_result();
    $count_row = $count_result->fetch_assoc();
    $cart_count = $count_row['cart_count'];
    
    $count_cart->close();
    $stmt->close();
    $conn->close();
    
    // Set success response
    $response['success'] = true;
    $response['message'] = 'Product added to cart successfully';
    $response['cart_count'] = $cart_count;
    
    // Store cart count in session for display
    $_SESSION['cart_count'] = $cart_count;
}
?>