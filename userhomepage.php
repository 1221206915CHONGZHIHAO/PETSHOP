<?php
session_start();


$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "petshop";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $sql = "SELECT * FROM customer WHERE customer_name='$username' AND customer_password='$password'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['customer_id'] = $row['customer_id'];
        $_SESSION['customer_name'] = $row['customer_name'];
        header("Location: userhomepage.php");
        exit();
    } else {
        $login_error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Home</title>
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
    <img src="Hachi_Logo.png" alt="Pet Shop">
    </a>
    
    <!-- Toggler for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main nav links centered -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link active" href="userhomepage.php">Home</a></li>
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

        <!-- Cart Icon with item count -->
        <li class="nav-item">
          <a class="nav-link position-relative" href="cart.php">
            <i class="bi bi-cart" style="font-size: 1.2rem;"></i>
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo count($_SESSION['cart']); ?>
              </span>
            <?php endif; ?>
          </a>
        </li>

        <!-- User Icon with Dynamic Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- Show username when logged in -->
              <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
            <?php else: ?>
              <i class="bi bi-person" style="font-size: 1.2rem;"></i>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- If user is logged in, show dashboard options -->
              <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favourite</a></li>
              <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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


  <!-- Hero Section -->
  <div class="hero-section">
    <div class="hero-content container text-center">
      <h1>Welcome to Our Pet Shop</h1>
      <p>Your one-stop online store for all pet essentials. Find quality products for your beloved companions.</p>
    </div>
  </div>

  <!-- Featured Categories Section -->
  <section class="categories py-5">
    <div class="container">
      <h2 class="section-title text-center">Featured Categories</h2>
      <div class="row">
        <!-- Category Card 1 -->
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="dog_categories.png" class="card-img-top" alt="Dogs">
            <div class="card-body text-center">
              <h5 class="card-title">Shop for Dogs</h5>
              <a href="#" class="btn btn-outline-primary">Shop now</a>
            </div>
          </div>
        </div>
        <!-- Category Card 2 -->
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="cat_categories.png" class="card-img-top" alt="Cats">
            <div class="card-body text-center">
              <h5 class="card-title">Shop for Cats</h5>
              <a href="#" class="btn btn-outline-primary">Shop now</a>
            </div>
          </div>
        </div>
        <!-- Category Card 3 -->
        <div class="col-md-4 mb-4">
          <div class="card">
            <img src="other_categories.png" class="card-img-top" alt="Other Pets">
            <div class="card-body text-center">
              <h5 class="card-title">Other Pets and Accessories</h5>
              <a href="#" class="btn btn-outline-primary">Shop now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Best Sellers Section -->
  <section class="best-sellers py-5 bg-light">
    <div class="container">
      <h2 class="section-title text-center">Best Sellers</h2>
      <div class="row">
        <!-- Best Seller Product 1 -->
        <div class="col-md-3 mb-4">
          <div class="card product-card">
            <img src="dog_product.png" class="card-img-top" alt="PEDIGREE Complete Nutrition Grilled Steak & Vegetable Dry Dog Food">
            <div class="card-body text-center">
              <h5 class="card-title">PEDIGREE Complete Nutrition Grilled Steak & Vegetable Dry Dog Food</h5>
              <p class="card-text">$29.99</p>
              <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 2 -->
        <div class="col-md-3 mb-4">
          <div class="card product-card">
            <img src="cat_scratcher.png" class="card-img-top" alt="Cat Scratcher">
            <div class="card-body text-center">
              <h5 class="card-title">Cat Scratcher</h5>
              <p class="card-text">$19.99</p>
              <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 3 -->
        <div class="col-md-3 mb-4">
          <div class="card product-card">
            <img src="Bird_cage.png" class="card-img-top" alt="Bird Cage">
            <div class="card-body text-center">
              <h5 class="card-title">Bird Cage</h5>
              <p class="card-text">$49.99</p>
              <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 4 -->
        <div class="col-md-3 mb-4">
          <div class="card product-card">
            <img src="fish_tank.png" class="card-img-top" alt="Fish Tank">
            <div class="card-body text-center">
              <h5 class="card-title">Fish Tank</h5>
              <p class="card-text">$89.99</p>
              <a href="#" class="btn btn-sm btn-primary">Buy Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Us Section -->
  <section class="about-us py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <img src="about_us_index.png" class="img-fluid rounded" alt="About Us">
        </div>
        <div class="col-md-6">
          <h2>About Our Pet Shop</h2>
          <p>We are dedicated to providing the best quality products for your pets. Our carefully curated selection ensures that your animal friends receive the care they deserve. Enjoy a seamless shopping experience, reliable customer service, and unbeatable deals.</p>
          <a href="about_us.php" class="btn btn-outline-primary">Learn More</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section class="testimonials py-5 bg-light">
    <div class="container">
      <h2 class="section-title text-center">What Our Customers Say</h2>
      <div class="row">
        <!-- Testimonial 1 -->
        <div class="col-md-4 mb-4">
          <div class="card testimonial-card">
            <div class="card-body">
              <p class="card-text">"Great selection and fast shipping! My dog loves the new treats I ordered."</p>
              <h6 class="card-subtitle text-muted">- Alex</h6>
            </div>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="col-md-4 mb-4">
          <div class="card testimonial-card">
            <div class="card-body">
              <p class="card-text">"Excellent customer service and quality products. I highly recommend this shop!"</p>
              <h6 class="card-subtitle text-muted">- Jamie</h6>
            </div>
          </div>
        </div>
        <!-- Testimonial 3 -->
        <div class="col-md-4 mb-4">
          <div class="card testimonial-card">
            <div class="card-body">
              <p class="card-text">"I was impressed by the variety and the friendly staff. Five stars!"</p>
              <h6 class="card-subtitle text-muted">- Taylor</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Footer -->
  <footer class="footer bg-dark text-white py-4">
    <div class="container text-center">
      <div class="mb-3">
        <a href="#" class="text-white me-3">Facebook</a>
        <a href="#" class="text-white me-3">Twitter</a>
        <a href="#" class="text-white">Instagram</a>
      </div>
      <p>&copy; 2025 Hachi Pet Shop. All rights reserved.</p>
      <p>Email: <a href="mailto:info@petshop.com" class="text-decoration-none text-white">info@petshop.com</a></p>
    </div>
  </footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Toastr JS for notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
  // Configure toastr notification settings
  toastr.options = {
    "closeButton": true,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  };
  
  // Sort select change handler
  $('#sortSelect').change(function() {
    window.location.href = 'products.php?sort=' + $(this).val();
  });
  
  // Add to cart button click handler
  $('.add-to-cart-btn').click(function() {
    const productId = $(this).data('product-id');
    const productName = $(this).data('product-name');
    
    // AJAX request to add item to cart
    $.ajax({
      url: 'add_to_cart.php',
      type: 'POST',
      data: {
        product_id: productId,
        quantity: 1
      },
      success: function(response) {
        try {
          const result = JSON.parse(response);
          if (result.success) {
            // Show success notification
            toastr.success(productName + ' added to cart!');
            
            // Update cart count if necessary
            if (result.cart_count) {
              updateCartCount(result.cart_count);
            }
          } else if (result.require_login) {
            // Show login required message
            toastr.warning(result.message);
            
            // Show login modal or redirect to login page after a short delay
            setTimeout(function() {
              window.location.href = 'login.php?redirect=products.php';
            }, 2000);
          } else {
            // Show error message
            toastr.error(result.message || 'Failed to add item to cart');
          }
        } catch(e) {
          toastr.error('Error processing response');
          console.error('Error parsing JSON: ', e);
        }
      },
      error: function() {
        toastr.error('Error processing your request');
      }
    });
  });
  
  // Function to update cart count badge
  function updateCartCount(count) {
    const cartIcon = $('.bi-cart');
    // Remove existing badge if any
    cartIcon.next('.badge').remove();
    
    // Add badge if count > 0
    if (count > 0) {
      cartIcon.after('<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' + count + '</span>');
    }
  }
});
</script>
</body>
</html>