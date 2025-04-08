<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shopping Cart - Pet Shop</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- 共享的CSS文件（如 navbar 样式） -->
  <link rel="stylesheet" href="product_page.css">
  <!-- 若你有单独的购物车CSS，也可以单独引入 -->
  <style>
    /* 如需额外调整购物车页面样式，可在此添加 */
    .quantity-group {
      max-width: 120px;
      margin: 0 auto;
    }
    .quantity-group input {
      text-align: center;
    }
  </style>
</head>
<body>
  <!-- 引入统一导航栏（可使用与你用户主页一致的代码） -->
  <nav class="navbar navbar-expand-lg navbar-dark custom-nav">
    <div class="container">
      <a class="navbar-brand" href="userhomepage.php">
        <img src="cat_paw.png" alt="Pet Shop" width="50">
        <span>Pet Shop</span>
      </a>
      <!-- 省略其他导航内容... -->
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
            <img src="https://via.placeholder.com/80" alt="Probalance Pouch 100g" class="img-fluid">
          </td>
          <td>Probalance Pouch 100g</td>
          <td class="price" data-price="120">\$120</td>
          <td>
            <div class="input-group quantity-group">
              <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
              <input type="number" class="form-control text-center quantity-input" value="1" min="1">
              <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
            </div>
          </td>
          <td class="subtotal">\$120</td>
          <td>
            <button class="btn btn-danger btn-remove">Remove</button>
          </td>
        </tr>
        <!-- 示例产品 2 -->
        <tr data-id="2">
          <td>
            <img src="https://via.placeholder.com/80" alt="Probalance Gourmet" class="img-fluid">
          </td>
          <td>Probalance Gourmet</td>
          <td class="price" data-price="150">\$150</td>
          <td>
            <div class="input-group quantity-group">
              <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
              <input type="number" class="form-control text-center quantity-input" value="1" min="1">
              <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
            </div>
          </td>
          <td class="subtotal">\$150</td>
          <td>
            <button class="btn btn-danger btn-remove">Remove</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="d-flex justify-content-end">
      <h4>Total: \$<span id="cart-total">270</span></h4>
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
      subtotalElem.textContent = '$' + subtotal.toFixed(2);
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
