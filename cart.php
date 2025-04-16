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
                if (isset($_SESSION['Customer_ID'])) {
                    // First check if this item already exists in the cart
                    $check_stmt = $conn->prepare("SELECT Cart_ID FROM cart WHERE Customer_ID = ? AND Inventory_ID = ?");
                    $check_stmt->bind_param("ii", $_SESSION['Customer_ID'], $product_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        // Update existing cart item
                        $cart_row = $check_result->fetch_assoc();
                        $update_stmt = $conn->prepare("UPDATE cart SET Quantity = ? WHERE Cart_ID = ?");
                        $update_stmt->bind_param("ii", $quantity, $cart_row['Cart_ID']);
                        $update_stmt->execute();
                    } else {
                        // Insert new cart item
                        $insert_stmt = $conn->prepare("INSERT INTO cart (Customer_ID, Inventory_ID, Price, Quantity) VALUES (?, ?, ?, ?)");
                        $insert_stmt->bind_param("iddi", $_SESSION['Customer_ID'], $product_id, $product['price'], $quantity);
                        $insert_stmt->execute();
                    }
                }
                
                $success_message = 'Cart updated successfully';
                break;
                
            case 'remove':
                // Remove from session
                if (isset($_SESSION['cart'][$product_id])) {
                    unset($_SESSION['cart'][$product_id]);
                }
                
                // Remove from database if user is logged in
                if (isset($_SESSION['Customer_ID'])) {
                    $stmt = $conn->prepare("DELETE FROM cart WHERE Customer_ID = ? AND Inventory_ID = ?");
                    $stmt->bind_param("ii", $_SESSION['Customer_ID'], $product_id);
                    $stmt->execute();
                }
                
                $success_message = 'Item removed from cart';
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
        exit;
    }
    
    // If not AJAX, redirect to avoid form resubmission
    header('Location: cart.php' . (!empty($error_message) ? '?error=' . urlencode($error_message) : ''));
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
    if (isset($_SESSION['Customer_ID'])) {
        // Get cart items from database
        $stmt = $conn->prepare("
            SELECT c.Inventory_ID as product_id, c.Quantity as quantity, c.Price as price,
                  p.product_name as name, p.stock_quantity as stock, p.image_url as image 
            FROM cart c
            JOIN products p ON c.Inventory_ID = p.product_id
            WHERE c.Customer_ID = ?
        ");
        $stmt->bind_param("i", $_SESSION['Customer_ID']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Create or update session cart from database items
        $_SESSION['cart'] = [];
        
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
        }
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
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark custom-nav">
  <div class="container">
    <!-- Brand on the left -->
    <a class="navbar-brand" href="userhomepage.php">
      <img src="cat_paw.png" alt="Pet Shop" width="50">
      <span>Hachi Pet Shop</span>
    </a>
    
    <!-- Toggler for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main nav links centered -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <!-- Icons on the right -->
      <ul class="navbar-nav ms-auto">
        <!-- Search Icon with Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-search" style="font-size: 1.2rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="searchDropdown" style="min-width: 250px;">
            <form class="d-flex">
              <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
              <button class="btn btn-primary" type="submit">Go</button>
            </form>
          </ul>
        </li>

        <!-- Cart Icon: Active since we're on the cart page -->
        <li class="nav-item">
          <a class="nav-link position-relative active" href="cart.php">
            <i class="bi bi-cart" style="font-size: 1.2rem;"></i>
            <?php if ($cart_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
              <?php echo $cart_count; ?>
            </span>
            <?php endif; ?>
          </a>
        </li>

        <!-- User Icon with Dynamic Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person" style="font-size: 1.2rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- If user is logged in, show username and account links -->
              <li class="dropdown-item-text">
                <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
              </li>
              <li><a class="dropdown-item" href="account_setting.php">Account Settings</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php else: ?>
              <!-- If not logged in, show login/register links -->
              <li><a class="dropdown-item" href="login.php">Login</a></li>
              <li><a class="dropdown-item" href="register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h1 class="mb-4">Your Shopping Cart</h1>
  
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
  <div id="cart-empty-message" class="alert alert-info text-center" role="alert">
    Your shopping cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>
  </div>
  <?php else: ?>
  <div id="cart-content">
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
              <button type="submit" class="btn btn-danger btn-remove">Remove</button>
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
        <div class="d-flex justify-content-end">
          <h4>Total: RM<span id="cart-total"><?php echo number_format($cart_total, 2); ?></span></h4>
        </div>
        <div class="text-end mt-4">
          <a href="products.php" class="btn btn-secondary me-2">Continue Shopping</a>
          <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

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
      if (!confirm('Are you sure you want to remove this item from your cart?')) {
        e.preventDefault();
      }
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