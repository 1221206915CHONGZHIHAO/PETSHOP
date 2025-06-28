<?php
// Start the session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

// Check if customer is logged in and account is active
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
    $check_active = $conn->prepare("SELECT is_active FROM customer WHERE customer_id = ?");
    $check_active->bind_param("i", $customer_id);
    $check_active->execute();
    $check_active->bind_result($is_active);
    $check_active->fetch();
    $check_active->close();
    
    if ($is_active != 1) {
        // Account is deactivated, clear session and redirect
        session_unset();
        session_destroy();
        header("Location: login.php?error=account_deactivated");
        exit();
    }
}

// Function to get current cart count
function getCartCount() {
    if (isset($_SESSION['cart_count'])) {
        return (int)$_SESSION['cart_count'];
    } else if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += isset($item['quantity']) ? (int)$item['quantity'] : 1;
        }
        return $total;
    }
    return 0;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    $stmt->close();
}

// If product not found, redirect to products page
if (!$product) {
    header("Location: products.php");
    exit();
}

// Page title
$page_title = htmlspecialchars($product['product_name']);

// Check if product is in user's favorites (if logged in)
$is_favorite = false;
if (isset($_SESSION['customer_id']) && $product_id > 0) {
    $check_fav_sql = "SELECT * FROM wishlist WHERE customer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_fav_sql);
    $stmt->bind_param("ii", $_SESSION['customer_id'], $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_favorite = ($result->num_rows > 0);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - <?php echo $page_title; ?></title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@600&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Toastr CSS for notifications -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Custom styles for product details page */
    .product-details-section {
      padding: 100px 0;
      background-color: var(--light);
    }
    /* Breadcrumb */
    .breadcrumb {
      background: transparent;
      padding: 0;
      margin-bottom: 1rem;
      font-family: 'Open Sans', sans-serif;
      font-size: 0.9rem;
    }
    .breadcrumb-item a {
      color: var(--gray);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .breadcrumb-item a:hover {
      color: var(--primary);
    }
    .breadcrumb-item.active {
      color: var(--primary);
    }
    /* Product Image Gallery */
    .product-image-container {
      max-width: 100%;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
      margin-bottom: 10px;
    }
    .product-image-container img {
      width: 100%;
      height: auto;
      object-fit: cover;
      aspect-ratio: 4 / 3;
      transition: all 0.5s ease;
    }
    .product-image-container:hover img {
      transform: scale(1.05);
    }
    .thumbnail-gallery {
      display: flex;
      gap: 8px;
      justify-content: start;
    }
    .thumbnail {
      width: 70px;
      height: 70px;
      border-radius: 5px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: all 0.5s ease;
    }
    .thumbnail.active, .thumbnail:hover {
      border-color: var(--primary);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .thumbnail:hover img {
      transform: scale(1.05);
    }
    /* Product Details */
    .product-details {
      padding: 15px 0;
    }
    .product-details h1 {
      font-family: 'Montserrat', sans-serif;
      font-size: 2.2rem;
      font-weight: 600;
      margin-bottom: 0.8rem;
      line-height: 1.2;
      color: var(--dark);
    }
    .product-details .price {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 0.8rem;
    }
    .product-details .price .current-price {
      color: var(--primary);
      font-family: 'Open Sans', sans-serif;
      font-weight: 700;
      font-size: 2rem;
    }
    .product-details .stock-status {
      font-family: 'Open Sans', sans-serif;
      font-size: 1rem;
      margin-bottom: 1.2rem;
      font-weight: 500;
    }
    .product-details .stock-status .text-success {
      display: inline-flex;
      align-items: center;
    }
    .product-details .stock-status .text-danger {
      display: inline-flex;
      align-items: center;
    }
    .product-details .quantity-selector {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 1.2rem;
    }
    .quantity-selector label {
      font-family: 'Open Sans', sans-serif;
      margin-bottom: 0;
      font-weight: 600;
      font-size: 1rem;
      color: var(--dark);
    }
    .quantity-selector .input-group {
      width: 140px;
      border-radius: 8px;
      overflow: hidden;
    }
    .quantity-selector .btn {
      background-color: var(--light-gray);
      border-color: var(--gray);
      color: var(--dark);
      padding: 8px 12px;
      font-size: 1.2rem;
      transition: all 0.3s ease;
    }
    .quantity-selector .btn:hover {
      background-color: var(--gray);
      color: var(--light);
    }
    .quantity-selector .form-control {
      border-color: var(--gray);
      font-family: 'Open Sans', sans-serif;
      font-weight: 600;
      font-size: 1rem;
      padding: 8px;
    }
    .product-details .actions {
      margin-bottom: 1.2rem;
    }
    .btn-add-to-cart {
      background-color: var(--accent);
      border-color: var(--accent);
      color: white;
      padding: 12px 40px;
      font-family: 'Open Sans', sans-serif;
      font-weight: 600;
      font-size: 1.1rem;
      border-radius: 25px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 126, 46, 0.3);
    }
    .btn-add-to-cart:hover {
      background-color: #e66e26;
      border-color: #e66e26;
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255, 126, 46, 0.4);
    }
    .btn-add-to-cart:disabled {
      background-color: var(--gray);
      border-color: var(--gray);
      cursor: not-allowed;
      box-shadow: none;
    }
    .product-details .extra-actions {
      display: flex;
      gap: 20px;
    }
    .extra-actions a {
      color: var(--gray);
      font-size: 1.3rem;
      transition: all 0.3s ease;
    }
    .extra-actions a:hover {
      color: var(--primary);
      transform: translateY(-2px);
    }
    /* Tabs for Product Details */
    .nav-tabs {
      border-bottom: 2px solid var(--light-gray);
      margin-bottom: 1.5rem;
    }
    .nav-tabs .nav-item {
      margin-right: 10px;
    }
    .nav-tabs .nav-link {
      color: var(--gray);
      font-family: 'Open Sans', sans-serif;
      font-weight: 500;
      font-size: 1.1rem;
      border: none;
      padding: 12px 25px;
      border-radius: 8px 8px 0 0;
      transition: all 0.3s ease;
      position: relative;
    }
    .nav-tabs .nav-link.active {
      color: var(--primary);
      background-color: var(--light-gray);
      border-bottom: none;
    }
    .nav-tabs .nav-link.active:after {
      content: '';
      position: absolute;
      width: 60%;
      height: 3px;
      background-color: var(--primary);
      bottom: -2px;
      left: 50%;
      transform: translateX(-50%);
    }
    .nav-tabs .nav-link:hover {
      color: var(--primary);
      background-color: var(--light-gray);
    }
    .tab-content {
      font-family: 'Open Sans', sans-serif;
      font-size: 1rem;
      color: var(--dark);
      line-height: 1.8;
      padding: 20px;
      background-color: white;
      border-radius: 0 8px 8px 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    .tab-content h5 {
      font-family: 'Montserrat', sans-serif;
      color: var(--primary);
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .tab-content ul {
      padding-left: 20px;
    }
    .tab-content ul li {
      margin-bottom: 10px;
    }
    /* Responsive Adjustments */
    @media (max-width: 992px) {
      .product-details-section {
        padding: 70px 0;
      }
      .product-details h1 {
        font-size: 1.9rem;
      }
      .product-details .price .current-price {
        font-size: 1.7rem;
      }
      .thumbnail {
        width: 60px;
        height: 60px;
      }
      .nav-tabs .nav-link {
        font-size: 1rem;
        padding: 10px 15px;
      }
    }
    @media (max-width: 576px) {
      .product-details-section {
        padding: 50px 0;
      }
      .product-details h1 {
        font-size: 1.6rem;
      }
      .product-details .price .current-price {
        font-size: 1.4rem;
      }
      .quantity-selector .input-group {
        width: 120px;
      }
      .btn-add-to-cart {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
      }
      .thumbnail {
        width: 50px;
        height: 50px;
      }
      .nav-tabs .nav-link {
        font-size: 0.9rem;
        padding: 8px 12px;
      }
      .tab-content {
        padding: 15px;
      }
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark custom-nav fixed-top">
  <div class="container">
    <!-- Brand on the left -->
    <a class="navbar-brand" href="userhomepage.php">
      <img src="Hachi_Logo.png" alt="Pet Shop">
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
        <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
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
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
            <?php else: ?>
              <i class="bi bi-person"></i>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
              <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
              <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="logout.php?type=customer">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
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

<!-- Product Details Section -->
<section class="product-details-section">
  <div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" data-aos="fade-down">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>

    <div class="row" data-aos="fade-up" data-aos-duration="800">
      <!-- Product Image -->
      <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="product-image-container">
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" id="mainImage">
        </div>
        <!-- Thumbnail Gallery -->
        <div class="thumbnail-gallery">
          <!-- 假设有多个图片，这里用相同的图片占位 -->
          <div class="thumbnail active" onclick="changeImage('<?php echo htmlspecialchars($product['image_url']); ?>')">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Thumbnail 1">
          </div>
          <div class="thumbnail" onclick="changeImage('<?php echo htmlspecialchars($product['image_url']); ?>')">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Thumbnail 2">
          </div>
        </div>
      </div>
      <!-- Product Details -->
      <div class="col-lg-6">
        <div class="product-details">
          <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
          <div class="price">
            <span class="current-price">RM<?php echo number_format($product['price'], 2); ?></span>
          </div>
          <p class="stock-status">
            <?php if ($product['stock_quantity'] > 0): ?>
              <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>In Stock (<?php echo $product['stock_quantity']; ?>)</span>
            <?php else: ?>
              <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</span>
            <?php endif; ?>
          </p>
          <!-- Quantity Selector -->
          <div class="quantity-selector">
            <label for="quantity">Quantity:</label>
            <div class="input-group">
              <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(-1)">-</button>
              <input type="text" class="form-control text-center" id="quantity" value="1" readonly>
              <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(1)">+</button>
            </div>
          </div>
          <!-- Add to Cart Button -->
          <div class="actions">
            <button class="btn btn-add-to-cart add-to-cart-btn" 
                    data-product-id="<?php echo $product['product_id']; ?>" 
                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                    <?php echo ($product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
              <i class="bi bi-cart-plus me-2"></i>Add to Cart
            </button>
          </div>
          <!-- Extra Actions -->
          <div class="extra-actions">
    <a href="#" id="add-to-favorites" title="<?php echo $is_favorite ? 'Remove from Favorites' : 'Add to Favorites'; ?>" data-product-id="<?php echo $product['product_id']; ?>">
        <i class="bi bi-heart<?php echo $is_favorite ? '-fill' : ''; ?>" <?php echo $is_favorite ? 'style="color: #dc3545;"' : ''; ?>></i>
    </a>
</div>
        </div>
      </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="row mt-5" data-aos="fade-up" data-aos-delay="200">
      <div class="col-12">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Description</button>
          </li>
        </ul>
        <div class="tab-content" id="productTabsContent">
          <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <h5>Product Description</h5>
            <p><?php echo htmlspecialchars($product['description']) ?: "No description available."; ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer style="background: linear-gradient(to bottom,rgb(134, 138, 135),rgba(46, 21, 1, 0.69));">
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
      <div class="col-12">
        <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px;">
          <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
              <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" id="backToTop" style="background: linear-gradient(145deg, var(--primary), var(--primary-dark));">
  <i class="bi bi-arrow-up"></i>
</a>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!-- Custom JavaScript -->
<script>
// Initialize AOS Animation
AOS.init({
  once: true,
  duration: 800,
  offset: 100
});

// Navbar Scroll Effect
const navbar = document.querySelector('.custom-nav');
window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    navbar.classList.add('navbar-scrolled');
  } else {
    navbar.classList.remove('navbar-scrolled');
  }
});

// Back to Top Button
const backToTopButton = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  if (window.scrollY > 300) {
    backToTopButton.classList.add('active');
  } else {
    backToTopButton.classList.remove('active');
  }
});

// Image Gallery Functionality
function changeImage(imageSrc) {
  document.getElementById('mainImage').src = imageSrc;
  // Update active thumbnail
  document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.classList.remove('active');
    if (thumb.querySelector('img').src === imageSrc) {
      thumb.classList.add('active');
    }
  });
}

