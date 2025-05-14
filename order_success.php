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

// Get order details - updated to match your database schema
$stmt = $conn->prepare("
    SELECT o.Order_ID, o.order_date, o.Total, o.PaymentMethod as payment_method, 
           o.status, COUNT(oi.order_item_id) as item_count 
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
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/order_success.css">
  <link rel="stylesheet" href="userhomepage.css">
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <div class="container">
      <a class="navbar-brand" href="userhomepage.php">
        <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
        </ul>

        <ul class="navbar-nav ms-auto nav-icons">
        <li class="nav-item">
  <a class="nav-link" href="cart.php">
    <i class="bi bi-cart"></i>
    <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
        <?php echo $_SESSION['cart_count']; ?>
      </span>
    <?php endif; ?>
  </a>
</li>
          
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
              <?php else: ?>
                <i class="bi bi-person"></i>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="login.php">Login</a></li>
                <li><a class="dropdown-item" href="register.php">Register</a></li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<!-- Page Content -->
<div class="page-content">
  <main class="container py-5">
    <div class="success-container text-center">
      <i class="bi bi-check-circle-fill success-icon"></i>
      <h1 class="mb-3">Thank You for Your Order!</h1>
      <p class="lead mb-4">Your order #<?php echo $order['Order_ID']; ?> has been confirmed.</p>
      
      <div class="order-details">
        <h3><i class="bi bi-receipt me-2"></i>Order Summary</h3>
        
        <div class="row text-start">
          <div class="col-md-6">
            <p><strong>Order Number:</strong> #<?php echo $order['Order_ID']; ?></p>
            <p><strong>Date Placed:</strong> <?php echo date('F j, Y \a\t g:i a', strtotime($order['order_date'])); ?></p>
            <p><strong>Items:</strong> <?php echo $order['item_count']; ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Total Amount:</strong> RM<?php echo number_format($order['Total'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
          </div>
        </div>
        
        <hr class="my-4">
        
        <div class="delivery-info">
          <i class="bi bi-truck me-2"></i>
          <p>We're preparing your order for shipment. You'll receive a confirmation email at <strong><?php echo isset($_SESSION['customer_email']) ? htmlspecialchars($_SESSION['customer_email']) : 'your registered email'; ?></strong> when it's on its way.</p>
        </div>
      </div>
      
      <div class="action-buttons mt-5">
        <a href="products.php" class="btn btn-primary btn-success-page">
          <i class="bi bi-arrow-left me-2"></i>Continue Shopping
        </a>
        <a href="my_orders.php" class="btn btn-outline-secondary btn-success-page">
          <i class="bi bi-list-check me-2"></i>View Order Details
        </a>
      </div>
    </div>
  </main>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-5 mb-4 mb-lg-0">
        <div class="footer-about">
          <div class="footer-logo">
            <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
          </div>
          <p>Your trusted partner in pet care since 2015. We're dedicated to providing quality products and exceptional service for pet lovers everywhere.</p>
          <div class="social-links">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
      </div>
      
      <div class="col-md-7">
        <h4 class="footer-title">Contact Us</h4>
        <div class="row">
          <div class="col-sm-6 mb-3">
            <div class="contact-info">
              <i class="bi bi-geo-alt"></i>
              <span>123 Pet Street, Animal City<br>Singapore 123456</span>
            </div>
          </div>
          <div class="col-sm-6 mb-3">
            <div class="contact-info">
              <i class="bi bi-telephone"></i>
              <span>+65 1234 5678</span>
            </div>
          </div>
          <div class="col-sm-6 mb-3">
            <div class="contact-info">
              <i class="bi bi-envelope"></i>
              <span>info@hachipetshop.com</span>
            </div>
          </div>
          <div class="col-sm-6 mb-3">
            <div class="contact-info">
              <i class="bi bi-clock"></i>
              <span>Mon-Fri: 9am-6pm<br>Sat-Sun: 10am-4pm</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="footer-bottom">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>