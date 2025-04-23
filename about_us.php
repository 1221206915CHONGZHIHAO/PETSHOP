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
  <title>About Us - Hachi Pet Shop</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Custom CSS -->
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
          <li class="nav-item"><a class="nav-link active" href="about_us.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        </ul>

        <ul class="navbar-nav ms-auto nav-icons">
          <!-- Search Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-search"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
              <form class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search products...">
                <button class="btn btn-primary" type="submit">Go</button>
              </form>
            </ul>
          </li>

          <!-- Cart -->
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

          <!-- User Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" data-bs-toggle="dropdown">
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
                <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>Profile</a></li>
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
    <div class="container" data-aos="fade-up">
      <div class="hero-content text-center">
        <h1>About Hachi Pet Shop</h1>
        <p class="lead">Discover our passion for pet care and commitment to quality</p>
      </div>
    </div>
  </div>

  <!-- About Content -->
  <section class="about-content py-5">
    <div class="container">
      <div class="row align-items-center" data-aos="fade-up">
        <div class="col-lg-6 mb-4">
          <img src="about_us_image.jpg" class="img-fluid rounded shadow" alt="Our Team">
        </div>
        <div class="col-lg-6">
          <h2 class="section-title">Our Story</h2>
          <p class="lead">Founded in 2025, Hachi Pet Shop was born from our deep love for animals. What started as a small local store has grown into Singapore's trusted online destination for premium pet products.</p>
          <div class="row mt-4">
            <div class="col-md-6">
              <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                  <h5 class="text-primary"><i class="bi bi-heart-pulse me-2"></i>Our Mission</h5>
                  <p>Delivering happiness and health to every pet through quality products and expert advice.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                  <h5 class="text-primary"><i class="bi bi-award me-2"></i>Our Promise</h5>
                  <p>100% quality guarantee on all products with free returns within 30 days.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5" data-aos="fade-up">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-truck fs-1 text-primary"></i>
              <h5 class="my-3">Fast Delivery</h5>
              <p>Islandwide delivery within 2 working days</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-shield-check fs-1 text-primary"></i>
              <h5 class="my-3">Secure Payments</h5>
              <p>100% secure payment processing</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-chat-dots fs-1 text-primary"></i>
              <h5 class="my-3">Expert Support</h5>
              <p>24/7 customer service via chat and email</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Team Section -->
  <section class="bg-light-custom py-5">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="section-title">Meet Our Team</h2>
        <p class="section-subtitle">Passionate professionals dedicated to pet care</p>
      </div>
      <div class="row">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
          <div class="card border-0 shadow-sm">
            <img src="team1.jpg" class="card-img-top" alt="Veterinarian">
            <div class="card-body text-center">
              <h5>Dr. Sarah Lim</h5>
              <p class="text-muted">Chief Veterinarian</p>
            </div>
          </div>
        </div>
        <!-- Add more team members -->
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