<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About Us - Hachi Pet Shop</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="about_us.css">
</head>
<body>
 <!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark custom-nav">
  <div class="container">
    <!-- Brand on the left -->
    <a class="navbar-brand" href="userhomepage.php">
      <img src="cat_paw.png" alt="Pet Shop" width="50">
      <span>Hachi Pet Shop</span>
    </a>
    
    <!-- Toggler for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main nav links centered -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link active" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Product</a></li>
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

        <!-- Cart Icon with Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cart" style="font-size: 1.2rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="cartDropdown">
            <li><a class="dropdown-item" href="#">Your cart is empty</a></li>
          </ul>
        </li>

        <!-- User Icon with Dynamic Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person" style="font-size: 1.2rem;"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <?php if(isset($_SESSION['customer_id'])): ?>
              <!-- If user is logged in, show username and account links -->
              <li class="dropdown-item-text">
                <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
              </li>
              <li><a class="dropdown-item" href="account_setting.php">Account Settings</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php else: ?>
              <!-- If not logged in, show login/register links -->
              <li><a class="dropdown-item" href="admin_login.php">Login</a></li>
              <li><a class="dropdown-item" href="admin_register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>


  <!-- Hero Section -->
  <section class="hero-section-about">
    <div class="hero-overlay-about"></div>
    <div class="hero-content container text-center">
      <h1>About Us</h1>
      <p>Learn more about our passion and mission to provide the best for your pets.</p>
    </div>
  </section>

  <!-- About Us Content -->
  <section class="about-content">
    <div class="container">
      <div class="row align-items-center">
        <!-- Image Column -->
        <div class="image">
          <img src="about_us_image.jpg" class="img-fluid rounded" alt="About Us">
        </div>
        <!-- Text Column -->
        <div class="text">
          <h2>Our Story</h2>
          <p>Founded in 2025, Pet Shop was born out of a passion for animals and a desire to provide high-quality products for pet owners everywhere. We believe that every pet deserves the best care and accessories. Our dedicated team works tirelessly to curate a selection of premium products that ensure the health and happiness of your beloved companions.</p>
          <h2>Our Mission</h2>
          <p>Our mission is to offer a convenient, reliable, and enjoyable shopping experience for pet lovers. We strive to provide a wide range of products at competitive prices, backed by exceptional customer service and expert advice.</p>
          <h2>Our Team</h2>
          <p>Our team consists of animal care experts, product specialists, and customer service professionals who are committed to helping you make informed decisions for your pet's needs.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer bg-dark text-white py-4">
    <div class="container text-center">
      <p>&copy; 2025 Pet Shop. All rights reserved.</p>
      <p>Email: <a href="mailto:info@petshop.com" class="text-decoration-none text-white">info@petshop.com</a></p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
