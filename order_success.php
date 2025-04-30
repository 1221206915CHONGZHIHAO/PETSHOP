<?php
session_start();

if (!isset($_GET['order_id'])) {
    header('Location: products.php');
    exit;
}

$order_id = intval($_GET['order_id']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.Order_ID, o.order_date, o.Total, o.PaymentMethod, 
           COUNT(oi.order_item_id) as item_count 
    FROM Orders o
    JOIN Order_Items oi ON o.Order_ID = oi.order_id
    WHERE o.Order_ID = ? AND o.Customer_ID = ?
    GROUP BY o.Order_ID
");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

$conn->close();

if (!$order) {
    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Order Confirmation</title>
  <!-- Your existing head content from cart.php -->
  <style>
    .success-icon {
      font-size: 5rem;
      color: #28a745;
      margin-bottom: 1rem;
    }
    .order-details {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1.5rem;
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
    <div class="text-center">
      <i class="bi bi-check-circle success-icon"></i>
      <h1 class="mb-3">Thank You for Your Order!</h1>
      <p class="lead mb-4">Your order has been placed successfully.</p>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="order-details">
          <h3 class="mb-4">Order Details</h3>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <p><strong>Order Number:</strong> #<?php echo $order['Order_ID']; ?></p>
              <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
            </div>
            <div class="col-md-6">
              <p><strong>Total Amount:</strong> RM<?php echo number_format($order['Total'], 2); ?></p>
              <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['PaymentMethod']); ?></p>
            </div>
          </div>
          
          <p>We've sent an order confirmation email to <?php echo htmlspecialchars($_SESSION['Customer_email']); ?></p>
          <p>You can track your order in <a href="my_orders.php">My Orders</a>.</p>
        </div>
        
        <div class="text-center mt-4">
          <a href="products.php" class="btn btn-primary">Continue Shopping</a>
          <a href="my_orders.php" class="btn btn-outline-secondary ms-2">View My Orders</a>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Footer (same as cart.php) -->
<footer>
    <!-- Your existing footer code from cart.php -->
</footer>

<!-- Bootstrap JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>