// Quantity Selector Functionality
function updateQuantity(change) {
  const quantityInput = document.getElementById('quantity');
  let quantity = parseInt(quantityInput.value);
  quantity += change;
  if (quantity < 1) quantity = 1;
  if (quantity > <?php echo $product['stock_quantity']; ?>) quantity = <?php echo $product['stock_quantity']; ?>;
  quantityInput.value = quantity;
}

// Add to Cart Functionality
const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
addToCartButtons.forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();
    const productId = this.getAttribute('data-product-id');
    const productName = this.getAttribute('data-product-name');
    const quantity = document.getElementById('quantity').value;

    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
    
    fetch('add_to_cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `product_id=${productId}&quantity=${quantity}` 
    })
    .then(response => response.json())
    .then(data => {
      this.disabled = false;
      this.innerHTML = originalText;
      
      if (data.success) {
        // Success notification (existing code)
        const toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
        toastContainer.style.zIndex = '9999';
        
        const toastElement = document.createElement('div');
        toastElement.classList.add('toast', 'align-items-center', 'text-white', 'border-0');
        toastElement.style.backgroundColor = '#4e9f3d';
        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        
        toastElement.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">
              <i class="bi bi-check-circle me-2"></i> ${productName} added to cart!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        `;
        
        toastContainer.appendChild(toastElement);
        document.body.appendChild(toastContainer);
        
        const toast = new bootstrap.Toast(toastElement, {
          autohide: true,
          delay: 3000
        });
        toast.show();
        
        // Update cart badge
        const cartLink = document.querySelector('.nav-link[href="cart.php"]');
        let cartBadge = cartLink.querySelector('.badge');
        
        if (data.cart_count > 0) {
          if (!cartBadge) {
            cartBadge = document.createElement('span');
            cartBadge.classList.add('position-absolute', 'top-0', 'start-100', 'translate-middle', 'badge', 'rounded-pill', 'bg-primary');
            cartLink.appendChild(cartBadge);
          }
          cartBadge.textContent = data.cart_count;
        } else if (cartBadge) {
          cartBadge.remove();
        }
        
        toastElement.addEventListener('hidden.bs.toast', function () {
          document.body.removeChild(toastContainer);
        });
      } else if (data.require_login) {
        // NEW: Show login required notification before redirecting
        const loginToastContainer = document.createElement('div');
        loginToastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
        loginToastContainer.style.zIndex = '9999';
        
        const loginToastElement = document.createElement('div');
        loginToastElement.classList.add('toast', 'align-items-center', 'text-white', 'border-0');
        loginToastElement.style.backgroundColor = '#dc3545'; // Red color for warning
        loginToastElement.setAttribute('role', 'alert');
        loginToastElement.setAttribute('aria-live', 'assertive');
        loginToastElement.setAttribute('aria-atomic', 'true');
        
        loginToastElement.innerHTML = `
          <div class="d-flex">
            <div class="toast-body">
              <i class="bi bi-exclamation-triangle me-2"></i> Please login to add items to cart. Redirecting to login page...
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        `;
        
        loginToastContainer.appendChild(loginToastElement);
        document.body.appendChild(loginToastContainer);
        
        const loginToast = new bootstrap.Toast(loginToastElement, {
          autohide: true,
          delay: 3000 // Show for 3 seconds
        });
        loginToast.show();
        
        // Redirect to login page after 3 seconds
        setTimeout(() => {
          window.location.href = 'login.php';
        }, 3000);
        
        // Clean up toast container after it's hidden
        loginToastElement.addEventListener('hidden.bs.toast', function () {
          document.body.removeChild(loginToastContainer);
        });
      } else {
        // Error notification
        alert(data.message || 'Failed to add item to cart');
        console.error('Failed to add item to cart:', data.message);
      }
    })
    .catch(error => {
      this.disabled = false;
      this.innerHTML = originalText;
      console.error('Error:', error);
      
      // Show error notification
      const errorToastContainer = document.createElement('div');
      errorToastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
      errorToastContainer.style.zIndex = '9999';
      
      const errorToastElement = document.createElement('div');
      errorToastElement.classList.add('toast', 'align-items-center', 'text-white', 'border-0');
      errorToastElement.style.backgroundColor = '#dc3545';
      errorToastElement.setAttribute('role', 'alert');
      errorToastElement.setAttribute('aria-live', 'assertive');
      errorToastElement.setAttribute('aria-atomic', 'true');
      
      errorToastElement.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-exclamation-circle me-2"></i> Something went wrong. Please try again.
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      `;
      
      errorToastContainer.appendChild(errorToastElement);
      document.body.appendChild(errorToastContainer);
      
      const errorToast = new bootstrap.Toast(errorToastElement, {
        autohide: true,
        delay: 3000
      });
      errorToast.show();
      
      errorToastElement.addEventListener('hidden.bs.toast', function () {
        document.body.removeChild(errorToastContainer);
      });
    });
  });
});

