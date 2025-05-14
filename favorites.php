<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
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

// Get favorite products for the current customer using wishlist table
$stmt = $conn->prepare("
    SELECT p.product_id, p.product_name, p.price, p.image_url, p.stock_quantity
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.Customer_ID = ?
    ORDER BY w.added_at DESC
");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$favorites = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle remove from favorites
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE Customer_ID = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
    $stmt->execute();
    $stmt->close();
    header("Location: favorites.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - My Favorites</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/order_success.css">
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Additional styles for favorites */
    .favorites-container {
      background: linear-gradient(to bottom, #ffffff, #f8f9fa);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      padding: 3rem;
      margin: 2rem auto;
      transition: all 0.3s ease;
    }
    
    .favorites-container:hover {
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .product-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
      border-left: 4px solid #6c757d;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .product-img {
      height: 150px;
      object-fit: contain;
      margin-bottom: 1rem;
    }
    
    .no-favorites {
      text-align: center;
      padding: 4rem;
    }
    
    .no-favorites-icon {
      font-size: 5rem;
      color: #6c757d;
      margin-bottom: 1.5rem;
    }
    
    .stock-status {
      font-weight: 600;
    }
    
    .in-stock {
      color: #28a745;
    }
    
    .out-of-stock {
      color: #dc3545;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    
    @media (max-width: 768px) {
      .favorites-container {
        padding: 2rem 1rem;
        margin: 1rem;
      }
      
      .action-buttons {
        flex-direction: column;
      }
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
            <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active-dropdown-item' : ''; ?>" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'my_orders.php' ? 'active-dropdown-item' : ''; ?>" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active-dropdown-item' : ''; ?>" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'myprofile_address.php' ? 'active-dropdown-item' : ''; ?>" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              <?php else: ?>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active-dropdown-item' : ''; ?>" href="login.php">Login</a></li>
                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active-dropdown-item' : ''; ?>" href="register.php">Register</a></li>
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
    <div class="favorites-container">
      <h1 class="mb-4 text-center"><i class="bi bi-heart-fill me-2" style="color: #dc3545;"></i> My Favorites</h1>
      
      <?php if (empty($favorites)): ?>
        <div class="no-favorites">
          <i class="bi bi-heart no-favorites-icon"></i>
          <h3>Your Favorites List is Empty</h3>
          <p class="lead">You haven't added any products to your favorites yet.</p>
          <a href="products.php" class="btn btn-primary btn-success-page mt-3">
            <i class="bi bi-search me-2"></i>Browse Products
          </a>
        </div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($favorites as $product): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="product-card h-100">
                <div class="text-center">
                  <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-img img-fluid">
                </div>
                <h5><?php echo htmlspecialchars($product['product_name']); ?></h5>
                <p class="stock-status <?php echo ($product['stock_quantity'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                  <?php echo ($product['stock_quantity'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
                </p>
                <h5 class="mb-3">RM<?php echo number_format($product['price'], 2); ?></h5>
                <div class="action-buttons">
                  <a href="products.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-eye me-1"></i> View
                  </a>
                  <a href="favorites.php?remove=<?php echo $product['product_id']; ?>" class="btn btn-outline-danger btn-sm flex-grow-1">
                    <i class="bi bi-heartbreak me-1"></i> Remove
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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