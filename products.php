<?php
// Connect to the database using PDO
$pdo = new PDO("mysql:host=localhost;dbname=petshop", "username", "password");

// Fetch all products from the 'products' table
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pet Shop - Home</title>
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
    <header>
      <h1>Our Pet Products</h1>
    </header>
    <main>
      <div class="product-grid">
          <?php foreach($products as $product): ?>
              <div class="product-card">
                  <a href="product.php?id=<?= $product['product_id'] ?>">
                      <img src="<?= $product['image_url'] ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                      <h2><?= htmlspecialchars($product['product_name']) ?></h2>
                  </a>
              </div>
          <?php endforeach; ?>
      </div>
    </main>
</body>
</html>
