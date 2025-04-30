<?php
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) {
    header('Location: cart.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error_message = '';
$success_message = '';
$cart_items = [];
$cart_total = 0;
$customer_details = [];
$shipping_address = '';

// Get customer details if logged in
if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT * FROM Customer WHERE Customer_ID = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer_details = $result->fetch_assoc();
    $stmt->close();
    
    // Set default shipping address
    if (!empty($customer_details['address'])) {
        $shipping_address = $customer_details['address'];
    }
}

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate form data
        $required_fields = [
            'shipping_address' => 'Shipping Address',
            'payment_method' => 'Payment Method',
            'card_name' => 'Name on Card',
            'card_number' => 'Card Number',
            'card_expiry' => 'Card Expiry',
            'card_cvv' => 'CVV'
        ];
        
        foreach ($required_fields as $field => $name) {
            if (empty($_POST[$field])) {
                throw new Exception("$name is required");
            }
        }
        
        // Basic card validation
        $card_number = preg_replace('/\D/', '', $_POST['card_number']);
        if (strlen($card_number) < 13 || strlen($card_number) > 19) {
            throw new Exception("Invalid card number");
        }
        
        $expiry = explode('/', $_POST['card_expiry']);
        if (count($expiry) != 2 || !checkdate($expiry[0], 1, $expiry[1])) {
            throw new Exception("Invalid expiry date (MM/YY format required)");
        }
        
        if (!preg_match('/^\d{3,4}$/', $_POST['card_cvv'])) {
            throw new Exception("Invalid CVV");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO Orders 
                (Customer_ID, total_amount, shipping_address, billing_address, payment_method) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $total_amount = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            $shipping_address = $_POST['shipping_address'];
            $billing_address = isset($_POST['same_as_shipping']) ? $shipping_address : $_POST['billing_address'];
            $payment_method = $_POST['payment_method'];
            
            $stmt->bind_param(
                "idsss", 
                $_SESSION['customer_id'], 
                $total_amount, 
                $shipping_address, 
                $billing_address, 
                $payment_method
            );
            
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Add order items
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt = $conn->prepare("
                    INSERT INTO Order_Items 
                    (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bind_param(
                    "iiidd", 
                    $order_id, 
                    $product_id, 
                    $item['quantity'], 
                    $item['price'], 
                    $subtotal
                );
                
                $stmt->execute();
                $stmt->close();
                
                // Update product stock
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE product_id = ?
                ");
                $stmt->bind_param("ii", $item['quantity'], $product_id);
                $stmt->execute();
                $stmt->close();
            }
            
            // Record payment (in a real system, this would integrate with a payment gateway)
            $stmt = $conn->prepare("
                INSERT INTO Payments 
                (order_id, customer_id, amount, payment_method, payment_status) 
                VALUES (?, ?, ?, ?, 'Completed')
            ");
            $stmt->bind_param(
                "iids", 
                $order_id, 
                $_SESSION['customer_id'], 
                $total_amount, 
                $payment_method
            );
            $stmt->execute();
            $stmt->close();
            
            // Clear cart
            if (isset($_SESSION['customer_id'])) {
                $stmt = $conn->prepare("DELETE FROM Cart WHERE Customer_ID = ?");
                $stmt->bind_param("i", $_SESSION['customer_id']);
                $stmt->execute();
                $stmt->close();
            }
            unset($_SESSION['cart']);
            $_SESSION['cart_count'] = 0;
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Load cart items for display
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    
    $stmt = $conn->prepare("
        SELECT product_id, product_name as name, price, image_url as image 
        FROM products 
        WHERE product_id IN ($ids_string)
    ");
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $product_id = $product['product_id'];
        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $_SESSION['cart'][$product_id]['quantity']
        ];
        $cart_total += $product['price'] * $_SESSION['cart'][$product_id]['quantity'];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Checkout</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    .checkout-container {
      background: linear-gradient(to bottom, #ffffff, #f8f9fa);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      padding: 2rem;
    }
    .payment-methods img {
      height: 30px;
      margin-right: 10px;
    }
    .order-summary {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1.5rem;
    }
    .card-element {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }
  </style>
</head>
<body>
<!-- Navigation (same as cart.php) -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <!-- Your existing navigation code from cart.php -->
</nav>

<!-- Page Content -->
<div class="page-content">
  <main class="container py-5">
    <h1 class="section-title mb-4">Checkout</h1>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <form method="post" id="checkout-form">
      <div class="row">
        <div class="col-lg-8">
          <div class="checkout-container mb-4">
            <h3 class="mb-4">Shipping Information</h3>
            
            <div class="mb-3">
              <label for="shipping_address" class="form-label">Shipping Address</label>
              <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
            </div>
            
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="same_as_shipping" name="same_as_shipping" checked>
              <label class="form-check-label" for="same_as_shipping">
                Billing address same as shipping address
              </label>
            </div>
            
            <div id="billing_address_container" class="mb-3" style="display: none;">
              <label for="billing_address" class="form-label">Billing Address</label>
              <textarea class="form-control" id="billing_address" name="billing_address" rows="3"></textarea>
            </div>
          </div>
          
          <div class="checkout-container">
            <h3 class="mb-4">Payment Method</h3>
            
            <div class="payment-methods mb-4">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="Credit Card" checked>
                <label class="form-check-label" for="credit_card">
                  <img src="https://cdn-icons-png.flaticon.com/512/179/179457.png" alt="Visa">
                  <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Mastercard">
                  Credit Card
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal">
                <label class="form-check-label" for="paypal">
                  <img src="https://cdn-icons-png.flaticon.com/512/174/174861.png" alt="PayPal">
                  PayPal
                </label>
              </div>
            </div>
            
            <div id="credit_card_details">
              <div class="mb-3">
                <label for="card_name" class="form-label">Name on Card</label>
                <input type="text" class="form-control" id="card_name" name="card_name" required>
              </div>
              
              <div class="mb-3">
                <label for="card_number" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="card_expiry" class="form-label">Expiration Date (MM/YY)</label>
                  <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="card_cvv" class="form-label">CVV</label>
                  <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123" required>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="order-summary">
            <h3 class="mb-4">Order Summary</h3>
            
            <div class="mb-3">
              <?php foreach($cart_items as $item): ?>
              <div class="d-flex justify-content-between mb-2">
                <div>
                  <?php echo htmlspecialchars($item['name']); ?> 
                  <span class="text-muted">x<?php echo $item['quantity']; ?></span>
                </div>
                <div>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between mb-2">
              <div>Subtotal</div>
              <div>RM<?php echo number_format($cart_total, 2); ?></div>
            </div>
            
            <div class="d-flex justify-content-between mb-2">
              <div>Shipping</div>
              <div>Free</div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between mb-4">
              <h5>Total</h5>
              <h5>RM<?php echo number_format($cart_total, 2); ?></h5>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2">Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </main>
</div>

<!-- Footer (same as cart.php) -->
<footer>
    <!-- Your existing footer code from cart.php -->
</footer>

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Checkout Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle billing address
    document.getElementById('same_as_shipping').addEventListener('change', function() {
        const billingContainer = document.getElementById('billing_address_container');
        billingContainer.style.display = this.checked ? 'none' : 'block';
        if (!this.checked) {
            document.getElementById('billing_address').required = true;
        } else {
            document.getElementById('billing_address').required = false;
        }
    });
    
    // Toggle payment methods
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('credit_card_details').style.display = 
                this.value === 'Credit Card' ? 'block' : 'none';
        });
    });
    
    // Format card number
    document.getElementById('card_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '')
            .replace(/(\d{4})(?=\d)/g, '$1 ');
    });
    
    // Format expiry date
    document.getElementById('card_expiry').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '')
            .replace(/(\d{2})(?=\d)/g, '$1/')
            .substring(0, 5);
    });
    
    // Format CVV
    document.getElementById('card_cvv').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });
});
</script>
</body>
</html>