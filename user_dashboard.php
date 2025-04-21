<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "petshop";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// Get user details
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Get the actual password from database
$actual_password = $user['Customer_password'];
// Create masked password (all asterisks)
$masked_password = str_repeat('*', strlen($actual_password));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Dashboard</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    /* Dashboard specific styles */
    .dashboard-container {
      display: flex;
      padding: 20px;
      min-height: calc(100vh - 76px - 91px); /* Account for navbar and footer height */
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
    
    .sidebar-nav a:hover, .sidebar-nav a.active {
      background-color: #e9ecef;
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
    }
    
    .user-info {
      flex: 1;
    }
    
    .user-info .row {
      margin-bottom: 10px;
    }
    
    /* Password toggle styling */
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
    
  </style>
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
        <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
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
              <li><a class="dropdown-item" href="account_setting.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
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

<!-- Dashboard Content -->
<div class="dashboard-container container">
  <!-- Sidebar -->
  <div class="sidebar">
    <ul class="sidebar-nav">
      <li>
        <a href="user_dashboard.php" class="active">
          <i class="bi bi-house"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="my_orders.php">
          <i class="bi bi-box"></i> My Orders
        </a>
      </li>
      <li>
        <a href="favorites.php">
          <i class="bi bi-heart"></i> My Favourite
        </a>
      </li>
      <li>
        <a href="myprofile_address.php">
          <i class="bi bi-person-lines-fill"></i> My Profile/Address
        </a>
      </li>
      <li>
        <a href="logout.php" class="text-danger">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <!-- Account Details Section -->
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2>Account Details</h2>
          <a href="account_setting.php" class="btn btn-link">View more</a>
        </div>
        <div class="info-card">
          <div class="account-details">
            <div class="user-avatar">
              <?php 
              // Display user initials
              $name = $user['Customer_name'];
              $initials = strtoupper(substr($name, 0, 1));
              if (strpos($name, ' ') !== false) {
                $name_parts = explode(' ', $name);
                $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
              }
              echo $initials;
              ?>
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

<script>
// Password toggle function
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