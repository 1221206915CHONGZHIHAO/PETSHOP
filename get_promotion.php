<?php
require_once 'db_connection.php';

if (isset($_GET['code'])) {
    $promoCode = $conn->real_escape_string($_GET['code']);
    $result = $conn->query("SELECT * FROM promotion WHERE promo_code = '$promoCode'");
    $promo = $result->fetch_assoc();

    if ($promo) {
        ?>
        <div class="mb-3">
            <label class="form-label">Promo Code</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($promo['promo_code']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Discount (%)*</label>
            <input type="number" name="discount" min="1" max="100" class="form-control" 
                   value="<?= htmlspecialchars($promo['discount']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Start Date*</label>
            <input type="date" name="start_date" class="form-control" 
                   value="<?= htmlspecialchars($promo['start_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">End Date*</label>
            <input type="date" name="end_date" class="form-control" 
                   value="<?= htmlspecialchars($promo['end_date']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Usage Limit (0 for unlimited)</label>
            <input type="number" name="usage_limit" min="0" class="form-control" 
                   value="<?= htmlspecialchars($promo['usage_limit']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Status*</label>
            <select name="status" class="form-select" required>
                <option value="Active" <?= $promo['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $promo['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <?php
    }
}
?>