// Add to Favorites Functionality
const addToFavoritesButton = document.getElementById('add-to-favorites');
if (addToFavoritesButton) {
    // Check initial state
    const isFavorite = addToFavoritesButton.querySelector('i').classList.contains('bi-heart-fill');
    
    addToFavoritesButton.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.getAttribute('data-product-id');
        const isCurrentlyFavorite = this.querySelector('i').classList.contains('bi-heart-fill');
        const action = isCurrentlyFavorite ? 'remove' : 'add';
        
        // Change icon to loading spinner
        const originalHtml = this.innerHTML;
        this.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
        this.style.pointerEvents = 'none';
        
        fetch('add_to_favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            this.style.pointerEvents = 'auto';
            
            if (data.success) {
                // Toggle heart icon
                const heartIcon = this.querySelector('i');
                if (action === 'add') {
                    heartIcon.classList.remove('bi-heart');
                    heartIcon.classList.add('bi-heart-fill');
                    heartIcon.style.color = '#dc3545';
                    this.title = "Remove from Favorites";
                    
                    // Show added notification
                    showFavoriteToast('Product added to favorites!', '#dc3545');
                } else {
                    heartIcon.classList.remove('bi-heart-fill');
                    heartIcon.classList.add('bi-heart');
                    heartIcon.style.color = '';
                    this.title = "Add to Favorites";
                    
                    // Show removed notification
                    showFavoriteToast('Product removed from favorites!', '#6c757d');
                }
            } else if (data.already_added) {
                // Product already in favorites - keep it as filled heart
                this.innerHTML = '<i class="bi bi-heart-fill" style="color: #dc3545;"></i>';
                this.title = "Remove from Favorites";
                showFavoriteToast('This product is already in your favorites!', '#ffc107');
            } else if (data.require_login) {
                showFavoriteToast('Please login to manage favorites. Redirecting to login page...', '#dc3545');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            } else {
                this.innerHTML = originalHtml;
                showFavoriteToast(data.message || 'Failed to update favorites', '#dc3545');
                console.error('Failed to update favorites:', data.message);
            }
        })
        .catch(error => {
            this.innerHTML = originalHtml;
            this.style.pointerEvents = 'auto';
            console.error('Error:', error);
            showFavoriteToast('Something went wrong. Please try again.', '#dc3545');
        });
    });
}

// Helper function to show favorite toasts
function showFavoriteToast(message, bgColor) {
    const toastContainer = document.createElement('div');
    toastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
    toastContainer.style.zIndex = '9999';
    
    const toastElement = document.createElement('div');
    toastElement.classList.add('toast', 'align-items-center', 'text-white', 'border-0');
    toastElement.style.backgroundColor = bgColor;
    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    
    // Choose appropriate icon based on message type
    let iconClass = 'bi-heart-fill';
    if (bgColor === '#ffc107') {
        iconClass = 'bi-exclamation-triangle'; // Warning icon for already added
    } else if (bgColor === '#6c757d') {
        iconClass = 'bi-heart'; // Empty heart for removal
    }
    
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi ${iconClass} me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastElement);
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        document.body.removeChild(toastContainer);
    });
}
</script>
</body>
</html>