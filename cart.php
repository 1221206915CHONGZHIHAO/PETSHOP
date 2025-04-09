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
              <li><a class="dropdown-item" href="admin_login.php">Login</a></li>
              <li><a class="dropdown-item" href="admin_register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

  <div class="container py-4">
    <h1 class="mb-4">Your Shopping Cart</h1>
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th scope="col" style="width: 80px;">Product</th>
          <th scope="col">Name</th>
          <th scope="col" style="width: 100px;">Price</th>
          <th scope="col" style="width: 150px;">Quantity</th>
          <th scope="col" style="width: 100px;">Subtotal</th>
          <th scope="col" style="width: 100px;">Actions</th>
        </tr>
      </thead>
      <tbody id="cart-items">
        <!-- 示例产品 1 -->
        <tr data-id="1">
          <td>
            <img src="ProBalance_tenderlamb.png" alt="Probalance Pouch Tender Lamb 100g" class="img-fluid">
          </td>
          <td>Probalance Pouch Tender Lamb 100g</td>
          <td class="price" data-price="4.50">RM4.50</td>
          <td>
            <div class="input-group quantity-group">
              <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
              <input type="number" class="form-control text-center quantity-input" value="1" min="1">
              <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
            </div>
          </td>
          <td class="subtotal">RM4.50</td>
          <td>
            <button class="btn btn-danger btn-remove">Remove</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="d-flex justify-content-end">
      <h4>Total: RM<span id="cart-total">4.50</span></h4>
    </div>
    <div class="text-end mt-4">
      <a href="checkout.html" class="btn btn-primary">Proceed to Checkout</a>
    </div>
  </div>

  <!-- Bootstrap JS Bundle (包含 Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- 购物车操作脚本 -->
  <script>
    // 更新单行小计
    function updateRowSubtotal(row) {
      const price = parseFloat(row.querySelector('.price').getAttribute('data-price'));
      const quantity = parseInt(row.querySelector('.quantity-input').value);
      const subtotalElem = row.querySelector('.subtotal');
      const subtotal = price * quantity;
      subtotalElem.textContent = 'RM' + subtotal.toFixed(2);
    }

    // 更新购物车总计
    function updateCartTotal() {
      let total = 0;
      document.querySelectorAll('#cart-items tr').forEach(row => {
        const price = parseFloat(row.querySelector('.price').getAttribute('data-price'));
        const quantity = parseInt(row.querySelector('.quantity-input').value);
        total += price * quantity;
      });
      document.getElementById('cart-total').textContent = total.toFixed(2);
    }

    // 数量变化处理
    function handleQuantityChange(event) {
      const row = event.target.closest('tr');
      if (row) {
        updateRowSubtotal(row);
        updateCartTotal();
      }
    }

    // 增加数量
    document.querySelectorAll('.btn-increase').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        const quantityInput = row.querySelector('.quantity-input');
        quantityInput.value = parseInt(quantityInput.value) + 1;
        updateRowSubtotal(row);
        updateCartTotal();
      });
    });

    // 减少数量
    document.querySelectorAll('.btn-decrease').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        const quantityInput = row.querySelector('.quantity-input');
        let currentVal = parseInt(quantityInput.value);
        if (currentVal > 1) {
          quantityInput.value = currentVal - 1;
          updateRowSubtotal(row);
          updateCartTotal();
        }
      });
    });

    // 数量输入框变化
    document.querySelectorAll('.quantity-input').forEach(input => {
      input.addEventListener('change', handleQuantityChange);
    });

    // 删除产品
    document.querySelectorAll('.btn-remove').forEach(button => {
      button.addEventListener('click', function() {
        const row = this.closest('tr');
        row.parentNode.removeChild(row);
        updateCartTotal();
      });
    });
  </script>
</body>
</html>
