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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - <?php echo $page_title; ?></title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      background-color: var(--light-gray);
    }
    .product-image-container {
      max-width: 100%;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }
    .product-image-container img {
      width: 100%;
      height: auto;
      object-fit: cover;
    }
    .product-details {
      padding: 20px;
    }
    .product-details h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }
    .product-details .price {
      color: var(--primary);
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }
    .product-details .category {
      color: var(--gray);
      font-size: 1rem;
      margin-bottom: 1rem;
    }
    .product-details .stock-status {
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }
    .product-details .description {
      font-size: 1.1rem;
      color: var(--dark);
      margin-bottom: 2rem;
      line-height: 1.8;
    }
    @media (max-width: 768px) {
      .product-details h1 {
        font-size: 2rem;
      }
      .product-details .price {
        font-size: 1.5rem;
      }
      .product-details-section {
        padding: 70px 0;
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
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      </ul>

      <!-- Icons on the right -->
      <ul class="navbar-nav ms-auto nav-icons">
        <!-- Search Icon with Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-search"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="searchDropdown" style="min-width: 300px;">
            <form class="d-flex">
              <input class="form-control me-2" type="search" placeholder="Search products..." aria-label="Search">
              <button class="btn btn-primary" type="submit">Go</button>
            </form>
          </ul>
        </li>

        <!-- Cart Icon with item count -->
        <li class="nav-item">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart"></i>
            <?php $cartCount = getCartCount(); if($cartCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                <?php echo $cartCount; ?>
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

<!-- Product Details Section -->
<section class="product-details-section">
  <div class="container">
    <div class="row" data-aos="fade-up" data-aos-duration="800">
      <!-- Product Image -->
      <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="product-image-container">
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
      </div>
      <!-- Product Details -->
      <div class="col-lg-6">
        <div class="product-details">
          <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
          <p class="price">RM<?php echo number_format($product['price'], 2); ?></p>
          <p class="category"><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
          <p class="stock-status">
            <?php if ($product['stock_quantity'] > 0): ?>
              <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>In Stock (<?php echo $product['stock_quantity']; ?>)</span>
            <?php else: ?>
              <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</span>
            <?php endif; ?>
          </p>
          <div class="description">
            <h5>Description</h5>
            <p><?php echo htmlspecialchars($product['description']) ?: "No description available."; ?></p>
          </div>
          <button class="btn btn-primary add-to-cart-btn" 
                  data-product-id="<?php echo $product['product_id']; ?>" 
                  data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                  <?php echo ($product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
            <i class="bi bi-cart-plus me-2"></i>Add to Cart
          </button>
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
            <a href="#"><i class="bi bi-facebook"></i></a>
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

  // Add to Cart Functionality
  const addToCartButton = document.querySelector('.add-to-cart-btn');
  if (addToCartButton) {
    addToCartButton.addEventListener('click', function(e) {
      e.preventDefault();
      const productId = this.getAttribute('data-product-id');
      const productName = this.getAttribute('data-product-name');
      
      // Disable button temporarily
      this.disabled = true;
      const originalText = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
      
      // Send AJAX request to add item to cart
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
      })
      .then(response => response.json())
      .then(data => {
        // Re-enable button
        this.disabled = false;
        this.innerHTML = originalText;
        
        if (data.success) {
          // Create toast notification
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
          
          // Show the toast
          const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
          });
          toast.show();
          
          // Update cart icon with count
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
          window.location.href = 'login.php';
        } else {
          alert(data.message || 'Failed to add item to cart');
          console.error('Failed to add item to cart:', data.message);
        }
      })
      .catch(error => {
        this.disabled = false;
        this.innerHTML = originalText;
        console.error('Error:', error);
      });
    });
  }
</script>
</body>
</html>