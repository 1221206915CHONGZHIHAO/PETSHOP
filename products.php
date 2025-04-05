<?php
require_once 'db.php';

// =========== FETCH DISTINCT CATEGORIES ===========
// If you store categories in the `Category` column, you can fetch them like this:
$categoryStmt = $pdo->query("SELECT DISTINCT Category FROM products");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// =========== FILTERING LOGIC ===========
// We'll capture any category filter from GET parameters
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// We'll capture any sorting parameter from GET
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'best_selling';

// Base query
$query = "SELECT * FROM products";

// If a category is selected, add a WHERE clause
if ($selectedCategory) {
    $query .= " WHERE Category = :cat";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    default:
        // 'best_selling' is just a placeholder. If you have a field or logic for "best selling",
        // you'd order by that field. Otherwise, let's default to newest or product_name, etc.
        $query .= " ORDER BY product_id DESC";
        break;
}

$stmt = $pdo->prepare($query);

// Bind category if needed
if ($selectedCategory) {
    $stmt->bindValue(':cat', $selectedCategory);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pet Shop - Product Listing</title>
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS (optional) -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Pet Shop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
       aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
       <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<!-- MAIN CONTAINER -->
<div class="container-fluid mt-4">
  <div class="row">
    <!-- SIDEBAR -->
    <div class="col-md-3">
      <h5>Categories</h5>
      <ul class="list-group mb-4">
        <?php foreach ($categories as $cat): ?>
          <li class="list-group-item <?php if($cat === $selectedCategory) echo 'active'; ?>">
            <a href="?category=<?= urlencode($cat) ?>" 
               class="<?php if($cat === $selectedCategory) echo 'text-white'; ?>">
              <?= htmlspecialchars($cat) ?>
            </a>
          </li>
        <?php endforeach; ?>
        <!-- Optional: A link to clear the filter -->
        <li class="list-group-item">
          <a href="index.php">All Categories</a>
        </li>
      </ul>

      <!-- If you had a brand filter, you could replicate a similar structure here. -->
      <!-- <h5>Brands</h5>
      <ul class="list-group">
        ...
      </ul> -->
    </div>

    <!-- PRODUCT LISTING -->
    <div class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Products</h2>
        <form method="get" class="d-flex">
          <?php if ($selectedCategory): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
          <?php endif; ?>
          <label for="sort" class="me-2 align-self-center">Sort By:</label>
          <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
            <option value="best_selling" <?php if($sort === 'best_selling') echo 'selected'; ?>>Best Selling</option>
            <option value="price_asc" <?php if($sort === 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
            <option value="price_desc" <?php if($sort === 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
          </select>
        </form>
      </div>

      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if($products): ?>
          <?php foreach ($products as $product): ?>
            <div class="col">
              <div class="card h-100">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>" 
                     style="object-fit: cover; height: 200px;">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                  <p class="card-text text-muted mb-2">$<?= number_format($product['price'], 2) ?></p>
                  <p class="card-text small text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                  <div class="mt-auto">
                    <a href="product.php?id=<?= $product['product_id'] ?>" 
                       class="btn btn-primary w-100">View Details</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No products found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
