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
// In products.php, replace the getCartCount() function
function getCartCount() {
  if (isset($_SESSION['cart_count'])) {
      return (int)$_SESSION['cart_count'];
  } else if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
      return count($_SESSION['cart']); // Count items, not quantities
  }
  return 0;
}

// Get products from database
function getProducts($sort = 'newest', $category = '') {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE 1=1";
    
    // Add category filter if provided
    if (!empty($category)) {
        $category = $conn->real_escape_string($category);
        $sql .= " AND category = '$category'";
    }
    
    // Add sorting logic
    switch ($sort) {
        case 'lowest-price':
            $sql .= " ORDER BY price ASC";
            break;
        case 'highest-price':
            $sql .= " ORDER BY price DESC";
            break;
        case 'best-selling':
            $sql .= " ORDER BY updated_at DESC";
            break;
        default: // newest
            $sql .= " ORDER BY created_at DESC";
            break;
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

// Get categories for sidebar
function getCategories() {
    global $conn;
    
    $sql = "SELECT DISTINCT category FROM products ORDER BY category";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }
    
    return $categories;
}

// Get sort parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get category parameter
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Get products and categories
$products = getProducts($sort, $category);
$categories = getCategories();

// Count items
$product_count = count($products);

// Page title - change based on category
$page_title = !empty($category) ? htmlspecialchars($category) : "All Products";
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

<!-- Main Container -->
<div class="container py-4">
  <div class="row">
    <!-- Filter sidebar -->
    <aside class="col-lg-3 filter-section mb-4">
      <!-- Categories -->
      <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
          <h5>Categories</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <a href="products.php" class="<?php echo empty($category) ? 'text-primary' : ''; ?>">
                All Products
              </a>
            </li>
            <?php foreach($categories as $cat): ?>
              <li class="list-group-item">
                <a href="products.php?category=<?php echo urlencode($cat); ?>" class="<?php echo $category === $cat ? 'text-primary' : ''; ?>">
                  <?php echo htmlspecialchars($cat); ?>
                </a>
              </li>
            <?php endforeach; ?>
            <?php if(empty($categories)): ?>
              <li class="list-group-item">No categories found</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </aside>

    <!-- Product listing -->
    <div class="col-lg-9">
      <!-- Top info and sorting -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <p class="mb-0"><strong><?php echo $product_count; ?></strong> items found</p>
        
        <div class="d-flex align-items-center">
          <label for="sortSelect" class="me-2">Sort By:</label>
          <select id="sortSelect" class="form-select form-select-sm" style="width: auto;">
            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>New Arrivals</option>
            <option value="lowest-price" <?php echo $sort == 'lowest-price' ? 'selected' : ''; ?>>Lowest Price</option>
            <option value="highest-price" <?php echo $sort == 'highest-price' ? 'selected' : ''; ?>>Highest Price</option>
            <option value="best-selling" <?php echo $sort == 'best-selling' ? 'selected' : ''; ?>>Best Selling</option>
          </select>
        </div>
      </div>

      <!-- Product cards with AOS animations -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php if(!empty($products)): ?>
          <?php foreach($products as $index => $product): ?>
            <div class="col" data-aos="fade-up" data-aos-delay="<?php echo 50 * ($index % 6); ?>">
              <div class="card product-card h-100">
                <div class="overflow-hidden">
                  <a href="product_details.php?id=<?php echo $product['product_id']; ?>">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                  </a>
                </div>
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                  <p class="product-price">RM<?php echo number_format($product['price'], 2); ?></p>
                  <?php if($product['stock_quantity'] > 0): ?>
                    <p class="text-success small mb-0"><i class="bi bi-check-circle-fill me-1"></i>In Stock (<?php echo $product['stock_quantity']; ?>)</p>
                  <?php else: ?>
                    <p class="text-danger small mb-0"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</p>
                  <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                  <button class="btn btn-primary w-100 add-to-cart-btn" 
                          data-product-id="<?php echo $product['product_id']; ?>" 
                          data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                          <?php echo ($product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>No products found. Please try different filters.
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

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
  
  // Sort select functionality
  document.getElementById('sortSelect').addEventListener('change', function() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', this.value);
    window.location.href = currentUrl.toString();
  });

  // Updated Add to Cart Functionality
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
  addToCartButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const productId = this.getAttribute('data-product-id');
      const productName = this.getAttribute('data-product-name');
      
      this.disabled = true;
      const originalText = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
      
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
      })
      .then(response => response.json())
      .then(data => {
        this.disabled = false;
        this.innerHTML = originalText;
        
        if (data.success) {
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
  });
</script>
</body>
</html>