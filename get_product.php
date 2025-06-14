<?php
require_once 'db_connection.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID");
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found");
}

// Fetch all categories from pet_categories table
$categories = $conn->query("SELECT * FROM pet_categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
?>
<input type="hidden" name="action" value="update">
<input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Product Name*</label>
        <input type="text" name="product_name" class="form-control" 
               value="<?= htmlspecialchars($product['product_name']) ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Category*</label>
        <select name="category" class="form-select" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['category_name']) ?>" 
                    <?= $product['Category'] === $category['category_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Price*</label>
        <div class="input-group">
            <span class="input-group-text">RM</span>
            <input type="number" name="price" step="0.01" min="0" class="form-control" 
                   value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Stock Quantity*</label>
        <input type="number" name="stock_quantity" min="0" class="form-control" 
               value="<?= htmlspecialchars($product['stock_quantity']) ?>" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3"><?= 
        htmlspecialchars($product['description']) ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Current Image</label><br>
    <?php if ($product['image_url']): ?>
    <img src="<?= htmlspecialchars($product['image_url']) ?>" id="currentImage" 
         class="product-img rounded mb-2" alt="Current Image">
    <?php else: ?>
    <p class="text-muted">No image uploaded</p>
    <?php endif; ?>
    
    <label class="form-label">Update Image</label>
    <input type="file" name="product_image" class="form-control" 
           accept="image/*" onchange="previewImage(event, 'imagePreview')">
    <small class="text-muted">Leave blank to keep current image</small>
</div>