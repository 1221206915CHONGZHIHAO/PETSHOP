<?php
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
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
    // Get basic customer info
    $stmt = $conn->prepare("SELECT * FROM Customer WHERE Customer_ID = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer_details = $result->fetch_assoc();
    $stmt->close();
    
    // Get default address if available
    $stmt = $conn->prepare("SELECT * FROM customer_address WHERE Customer_ID = ? AND Is_Default = 1");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $address_result = $stmt->get_result();
    
    if ($address_result->num_rows > 0) {
        $default_address = $address_result->fetch_assoc();
        // Format the address more clearly
        $shipping_address = "Recipient: " . $default_address['Full_Name'] . "\n";
        $shipping_address .= "Address Line 1: " . $default_address['Address_Line1'] . "\n";
        if (!empty($default_address['Address_Line2'])) {
            $shipping_address .= "Address Line 2: " . $default_address['Address_Line2'] . "\n";
        }
        $shipping_address .= "City: " . $default_address['City'] . "\n";
        if (!empty($default_address['State'])) {
            $shipping_address .= "State: " . $default_address['State'] . "\n";
        }
        $shipping_address .= "Postal Code: " . $default_address['Postal_Code'] . "\n";
        $shipping_address .= "Country: " . $default_address['Country'] . "\n";
        $shipping_address .= "Phone: " . $default_address['Phone_Number'];
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
        
        // Validate name on card (letters and spaces only)
        if (!preg_match('/^[A-Za-z ]+$/', $_POST['card_name'])) {
            throw new Exception("Name on card can only contain letters and spaces");
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
        
        // CVV validation - exactly 3 digits
        if (!preg_match('/^\d{3}$/', $_POST['card_cvv'])) {
            throw new Exception("CVV must be exactly 3 digits");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO Orders 
                (Customer_ID, Address, PaymentMethod, Total, order_date, status) 
                VALUES (?, ?, ?, ?, NOW(), 'Pending')
            ");
            
            $total_amount = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            $total_amount += 4.90; // Add shipping fee
            
            $address = $_POST['shipping_address'];
            $payment_method = $_POST['payment_method'];
            
            $stmt->bind_param(
                "issd", 
                $_SESSION['customer_id'], 
                $address, 
                $payment_method, 
                $total_amount
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
            
            // Verify order total
            $verify_stmt = $conn->prepare("
                SELECT SUM(subtotal) as calculated_total 
                FROM Order_Items 
                WHERE order_id = ?
            ");
            $verify_stmt->bind_param("i", $order_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $calculated_total = $verify_result->fetch_assoc()['calculated_total'];

            if (abs($calculated_total - ($total_amount - 4.90)) > 0.01) {
                $conn->rollback();
                throw new Exception("Order total verification failed");
            }
            
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
    .order-summary table {
      font-size: 0.9rem;
    }
    .order-summary th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
    .order-summary .total-row th {
      background-color: #e9ecef;
      font-size: 1.1rem;
    }
    .order-summary tfoot tr:not(.total-row) th {
      border-top: 1px dashed #dee2e6;
    }
    .card-element {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }
    .no-refund-alert {
      border-left: 4px solid #ffc107;
    }
    /* Custom Alert Modal Styles */
    .custom-alert-modal {
      font-family: 'Poppins', sans-serif;
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    .custom-alert-modal .modal-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      border-radius: 12px 12px 0 0;
      padding: 1rem 1.5rem;
    }
    .custom-alert-modal .modal-title {
      font-weight: 600;
      color: #333;
    }
    .custom-alert-modal .modal-body {
      padding: 1.5rem;
      color: #555;
    }
    .custom-alert-modal .modal-footer {
      border-top: 1px solid #dee2e6;
      border-radius: 0 0 12px 12px;
      padding: 1rem;
      justify-content: center;
    }
    .custom-alert-modal .btn-cancel {
      background-color: #f8f9fa;
      color: #333;
      border: 1px solid #dee2e6;
      margin-right: 10px;
    }
    .custom-alert-modal .btn-confirm {
      background-color: #007bff;
      color: white;
      border: none;
    }
    .custom-alert-modal .btn-cancel:hover {
      background-color: #e9ecef;
    }
    .custom-alert-modal .btn-confirm:hover {
      background-color: #0069d9;
    }
    .custom-alert-modal .bi-exclamation-triangle {
      color: #ffc107;
      margin-right: 8px;
      font-size: 1.2rem;
    }
    /* Input validation styles */
    .is-invalid {
      border-color: #dc3545 !important;
    }
    .invalid-feedback {
      display: none;
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
    }
    .was-validated .form-control:invalid ~ .invalid-feedback {
      display: block;
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <!-- Your navigation code here -->
</nav>

<!-- Page Content -->
<div class="page-content">
  <main class="container py-5">
    <h1 class="section-title mb-4">Checkout</h1>
    
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger" id="server-error">
        <?php echo htmlspecialchars($error_message); ?>
      </div>
    <?php endif; ?>
    <div class="alert alert-danger d-none" id="client-error"></div>
    
    <form method="post" id="checkout-form" class="needs-validation" novalidate>
      <div class="row">
        <div class="col-lg-8">
          <div class="checkout-container mb-4">
            <h3 class="mb-4">Shipping Information</h3>
            
            <div class="mb-3">
                <label for="shipping_address" class="form-label">Shipping Address</label>
                <?php if (!empty($shipping_address)): ?>
                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="5" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                    <small class="text-muted">This is your default address from your profile</small>
                    <div class="mt-2">
                        <a href="myprofile_address.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Address
                        </a>
                    </div>
                <?php else: ?>
                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="5" required></textarea>
                    <div class="mt-2">
                        <a href="myprofile_address.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus"></i> Add Address
                        </a>
                    </div>
                <?php endif; ?>
                <div class="invalid-feedback">Please provide a shipping address.</div>
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
                  <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Mastercard">
                  Credit Card
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="debit_card" value="Debit Card">
                <label class="form-check-label" for="debit_card">
                  <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Debit Card">
                  Debit Card
                </label>
              </div>
            </div>
            
            <div id="credit_card_details">
              <div class="mb-3">
                <label for="card_name" class="form-label">Name on Card</label>
                <input type="text" class="form-control" id="card_name" name="card_name" 
                       pattern="[A-Za-z ]+" title="Please enter only letters" required>
                <div class="invalid-feedback">Please enter a valid name (letters only).</div>
              </div>
              
              <div class="mb-3">
                <label for="card_number" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="card_number" name="card_number" 
                       placeholder="1234 5678 9012 3456" pattern="[\d ]{13,19}" required>
                <div class="invalid-feedback">Please enter a valid card number (13-19 digits).</div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="card_expiry" class="form-label">Expiration Date (MM/YY)</label>
                  <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                         placeholder="MM/YY" pattern="\d{2}/\d{2}" required>
                  <div class="invalid-feedback">Please enter a valid expiry date (MM/YY format).</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="card_cvv" class="form-label">CVV (3 digits)</label>
                  <input type="text" class="form-control" id="card_cvv" name="card_cvv" 
                         placeholder="123" pattern="\d{3}" maxlength="3" required>
                  <div class="invalid-feedback">Please enter a valid 3-digit CVV.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-4">
          <div class="order-summary">
            <h3 class="mb-4">Order Summary</h3>
            
            <div class="table-responsive mb-3">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($cart_items as $item): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                        <div><?php echo htmlspecialchars($item['name']); ?></div>
                      </div>
                    </td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">RM<?php echo number_format($item['price'], 2); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="2" class="text-end">Subtotal:</th>
                    <th class="text-end">RM<?php echo number_format($cart_total, 2); ?></th>
                  </tr>
                  <tr>
                    <th colspan="2" class="text-end">Shipping:</th>
                    <th class="text-end">RM4.90</th>
                  </tr>
                  <tr class="total-row">
                    <th colspan="2" class="text-end">Total:</th>
                    <th class="text-end">RM<?php echo number_format($cart_total + 4.90, 2); ?></th>
                  </tr>
                </tfoot>
              </table>
            </div>
            
            <div class="alert alert-warning no-refund-alert mb-3">
              <h6><i class="bi bi-exclamation-triangle-fill me-2"></i>No Refund Policy</h6>
              <p class="small mb-0">By placing this order, you acknowledge that all sales are final and no refunds will be issued after payment.</p>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2" id="placeOrderBtn">Place Order</button>
          </div>
        </div>
      </div>
    </form>
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
            <a href="https://www.instagram.com/smal.l7018/"><i class="bi bi-instagram"></i></a>
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

<!-- Custom Alert Modal -->
<div class="modal fade" id="noRefundAlert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-alert-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i>Important Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>By placing this order, you acknowledge our <strong>No Refund Policy</strong>:</p>
                <ul>
                    <li>All sales are final</li>
                    <li>No refunds will be issued after payment</li>
                    <li>No returns or exchanges</li>
                </ul>
                <p>Do you wish to proceed with your order?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">No, Cancel</button>
                <button type="button" class="btn btn-confirm" id="confirmOrderBtn">Yes, Place Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Checkout Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle billing address
    document.getElementById('same_as_shipping').addEventListener('change', function () {
        const billingContainer = document.getElementById('billing_address_container');
        billingContainer.style.display = this.checked ? 'none' : 'block';
        document.getElementById('billing_address').required = !this.checked;
    });

    // Toggle credit/debit card fields
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('credit_card_details').style.display =
                (this.value === 'Credit Card' || this.value === 'Debit Card') ? 'block' : 'none';
        });
    });

    // Restrict name on card to letters only
    const cardNameInput = document.getElementById('card_name');
    cardNameInput.addEventListener('input', function() {
        // Remove any non-letter characters
        this.value = this.value.replace(/[^A-Za-z ]/g, '');
    });

    // Format and limit card number to digits only
    const cardNumberInput = document.getElementById('card_number');
    cardNumberInput.addEventListener('input', function() {
        // Remove all non-digit characters
        let value = this.value.replace(/\D/g, '');
        // Limit to 16 digits
        value = value.substring(0, 16);
        // Add space every 4 digits
        this.value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    });

    // Format expiry (MM/YY)
    const cardExpiryInput = document.getElementById('card_expiry');
    cardExpiryInput.addEventListener('input', function() {
        // Remove all non-digit characters
        let value = this.value.replace(/\D/g, '');
        // Limit to 4 digits (MMYY)
        value = value.substring(0, 4);
        // Add slash after 2 digits
        if (value.length > 2) {
            this.value = value.substring(0, 2) + '/' + value.substring(2);
        } else {
            this.value = value;
        }
    });

    // Format CVV (exactly 3 digits)
    const cardCvvInput = document.getElementById('card_cvv');
    cardCvvInput.addEventListener('input', function() {
        // Remove all non-digit characters and limit to 3 digits
        this.value = this.value.replace(/\D/g, '').substring(0, 3);
    });

    // Handle form submission with custom modal
    document.getElementById('placeOrderBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // First validate the form
        const form = document.getElementById('checkout-form');
        form.classList.add('was-validated');
        
        if (!form.checkValidity()) {
            // Scroll to first invalid field
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            return;
        }
        
        // If validation passed, show the modal
        const modal = new bootstrap.Modal(document.getElementById('noRefundAlert'));
        modal.show();
    });

    // Handle confirm order button
    document.getElementById('confirmOrderBtn').addEventListener('click', function() {
        document.getElementById('checkout-form').submit();
    });

    // Real-time validation for name on card
    cardNameInput.addEventListener('blur', function() {
        if (!/^[A-Za-z ]+$/.test(this.value.trim())) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Real-time validation for card number
    cardNumberInput.addEventListener('blur', function() {
        const digits = this.value.replace(/\D/g, '');
        if (digits.length < 13 || digits.length > 19) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Real-time validation for expiry
    cardExpiryInput.addEventListener('blur', function() {
        const [month, year] = this.value.split('/');
        const now = new Date();
        const currentYear = now.getFullYear() % 100;
        const currentMonth = now.getMonth() + 1;
        
        if (!/^\d{2}\/\d{2}$/.test(this.value) ||
            Number(month) < 1 || Number(month) > 12 ||
            Number(year) < currentYear ||
            (Number(year) === currentYear && Number(month) < currentMonth)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Real-time validation for CVV
    cardCvvInput.addEventListener('blur', function() {
        if (!/^\d{3}$/.test(this.value)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
</body>
</html>