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
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Your Custom Styles -->
  <link rel="stylesheet" href="userhomepage.css">
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
          <li class="nav-item"><a class="nav-link active" href="userhomepage.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
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
              <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                  <?php echo count($_SESSION['cart']); ?>
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

  <!-- Hero Section -->
  <div class="hero-section">
    <div class="container" data-aos="fade-up" data-aos-duration="1000">
      <div class="hero-content">
        <h1>Pamper Your Pets<br>With Premium Care</h1>
        <p>Your one-stop online destination for quality pet products. From nutrition to toys, we've got everything your beloved companion needs.</p>
        <div class="d-flex gap-3">
          <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Featured Categories Section -->
  <section class="categories py-5">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="section-title">Shop By Pet</h2>
        <p class="section-subtitle">Explore our carefully curated selection of premium products for all your pet needs</p>
      </div>
      <div class="row">
        <!-- Category Card 1 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="card category-card h-100">
            <div class="overflow-hidden">
              <img src="dog_categories.png" class="card-img-top" alt="Dogs">
            </div>
            <div class="card-body text-center">
              <h5 class="card-title">Dogs</h5>
              <p class="card-text text-muted mb-4">Food, toys, accessories, and more for your canine companion</p>
              <a href="products.php?category=dogs" class="btn btn-outline-primary">Browse Products</a>
            </div>
          </div>
        </div>
        <!-- Category Card 2 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card category-card h-100">
            <div class="overflow-hidden">
              <img src="cat_categories.png" class="card-img-top" alt="Cats">
            </div>
            <div class="card-body text-center">
              <h5 class="card-title">Cats</h5>
              <p class="card-text text-muted mb-4">Everything your feline friend needs for a happy, healthy life</p>
              <a href="products.php?category=cats" class="btn btn-outline-primary">Browse Products</a>
            </div>
          </div>
        </div>
        <!-- Category Card 3 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="card category-card h-100">
            <div class="overflow-hidden">
              <img src="other_categories.png" class="card-img-top" alt="Other Pets">
            </div>
            <div class="card-body text-center">
              <h5 class="card-title">Other Pets</h5>
              <p class="card-text text-muted mb-4">Supplies for birds, fish, reptiles, and small animals</p>
              <a href="products.php?category=other" class="btn btn-outline-primary">Browse Products</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Best Sellers Section -->
  <section class="best-sellers bg-light-custom py-5">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="section-title">Best Sellers</h2>
        <p class="section-subtitle">Discover the products our customers love the most</p>
      </div>
      <div class="row">
        <!-- Best Seller Product 1 -->
        <div class="col-6 col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="card product-card">
            <div class="product-img-container">
              <img src="dog_product.png" class="card-img-top" alt="PEDIGREE Complete Nutrition">
            </div>
            <div class="card-body">
              <h5 class="card-title">PEDIGREE Complete Nutrition Dog Food</h5>
              <div class="price">$29.99</div>
              <a href="#" class="btn btn-primary add-to-cart-btn" data-product-id="1" data-product-name="PEDIGREE Complete Nutrition">Add to Cart</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 2 -->
        <div class="col-6 col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card product-card">
            <div class="product-img-container">
              <img src="cat_scratcher.png" class="card-img-top" alt="Cat Scratcher">
            </div>
            <div class="card-body">
              <h5 class="card-title">Premium Cat Scratcher Tower</h5>
              <div class="price">$19.99</div>
              <a href="#" class="btn btn-primary add-to-cart-btn" data-product-id="2" data-product-name="Premium Cat Scratcher">Add to Cart</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 3 -->
        <div class="col-6 col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="card product-card">
            <div class="product-img-container">
              <img src="Bird_cage.png" class="card-img-top" alt="Bird Cage">
            </div>
            <div class="card-body">
              <h5 class="card-title">Deluxe Bird Cage with Stand</h5>
              <div class="price">$49.99</div>
              <a href="#" class="btn btn-primary add-to-cart-btn" data-product-id="3" data-product-name="Deluxe Bird Cage">Add to Cart</a>
            </div>
          </div>
        </div>
        <!-- Best Seller Product 4 -->
        <div class="col-6 col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
          <div class="card product-card">
            <div class="product-img-container">
              <img src="fish_tank.png" class="card-img-top" alt="Fish Tank">
            </div>
            <div class="card-body">
              <h5 class="card-title">Complete Aquarium Starter Kit</h5>
              <div class="price">$89.99</div>
              <a href="#" class="btn btn-primary add-to-cart-btn" data-product-id="4" data-product-name="Complete Aquarium Kit">Add to Cart</a>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center mt-4" data-aos="fade-up">
        <a href="products.php" class="btn btn-outline-primary">View All Products</a>
      </div>
    </div>
  </section>

  <!-- About Us Section -->
  <section class="about-section py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
          <div class="about-img">
            <img src="about_us_index.png" class="img-fluid rounded" alt="About Us">
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
          <div class="about-content">
            <h2>We Care About Your Pets</h2>
            <p>At Hachi Pet Shop, we understand that pets are family. That's why we're committed to providing only the highest quality products that promote their health, happiness, and well-being.</p>
            <p>Our carefully curated selection ensures that your animal companions receive the care they deserve, from premium nutrition to engaging toys and comfortable accessories.</p>
            <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
              <a href="about_us.php" class="btn btn-primary">About Our Story</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section with improved visual design -->
  <section class="testimonials py-5">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="section-title">Happy Pet Parents</h2>
        <p class="section-subtitle">See what our customers have to say about their experience with Hachi Pet Shop</p>
      </div>
      <!-- Decorative paw prints background -->
      <div class="position-absolute" style="z-index: -1; opacity: 0.05; right: 5%; top: 10%; width: 100%;">
      </div>
      <div class="row">
        <!-- Testimonial 1 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="card testimonial-card h-100" style="border-top: 4px solid var(--primary);">
            <div class="quote-icon"><i class="bi bi-quote"></i></div>
            <p class="card-text">"Hachi Pet Shop completely changed how I shop for my golden retriever, Max. Their premium food options have improved his coat, and the toys keep him entertained for hours. The customer service is exceptional!"</p>
            <div class="author">
              <div class="author-img me-3">
                <img src="user1.png" alt="Sarah Johnson" class="rounded-circle" width="50" height="50">
              </div>
              <div class="author-info">
                <h6>Sarah Johnson</h6>
                <span class="text-primary">Dog Owner</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card testimonial-card h-100" style="border-top: 4px solid var(--accent);">
            <div class="quote-icon"><i class="bi bi-quote"></i></div>
            <p class="card-text">"I've been ordering from Hachi for over a year now for my two cats. The quality is consistently excellent, shipping is always on time, and their rewards program saves me so much money. Couldn't be happier!"</p>
            <div class="author">
              <div class="author-img me-3">
                <img src="user2.png" alt="Michael Rodriguez" class="rounded-circle" width="50" height="50">
              </div>
              <div class="author-info">
                <h6>Michael Rodriguez</h6>
                <span style="color: var(--accent);">Cat Owner</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Testimonial 3 -->
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="card testimonial-card h-100" style="border-top: 4px solid var(--secondary);">
            <div class="quote-icon"><i class="bi bi-quote"></i></div>
            <p class="card-text">"As someone who owns both fish and a parakeet, it's hard to find a store that caters to both needs. Hachi has everything I need in one place, with great prices and helpful advice when I need it. Highly recommend!"</p>
            <div class="author">
              <div class="author-img me-3">
                <img src="user3.png" alt="Emily Chen" class="rounded-circle" width="50" height="50">
              </div>
              <div class="author-info">
                <h6>Emily Chen</h6>
                <span style="color: var(--secondary);">Multiple Pet Owner</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Brands Section with improved visual interest -->
  <section class="brands py-5" style="background: linear-gradient(135deg, var(--light-gray) 0%, white 50%, var(--light-gray) 100%);">>
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="section-title">Trusted Brands</h2>
        <p class="section-subtitle">We partner with the best pet brands to bring quality products to your doorstep</p>
      </div>
      <div class="row justify-content-center align-items-center">
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="100">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand1.png" alt="Brand 1" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="200">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand2.png" alt="Brand 2" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="300">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand3.png" alt="Brand 3" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="400">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand4.png" alt="Brand 4" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="500">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand5.png" alt="Brand 5" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
        <div class="col-4 col-md-2 mb-4 text-center" data-aos="fade-up" data-aos-delay="600">
          <div class="brand-box p-3 rounded bg-white shadow-sm">
            <img src="Brand6.png" alt="Brand 6" class="img-fluid" style="max-height: 60px;">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer with simplified structure -->
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

  <!-- Back to Top Button with improved styling -->
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
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.getAttribute('data-product-id');
        const productName = this.getAttribute('data-product-name');
        
        // Create a toast notification
        const toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'bottom-0', 'end-0', 'p-3');
        toastContainer.style.zIndex = '5';
        
        const toastElement = document.createElement('div');
        toastElement.classList.add('toast', 'align-items-center', 'text-white', 'bg-primary', 'border-0');
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
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Send AJAX request to add item to cart
        // This is where you would normally add AJAX code to update the cart on the server
        console.log(`Product added to cart: ID - ${productId}, Name - ${productName}`);
        
        // For demo purposes, remove the toast container after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function () {
          document.body.removeChild(toastContainer);
        });
      });
    });
  </script>
</body>
</html>