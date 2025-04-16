<?php
// Start the session
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "petshop"; // Your existing database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get products from database
function getProducts($sort = 'newest') {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE Category LIKE '%Food%' OR Category LIKE '%Treats%'";
    
    // Add sorting logic
    switch ($sort) {
        case 'lowest-price':
            $sql .= " ORDER BY price ASC";
            break;
        case 'highest-price':
            $sql .= " ORDER BY price DESC";
            break;
        case 'best-selling': // We'll need to implement this logic if you have sales data
            $sql .= " ORDER BY updated_at DESC"; // Using updated_at as a fallback
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
    
    $sql = "SELECT DISTINCT Category FROM products WHERE Category LIKE '%Dog%' ORDER BY Category";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row['Category'];
        }
    }
    
    return $categories;
}

// Get sort parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get products and categories
$products = getProducts($sort);
$categories = getCategories();

// Count items
$product_count = count($products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Products</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Toastr CSS for notifications -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
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
        <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link active" href="products.php">Product</a></li>
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
    <!-- Filter sidebar -->
    <aside class="col-md-3 filter-section">
      <!-- Categories -->
      <h5>Categories</h5>
      <ul class="list-group mb-4">
        <?php foreach($categories as $category): ?>
          <li class="list-group-item">
            <a href="products.php?category=<?php echo urlencode($category); ?>">
              <?php echo htmlspecialchars($category); ?>
            </a>
          </li>
        <?php endforeach; ?>
        <?php if(empty($categories)): ?>
          <li class="list-group-item">No categories found</li>
        <?php endif; ?>
      </ul>
    </aside>

    <!-- Product listing -->
    <div class="col-md-9">
      <!-- Top info and sorting -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0"><?php echo $product_count; ?> items found for Food &amp; Treats</p>
        
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

      <!-- Product cards -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
        <?php if(!empty($products)): ?>
          <?php foreach($products as $product): ?>
            <div class="col">
              <div class="card product-card h-100">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                  <p class="product-price">Price: RM<?php echo number_format($product['price'], 2); ?></p>
                  <?php if($product['stock_quantity'] > 0): ?>
                    <p class="text-success small">In Stock (<?php echo $product['stock_quantity']; ?>)</p>
                  <?php else: ?>
                    <p class="text-danger small">Out of Stock</p>
                  <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                  <button class="btn btn-primary w-100 add-to-cart-btn" 
                          data-product-id="<?php echo $product['product_id']; ?>" 
                          data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                          <?php echo ($product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              No products found. Please try different filters.
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

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