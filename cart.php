<?php
session_start();

// Initialize cart items array
$cart_items = [];
$error_message = '';
$success_message = '';

// Process cart actions if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Connect to database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "petshop";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = $_POST['action'];
    
    // Initialize cart in session if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    try {
        switch ($action) {
          case 'update':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
            if ($quantity < 1) {
                throw new Exception('Quantity must be at least 1');
            }
            
            // Check if product exists and has enough stock
            $stmt = $conn->prepare("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Product not found');
            }
            
            $product = $result->fetch_assoc();
            
            if ($quantity > $product['stock_quantity']) {
                throw new Exception('Not enough stock available');
            }
            
            // Update cart in session
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            
            // Update cart in database if user is logged in
            if (isset($_SESSION['customer_id'])) {
                // First check if this item already exists in the cart
                $check_stmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Customer_ID = ? AND Inventory_ID = ?");
                $check_stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing cart item
                    $cart_row = $check_result->fetch_assoc();
                    $update_stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
                    $update_stmt->bind_param("ii", $quantity, $cart_row['Cart_ID']);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Insert new cart item
                    $insert_stmt = $conn->prepare("INSERT INTO cart (Customer_ID, Inventory_ID, Price, Quantity) VALUES (?, ?, ?, ?)");
                    $insert_stmt->bind_param("iidi", $_SESSION['customer_id'], $product_id, $product['price'], $quantity);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
                $check_stmt->close();
            }
            
            $success_message = 'Cart updated successfully';
            break;
                
            case 'remove':
                // Remove from session
                if (isset($_SESSION['cart'][$product_id])) {
                    unset($_SESSION['cart'][$product_id]);
                }
                
                // Remove from database if user is logged in
                if (isset($_SESSION['customer_id'])) {
                    $stmt = $conn->prepare("DELETE FROM cart WHERE Customer_ID = ? AND Inventory_ID = ?");
                    $stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
                    $stmt->execute();
                    if ($stmt->affected_rows === 0) {
                        $error_message = 'Failed to remove item from database: No matching record found';
                    } else {
                        $success_message = 'Item removed from cart';
                    }
                    $stmt->close();
                } else {
                    $success_message = 'Item removed from cart';
                }
                
                // Update cart count in session
                $cart_count = count($_SESSION['cart']);
                $_SESSION['cart_count'] = $cart_count;
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
    
    // If this was an AJAX request, return JSON response and exit
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        $cart_count = count($_SESSION['cart']);
        $response = [
            'success' => empty($error_message),
            'message' => empty($error_message) ? $success_message : $error_message,
            'cart_count' => $cart_count
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        $conn->close();
        exit;
    }
    
    // If not AJAX, redirect to avoid form resubmission
    header('Location: cart.php' . (!empty($error_message) ? '?error=' . urlencode($error_message) : ''));
    $conn->close();
    exit;
}

