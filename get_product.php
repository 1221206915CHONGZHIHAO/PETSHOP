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
            <optgroup label="Dogs">
                <option value="Dogs" <?= $product['Category'] === 'Dogs' ? 'selected' : '' ?>>Dogs (General)</option>
                <option value="Dog > Dry Food" <?= $product['Category'] === 'Dog > Dry Food' ? 'selected' : '' ?>>Dog > Dry Food</option>
                <option value="Dog > Treats" <?= $product['Category'] === 'Dog > Treats' ? 'selected' : '' ?>>Dog > Treats</option>
                <option value="Dog > Wet Food" <?= $product['Category'] === 'Dog > Wet Food' ? 'selected' : '' ?>>Dog > Wet Food</option>
            </optgroup>
            <option value="Cats" <?= $product['Category'] === 'Cats' ? 'selected' : '' ?>>Cats</option>
            <option value="Birds" <?= $product['Category'] === 'Birds' ? 'selected' : '' ?>>Birds</option>
            <option value="Fish" <?= $product['Category'] === 'Fish' ? 'selected' : '' ?>>Fish</option>
            <option value="Other" <?= $product['Category'] === 'Other' ? 'selected' : '' ?>>Other</option>
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