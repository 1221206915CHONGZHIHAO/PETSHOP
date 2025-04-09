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
  <link rel="stylesheet" href="products.css">
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

       <!-- Cart Icon: 直接链接到购物车页面，并附带商品数量 badge -->
<li class="nav-item">
  <a class="nav-link position-relative" href="cart.php">
    <i class="bi bi-cart" style="font-size: 1.2rem;"></i>
  </a>
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
    <!-- 过滤栏 -->
    <aside class="col-md-3 filter-section">
      <!-- Categories -->
      <h5>Categories</h5>
      <ul class="list-group mb-4">
        <li class="list-group-item"><a href="#">Dog > Dry Food</a></li>
        <li class="list-group-item"><a href="#">Dog > Freeze Dried &amp; Air Dried</a></li>
        <li class="list-group-item"><a href="#">Dog > Treats</a></li>
        <li class="list-group-item"><a href="#">Dog > Wet Food</a></li>
      </ul>

      <!-- Brands -->
      <h5>Brands</h5>
      <ul class="list-group mb-4">
        <li class="list-group-item"><a href="#">ProBalance</a></li>
      </ul>
    </aside>

    <!-- 右侧商品列表 -->
    <div class="col-md-9">
      <!-- 顶部信息与排序 -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0">3 items found for Food &amp; Treats</p>
        
        <div class="d-flex align-items-center">
          <label for="sortSelect" class="me-2">Sort By:</label>
          <select id="sortSelect" class="form-select form-select-sm" style="width: auto;">
            <option value="best-selling">Best Selling</option>
            <option value="lowest-price">Lowest Price</option>
            <option value="highest-price">Highest Price</option>
            <option value="new-arrivals">New Arrivals</option>
          </select>
        </div>
      </div>

      <!-- 商品卡片列表：Bootstrap Cards -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
        <!-- 示例卡片 1 -->
        <div class="col">
          <div class="card product-card h-100">
            <img src="ProBalance_tenderlamb.png" class="card-img-top" alt="Probalance Pouch Tender Lamb 100g">
            <div class="card-body">
              <h5 class="card-title">Probalance Pouch Tender Lamb 100g</h5>
              <p class="product-price">Price: RM4.50</p>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary w-100">Add to Cart</button>
            </div>
          </div>
        </div>
        
        <!-- 示例卡片 2 -->
        <div class="col">
          <div class="card product-card h-100">
            <img src="dog_product.png" class="card-img-top" alt="Pedigree Complete Nutrition Roasted Chicken, Rice & Vegetable Dry Dog Food">
            <div class="card-body">
              <h5 class="card-title">Pedigree Complete Nutrition Roasted Chicken, Rice & Vegetable Dry Dog Food</h5>
              <p class="product-price">Price: RM175.00</p>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary w-100">Add to Cart</button>
            </div>
          </div>
        </div>

        <!-- 示例卡片 3 -->
        <div class="col">
          <div class="card product-card h-100">
            <img src="Pedigree_pouch.png" class="card-img-top" alt="Pedigree Pouch 130g">
            <div class="card-body">
              <h5 class="card-title">Pedigree Pouch 130g</h5>
              <p class="product-price">Price: RM3.00</p>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary w-100">Add to Cart</button>
            </div>
          </div>
        </div>
        
        <!-- 你可以根据数据库动态加载更多商品卡片 -->
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (necessary for dropdown, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
