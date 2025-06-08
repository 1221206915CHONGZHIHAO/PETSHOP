<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

$actual_password = $user['Customer_password'];
$masked_password = str_repeat('*', strlen($actual_password));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    .dashboard-container {
      display: flex;
      padding: 20px;
      min-height: calc(100vh - 76px - 91px);
    }
    .sidebar {
      width: 250px;
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 15px;
      margin-right: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .sidebar-nav { 
      list-style: none; 
      padding: 0; 
    }
    .sidebar-nav li { 
      margin-bottom: 10px; 
    }
    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 10px;
      color: #333;
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s ease;
    }
    .sidebar-nav a:hover { 
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    .sidebar-nav a.active { 
      background-color: var(--primary); 
      color: white;
    }
    .sidebar-nav a i { 
      margin-right: 10px; 
      width: 20px; 
      text-align: center; 
    }
    .main-content { 
      flex: 1; 
    }
    .info-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .account-details { 
      display: flex; 
      align-items: center; 
    }
    .user-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: #dee2e6;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: bold;
      margin-right: 20px;
      overflow: hidden;
    }
    .user-avatar img { 
      width: 100%; 
      height: 100%; 
      object-fit: cover; 
    }
    .user-info { 
      flex: 1; 
    }
    .user-info .row { 
      margin-bottom: 10px; 
    }
    .password-container { 
      position: relative; 
    }
    .password-toggle {
      position: absolute;
      right: 0;
      top: 0;
      cursor: pointer;
      background: none;
      border: none;
      color: #6c757d;
    }
    /* Dashboard menu styling */
    .dashboard-menu .active {
      background-color: var(--primary) !important;
      color: white !important;
    }
    .dashboard-menu a:hover {
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    .logout-link {
      color: #dc3545 !important;
    }
    .logout-link:hover {
      background-color: rgba(220, 53, 69, 0.1) !important;
      color: #dc3545 !important;
    }
    
    /* User dropdown menu styling */
    .user-dropdown .active-dropdown-item {
      background-color: var(--primary) !important;
      color: white !important;
    }
    
    .user-dropdown .dropdown-item:hover {
      background-color: rgba(78, 159, 61, 0.1);
      color: var(--primary);
    }
    
    .dropdown-item.active, .dropdown-item:active {
      background-color: var(--primary);
      color: white;
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
                <li><a class="dropdown-item" href="logout.php?type=customer"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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

<div class="dashboard-container container">
  <div class="sidebar">
    <ul class="sidebar-nav dashboard-menu">
      <li><a href="user_dashboard.php" class="active"><i class="bi bi-house"></i> Dashboard</a></li>
      <li><a href="my_orders.php"><i class="bi bi-box"></i> My Orders</a></li>
      <li><a href="favorites.php"><i class="bi bi-heart"></i> My Favourite</a></li>
      <li><a href="myprofile_address.php"><i class="bi bi-person-lines-fill"></i> My Profile/Address</a></li>
      <li><a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </div>
  
  <div class="main-content">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Account Details</h2>
          <a href="myprofile_address.php" class="btn btn-outline-primary">View more</a>
        </div>
        <div class="info-card">
          <div class="account-details">
            <div class="user-avatar">
              <?php if (!empty($user['profile_image']) && file_exists('uploads/profile_images/' . $user['profile_image'])): ?>
                <img src="uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image">
              <?php else: ?>
                <?php 
                $name = $user['Customer_name'];
                $initials = strtoupper(substr($name, 0, 1));
                if (strpos($name, ' ') !== false) {
                  $name_parts = explode(' ', $name);
                  $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
                }
                echo $initials;
                ?>
              <?php endif; ?>
            </div>
            <div class="user-info">
              <div class="row">
                <div class="col-md-3 fw-bold">Name:</div>
                <div class="col-md-9"><?php echo htmlspecialchars($user['Customer_name']); ?></div>
              </div>
              <div class="row">
                <div class="col-md-3 fw-bold">Password:</div>
                <div class="col-md-9 password-container">
                  <span id="passwordDisplay"><?php echo $masked_password; ?></span>
                  <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="bi bi-eye" id="passwordToggleIcon"></i>
                  </button>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3 fw-bold">Email:</div>
                <div class="col-md-9"><?php echo htmlspecialchars($user['Customer_email'] ?? ''); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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
    
    // Toggle Password Visibility
    function togglePassword() {
      const passwordDisplay = document.getElementById('passwordDisplay');
      const passwordToggleIcon = document.getElementById('passwordToggleIcon');
      if (passwordDisplay.textContent === '<?php echo $masked_password; ?>') {
        passwordDisplay.textContent = '<?php echo addslashes($actual_password); ?>';
        passwordToggleIcon.classList.remove('bi-eye');
        passwordToggleIcon.classList.add('bi-eye-slash');
      } else {
        passwordDisplay.textContent = '<?php echo $masked_password; ?>';
        passwordToggleIcon.classList.remove('bi-eye-slash');
        passwordToggleIcon.classList.add('bi-eye');
      }
    }
  </script>
</body>
</html>