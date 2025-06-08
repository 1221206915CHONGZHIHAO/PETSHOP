<?php
session_start();

$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "petshop";

// Database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch best sellers (example: most recently added products)
$best_sellers = [];
$best_sellers_sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
$best_sellers_result = $conn->query($best_sellers_sql);
if ($best_sellers_result && $best_sellers_result->num_rows > 0) {
    while($row = $best_sellers_result->fetch_assoc()) {
        $best_sellers[] = $row;
    }
}

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    // Use prepared statement for secure login
    $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_name = ? AND customer_password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['customer_id'] = $row['customer_id'];
        $_SESSION['customer_name'] = $row['customer_name'];
        
        // Sync cart data from database - Updated to use Product_ID
        $cart_stmt = $conn->prepare("
            SELECT COUNT(DISTINCT Product_ID) AS cart_count 
            FROM cart 
            WHERE Customer_ID = ?
        ");
        $cart_stmt->bind_param("i", $_SESSION['customer_id']);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_row = $cart_result->fetch_assoc();
        
        // Initialize session cart data
        $_SESSION['cart_count'] = $cart_row['cart_count'] ?? 0;
        $_SESSION['cart'] = [];
        
        $cart_stmt->close();
        $stmt->close();
        
        header("Location: userhomepage.php");
        exit();
    } else {
        $login_error = "Invalid username or password";
    }
    $stmt->close();
}

// Ensure cart count is loaded for logged-in users on every page
if (isset($_SESSION['customer_id'])) {
    // Only query if cart_count isn't set or needs refresh
    if (!isset($_SESSION['cart_count'])) {
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT Product_ID) AS cart_count 
            FROM cart 
            WHERE Customer_ID = ?
        ");
        $stmt->bind_param("i", $_SESSION['customer_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $_SESSION['cart_count'] = $row['cart_count'] ?? 0;
        $stmt->close();
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
  <!-- Toastr CSS for notifications -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <!-- Your Custom Styles -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Testimonials Section */
.testimonials {
  background: linear-gradient(to right, rgba(48, 81, 114, 0.9), rgba(240, 242, 245, 0.5));
  position: relative;
  padding: 80px 0;
}

/* Section title styling */
.testimonials .section-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #212529;
  position: relative;
  display: inline-block;
}

.testimonials .section-title:after {
  content: '';
  display: block;
  height: 4px;
  width: 70px;
  background-color: #4e9f3d;
  margin-top: 0.5rem;
}

.testimonials .section-subtitle {
  color: #6c757d;
  font-size: 1.1rem;
  max-width: 700px;
  margin: 0 auto 3rem;
}

/* Testimonial Cards */
.testimonial-card {
  border-radius: 10px;
  border: none;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 25px;
  background: linear-gradient(to bottom, #ffffff, #f8f9fa);
  height: 100%;
}

.testimonial-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

/* Quote icon styling */
.testimonial-card .quote-icon {
  font-size: 2rem;
  color: #4e9f3d;
  opacity: 0.2;
  margin-bottom: 15px;
}

/* Testimonial text styling */
.testimonial-card .card-text {
  font-style: italic;
  margin-bottom: 20px;
  font-size: 1rem;
  line-height: 1.6;
  color: #555;
}

/* Author section styling */
.testimonial-card .author {
  display: flex;
  align-items: center;
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid rgba(0,0,0,0.05);
}

/* Enhanced profile image styling */
.testimonial-card .author-img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #fff;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  margin-right: 15px;
  position: relative;
}

.testimonial-card .author-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
}

/* Author info styling */
.testimonial-card .author-info {
  flex: 1;
}

.testimonial-card .author-info h6 {
  margin-bottom: 3px;
  font-weight: 600;
  font-size: 1.1rem;
}

.testimonial-card .author-info span {
  font-size: 0.9rem;
  opacity: 0.9;
  font-weight: 500;
}

/* Specific card accent colors */
.testimonial-card.accent-primary {
  border-top: 4px solid #4e9f3d;
}

.testimonial-card.accent-secondary {
  border-top: 4px solid #ff7e2e;
}

.testimonial-card.accent-tertiary {
  border-top: 4px solid #1e3a8a;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .testimonial-card {
    margin-bottom: 25px;
  }
}
  </style>
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
          <li class="nav-item"><a class="nav-link active" href="userhomepage.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
        </ul>

        <ul class="navbar-nav ms-auto nav-icons">
          <!-- Search Icon -->
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

          <!-- Cart Icon -->
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

          <!-- User Icon -->
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
                <<li><a class="dropdown-item" href="logout.php?type=customer"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
  <div class="hero-section" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.5)), url('hero image.jpg');">
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
              <a href="products.php?category=Dogs" class="btn btn-outline-primary">Browse Products</a>
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
              <a href="products.php?category=Cats" class="btn btn-outline-primary">Browse Products</a>
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
              <a href="products.php?category=Other" class="btn btn-outline-primary">Browse Products</a>
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
        <?php if(count($best_sellers) > 0): ?>
          <?php foreach($best_sellers as $index => $product): ?>
            <div class="col-6 col-md-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
              <div class="card product-card">
                <div class="product-img-container overflow-hidden">
                  <a href="product_details.php?id=<?php echo $product['product_id']; ?>">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'product-placeholder.png'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                  </a>
                </div>
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                  <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                  <button class="btn btn-primary add-to-cart-btn" 
                          data-product-id="<?php echo $product['product_id']; ?>" 
                          data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center">
            <p>No products available at the moment. Check back soon!</p>
          </div>
        <?php endif; ?>
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
    
    <div class="row">
      <!-- Testimonial 1 -->
      <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="testimonial-card accent-primary h-100">
          <div class="quote-icon"><i class="bi bi-quote"></i></div>
          <p class="card-text">"Hachi Pet Shop completely changed how I shop for my golden retriever, Max. Their premium food options have improved his coat, and the toys keep him entertained for hours. The customer service is exceptional!"</p>
          <div class="author">
            <div class="author-img">
              <img src="user3.jpg" alt="Sarah Johnson">
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
        <div class="testimonial-card accent-secondary h-100">
          <div class="quote-icon"><i class="bi bi-quote"></i></div>
          <p class="card-text">"I've been ordering from Hachi for over a year now for my two cats. The quality is consistently excellent, shipping is always on time, and their rewards program saves me so much money. Couldn't be happier!"</p>
          <div class="author">
            <div class="author-img">
              <img src="user2.png" alt="Michael Rodriguez">
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
        <div class="testimonial-card accent-tertiary h-100">
          <div class="quote-icon"><i class="bi bi-quote"></i></div>
          <p class="card-text">"As someone who owns both fish and a parakeet, it's hard to find a store that caters to both needs. Hachi has everything I need in one place, with great prices and helpful advice when I need it. Highly recommend!"</p>
          <div class="author">
            <div class="author-img">
              <img src="user1.png" alt="Emily Chen">
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
  <section class="brands py-5" style="background: linear-gradient(135deg, var(--light-gray) 0%, white 50%, var(--light-gray) 100%);">
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
            <p>Your trusted partner in pet product. We're dedicated to providing quality products for pet lovers everywhere.</p>
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
  </script>
</body>
</html>