// Load cart items for display
// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Check URL parameters for messages
    if (isset($_GET['error'])) {
        $error_message = $_GET['error'];
    }
    if (isset($_GET['success'])) {
        $success_message = $_GET['success'];
    }
    
    // If user is logged in, sync with database cart
    if (isset($_SESSION['customer_id'])) {
        // Get cart items from database
        $stmt = $conn->prepare("
            SELECT c.Inventory_ID as product_id, c.Quantity as quantity, c.Price as price,
                  p.product_name as name, p.stock_quantity as stock, p.image_url as image 
            FROM cart c
            JOIN products p ON c.Inventory_ID = p.product_id
            WHERE c.Customer_ID = ?
        ");
        $stmt->bind_param("i", $_SESSION['customer_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Create or update session cart from database items
        $_SESSION['cart'] = [];
        $cart_count = 0;
        
        while ($item = $result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'name' => $item['name'],
                'image' => $item['image'],
                'stock' => $item['stock']
            ];
            $cart_count++;
        }
        $_SESSION['cart_count'] = $cart_count;
        $stmt->close();
    }
    
    // Get cart items from session regardless if logged in or not
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Get all product IDs in cart
        $product_ids = array_keys($_SESSION['cart']);
        
        if (!empty($product_ids)) {
            $ids_string = implode(',', array_fill(0, count($product_ids), '?'));
            
            // Build type string for bind_param
            $types = str_repeat('i', count($product_ids));
            
            // Prepare query to get current product info
            $stmt = $conn->prepare("
                SELECT product_id, product_name as name, price, stock_quantity as stock, image_url as image 
                FROM products 
                WHERE product_id IN ($ids_string)
            ");
            
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Build cart items array with current product data
            $cart_items = [];
            while ($product = $result->fetch_assoc()) {
                $product_id = $product['product_id'];
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'image' => $product['image'],
                    'quantity' => $_SESSION['cart'][$product_id]['quantity']
                ];
                
                // Update session cart with latest product info
                $_SESSION['cart'][$product_id]['name'] = $product['name'];
                $_SESSION['cart'][$product_id]['price'] = $product['price'];
                $_SESSION['cart'][$product_id]['stock'] = $product['stock'];
                $_SESSION['cart'][$product_id]['image'] = $product['image'];
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    $error_message = "Error loading cart: " . $e->getMessage();
}

// Calculate cart total
$cart_total = 0;
foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

// Count items in cart
$cart_count = count($cart_items);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Shopping Cart</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Base structure - This ensures the footer stays at the bottom */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Main content wrapper - This will push the footer down */
    .page-content {
      flex: 1 0 auto;
      display: flex;
      flex-direction: column;
      padding-top: 80px; /* Account for fixed navbar height */
    }

    /* Main content should take up remaining space */
    main {
      flex: 1 0 auto;
    }

    /* Footer should stick to the bottom and never overlap content */
    footer {
      flex-shrink: 0;
      margin-top: auto;
      background: linear-gradient(to bottom, rgb(134, 138, 135), rgba(46, 21, 1, 0.69));
      width: 100%;
    }

    /* Empty cart styles */
    .empty-cart-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 300px;
    }

    /* Cart styles */
    .cart-container {
      background: linear-gradient(to bottom, #ffffff, #f8f9fa);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      padding: 2rem;
      transition: all 0.3s ease;
    }
    .cart-container:hover {
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    .table {
      border-radius: 8px;
      overflow: hidden;
      background: #ffffff;
    }
    .table th {
      background-color: var(--primary);
      color: white;
      font-weight: 600;
    }
    .table td {
      vertical-align: middle;
    }
    .quantity-group .btn {
      background-color: var(--light-gray);
      border-color: var(--gray);
      color: var(--dark);
      transition: all 0.3s ease;
    }
    .quantity-group .btn:hover {
      background-color: var(--primary);
      border-color: var(--primary);
      color: white;
    }
    .btn-remove {
      background-color: var(--accent);
      border-color: var(--accent);
      transition: all 0.3s ease;
    }
    .btn-remove:hover {
      background-color: rgb(230, 39, 39);
      border-color: #e66b27;
      transform: translateY(-2px);
    }
    .alert {
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }
    .total-section {
      background-color: var(--light-gray);
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 2rem;
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <div class="container">
      <!-- Brand on the left -->
      <a class="navbar-brand" href="userhomepage.php">
        <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
      </a>
      
      <!-- Toggler for mobile view -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Main nav links centered -->
        <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'userhomepage.php' ? 'active' : ''; ?>" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about_us.php' ? 'active' : ''; ?>" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact_us.php' ? 'active' : ''; ?>" href="contact_us.php">Contact Us</a></li>
        </ul>

        <!-- Icons on the right -->
        <ul class="navbar-nav ms-auto nav-icons">
          <!-- Search Icon with Dropdown - Modified to redirect to products.php -->
          <li class="nav-item dropdown">
            <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-search"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end search-dropdown" aria-labelledby="searchDropdown">
              <form class="d-flex search-form" action="products.php" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search" required>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
              </form>
            </ul>
          </li>

          <!-- Cart Icon with item count -->
          <li class="nav-item">
            <a class="nav-link position-relative" href="cart.php">
              <i class="bi bi-cart"></i>
              <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                  <?php echo htmlspecialchars($_SESSION['cart_count']); ?>
                </span>
              <?php endif; ?>
            </a>
          </li>

          <!-- User Icon with Dynamic Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center <?php echo in_array(basename($_SERVER['PHP_SELF']), ['user_dashboard.php', 'my_orders.php', 'favorites.php', 'myprofile_address.php']) ? 'active' : ''; ?>" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
              <?php else: ?>
                <i class="bi bi-person"></i>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : ''; ?>" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'my_orders.php' ? 'active' : ''; ?>" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active' : ''; ?>" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'myprofile_address.php' ? 'active' : ''; ?>" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              <?php else: ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="login.php">Login</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="register.php">Register</a></li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<!-- Page Content Wrapper -->
<div class="page-content">
  <main class="container py-5">
    <h1 class="section-title mb-4">Your Shopping Cart</h1>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success" role="alert">
      <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if (empty($cart_items)): ?>
    <div id="cart-empty-message" class="alert alert-info text-center empty-cart-container">
      <div>
        <i class="bi bi-cart-x" style="font-size: 4rem; color: #17a2b8; margin-bottom: 1rem;"></i>
        <h4>Your shopping cart is empty</h4>
        <p class="mb-4">Looks like you haven't added any products to your cart yet.</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
      </div>
    </div>
    <?php else: ?>
    <div id="cart-content" class="cart-container">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th scope="col" style="width: 80px;">Product</th>
            <th scope="col">Name</th>
            <th scope="col" style="width: 100px;">Price</th>
            <th scope="col" style="width: 150px;">Quantity</th>
            <th scope="col" style="width: 100px;">Subtotal</th>
            <th scope="col" style="width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody id="cart-items">
          <?php foreach($cart_items as $item): ?>
          <tr data-id="<?php echo $item['product_id']; ?>">
            <td>
              <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid" style="max-height: 80px;">
            </td>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td class="price" data-price="<?php echo $item['price']; ?>">RM<?php echo number_format($item['price'], 2); ?></td>
            <td>
              <div class="input-group quantity-group">
                <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
                <input type="number" class="form-control text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
              </div>
            </td>
            <td class="subtotal">RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            <td>
              <form method="post" class="remove-form">
                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit" class="btn btn-remove">Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="row">
        <div class="col-md-6">
          <div class="alert alert-success update-message d-none">
            Cart updated successfully!
          </div>
        </div>
        <div class="col-md-6">
          <div class="total-section text-end">
            <h4>Total: RM<span id="cart-total"><?php echo number_format($cart_total, 2); ?></span></h4>
            <div class="mt-4">
              <a href="products.php" class="btn btn-secondary me-2">Continue Shopping</a>
              <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="row">
      <!-- Footer About -->
      <div class="col-md-5 mb-4 mb-lg-0">
        <div class="footer-about">
          <div class="footer-logo">
            <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
          </div>
          <p>Your trusted partner in pet care since 2015. We're dedicated to providing quality products and exceptional service for pet lovers everywhere.</p>
          <div class="social-links">
            <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
      </div>
      
      <!-- Contact Info -->
      <div class="col-md-7">
                    <h4 class="footer-title">Contact Us</h4>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo !empty($shopSettings['address']) ? htmlspecialchars($shopSettings['address']) : 'Address not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-telephone"></i>
                                <span><?php echo !empty($shopSettings['phone_number']) ? htmlspecialchars($shopSettings['phone_number']) : 'Phone number not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-envelope"></i>
                                <span><?php echo !empty($shopSettings['contact_email']) ? htmlspecialchars($shopSettings['contact_email']) : 'Email not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-clock"></i>
                                <span><?php echo !empty($shopSettings['opening_hours']) ? htmlspecialchars($shopSettings['opening_hours']) : 'Opening hours not available'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px;">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Cart Operation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Increase quantity button
    document.querySelectorAll('.btn-increase').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            const currentVal = parseInt(quantityInput.value);
            const maxVal = quantityInput.hasAttribute('max') ? parseInt(quantityInput.getAttribute('max')) : 99;
            
            if (currentVal < maxVal) {
                quantityInput.value = currentVal + 1;
                updateRowSubtotal(row);
                updateCartTotal();
                updateCartInDatabase(row);
            } else {
                alert('Sorry, we only have ' + maxVal + ' in stock.');
            }
        });
    });

    // Decrease quantity button
    document.querySelectorAll('.btn-decrease').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            const currentVal = parseInt(quantityInput.value);
            
            if (currentVal > 1) {
                quantityInput.value = currentVal - 1;
                updateRowSubtotal(row);
                updateCartTotal();
                updateCartInDatabase(row);
            }
        });
    });

    // Quantity input direct change
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            const maxVal = this.hasAttribute('max') ? parseInt(this.getAttribute('max')) : 99;
            let value = parseInt(this.value);
            
            // Validate input
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > maxVal) {
                value = maxVal;
                alert('Sorry, we only have ' + maxVal + ' in stock.');
            }
            
            this.value = value;
            updateRowSubtotal(row);
            updateCartTotal();
            updateCartInDatabase(row);
        });
    });

    // Remove product button (with confirmation)
    document.querySelectorAll('.remove-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'cart.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            form.closest('tr').remove();
                            updateCartTotal();
                            showUpdateMessage(response.message);
                            // Update cart badge
                            const cartBadge = document.querySelector('.nav-link[href="cart.php"] .badge');
                            if (cartBadge) {
                                cartBadge.textContent = response.cart_count;
                                if (response.cart_count === 0) {
                                    cartBadge.remove();
                                    document.getElementById('cart-content').innerHTML = `
                                        <div id="cart-empty-message" class="alert alert-info text-center" role="alert">
                                            Your shopping cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>
                                        </div>`;
                                }
                            }
                        } else {
                            alert('Error: ' + response.message);
                            // Reload the page to sync with database if removal failed
                            window.location.reload();
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error processing request');
                    }
                } else {
                    console.error('Request failed with status:', xhr.status);
                    alert('Network error occurred');
                }
            };

            xhr.onerror = function() {
                console.error('Network error occurred');
                alert('Network error occurred');
            };

            xhr.send(formData);
        });
    });

    // Update a single row's subtotal
    function updateRowSubtotal(row) {
        const price = parseFloat(row.querySelector('.price').getAttribute('data-price'));
        const quantity = parseInt(row.querySelector('.quantity-input').value);
        const subtotalElem = row.querySelector('.subtotal');
        const subtotal = price * quantity;
        subtotalElem.textContent = 'RM' + subtotal.toFixed(2);
    }

    // Update the cart total
    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('#cart-items tr').forEach(row => {
            const price = parseFloat(row.querySelector('.price').getAttribute('data-price'));
            const quantity = parseInt(row.querySelector('.quantity-input').value);
            total += price * quantity;
        });
        document.getElementById('cart-total').textContent = total.toFixed(2);
    }

    // Show update message
    function showUpdateMessage(message) {
        const messageElem = document.querySelector('.update-message');
        messageElem.textContent = message;
        messageElem.classList.remove('d-none');
        
        setTimeout(() => {
            messageElem.classList.add('d-none');
        }, 3000);
    }

    // Update cart in database with AJAX
    function updateCartInDatabase(row) {
        const productId = row.getAttribute('data-id');
        const quantity = row.querySelector('.quantity-input').value;
        
        // Create form data
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        formData.append('action', 'update');
        
        // Create XMLHttpRequest
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'cart.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showUpdateMessage('Cart updated successfully');
                    } else {
                        alert('Error updating cart: ' + response.message);
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        
        xhr.onerror = function() {
            console.error('Network error occurred');
        };
        
        xhr.send(formData);
    }
});
</script>
</body>
</html>