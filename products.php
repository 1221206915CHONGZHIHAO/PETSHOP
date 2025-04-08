<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pet Shop - Product List</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS（如有需要可自行创建） -->
  <link rel="stylesheet" href="userhomepage.css">
  
  <style>
    /* 你可以在此处编写特定的定制样式，也可在单独的 CSS 文件中编写 */
    .custom-nav {
      background-color: #343a40; /* 与 userhomepage.css 保持一致或按需修改 */
    }
    .filter-section h5 {
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .filter-section .list-group-item {
      border: none; /* 移除默认边框，风格可自行调整 */
      padding: 0.3rem 0;
    }
    .filter-section .list-group-item a {
      text-decoration: none;
      color: #333;
    }
    .filter-section .list-group-item a:hover {
      color: #007bff;
    }
    .product-card {
      transition: transform 0.2s;
    }
    .product-card:hover {
      transform: scale(1.02);
    }
    .card-img-top {
      height: 200px; /* 示例固定高度，实际可根据需求调整 */
      object-fit: cover;
    }
    /* 面包屑、筛选区域等可根据需求调整 */
  </style>
</head>
<body>

<!-- ========== NAVBAR (from userhomepage.php) ========== -->
<nav class="navbar navbar-expand-lg navbar-dark custom-nav">
  <div class="container">
    <!-- Brand on the left -->
    <a class="navbar-brand" href="userhomepage.php">
      <img src="cat_paw.png" alt="Pet Shop" width="50">
      <span>Pet Shop</span>
    </a>

    <!-- Toggler for mobile view -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main nav links centered -->
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link active" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Shop</a></li>
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
              <li class="dropdown-item-text">
                <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
              </li>
              <li><a class="dropdown-item" href="account_setting.php">Account Settings</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="admin_login.php">Login</a></li>
              <li><a class="dropdown-item" href="admin_register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- ========== END NAVBAR ========== -->

<!-- Main Container -->
<div class="container py-4">
  <!-- 如果需要面包屑，可自行添加： 
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="userhomepage.php">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Shop</li>
    </ol>
  </nav>
  -->

  <!-- 左侧过滤栏 + 右侧商品列表 -->
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
        <p class="mb-0">814 items found for Food &amp; Treats</p>
        
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
              <p class="card-text">With Veggie in Gravy Wet Dog Food</p>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary w-100">Add to Cart</button>
            </div>
          </div>
        </div>
        
        <!-- 示例卡片 2 -->
        <div class="col">
          <div class="card product-card h-100">
            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Probalance Gourmet">
            <div class="card-body">
              <h5 class="card-title">Probalance Gourmet</h5>
              <p class="card-text">Selection 100g Wet Dog Food</p>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary w-100">Add to Cart</button>
            </div>
          </div>
        </div>

        <!-- 示例卡片 3 -->
        <div class="col">
          <div class="card product-card h-100">
            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Probalance 700g">
            <div class="card-body">
              <h5 class="card-title">Probalance 700g</h5>
              <p class="card-text">In Loaf Wet Dog Food</p>
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
