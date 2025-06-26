<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php?redirect=inventory.php");
    exit();
}

require_once 'db_connection.php';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new product
        if ($_POST['action'] === 'add') {
            $imagePath = null;
            if (!empty($_FILES['product_image']['name'])) {
                $targetDir = "uploads/";
                $imagePath = $targetDir . basename($_FILES['product_image']['name']);
                move_uploaded_file($_FILES['product_image']['tmp_name'], $imagePath);
            }

            $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, image_url, stock_quantity, category) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsis", 
                $_POST['product_name'],
                $_POST['description'],
                $_POST['price'],
                $imagePath,
                $_POST['stock_quantity'],
                $_POST['category']
            );
            $stmt->execute();
        }
        // Update product
        elseif ($_POST['action'] === 'update') {
            // Get current image first
            $result = $conn->query("SELECT image_url FROM products WHERE product_id = " . $_POST['product_id']);
            $currentImage = $result->fetch_assoc()['image_url'];
            
            $imagePath = $currentImage;
            if (!empty($_FILES['product_image']['name'])) {
                $targetDir = "uploads/";
                $imagePath = $targetDir . basename($_FILES['product_image']['name']);
                move_uploaded_file($_FILES['product_image']['tmp_name'], $imagePath);
                
                // Delete old image if it exists
                if ($currentImage && file_exists($currentImage)) {
                    unlink($currentImage);
                }
            }

            $stmt = $conn->prepare("UPDATE products SET 
                                  product_name = ?,
                                  description = ?,
                                  price = ?,
                                  image_url = ?,
                                  stock_quantity = ?,
                                  category = ?
                                  WHERE product_id = ?");
            $stmt->bind_param("ssdsisi",
                $_POST['product_name'],
                $_POST['description'],
                $_POST['price'],
                $imagePath,
                $_POST['stock_quantity'],
                $_POST['category'],
                $_POST['product_id']
            );
            $stmt->execute();
        }
        // Delete product - MODIFIED FOR SOFT DELETE
        elseif ($_POST['action'] === 'delete') {
            // Instead of deleting, we set stock_quantity to -1 to mark as deleted
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = -1 WHERE product_id = ?");
            $stmt->bind_param("i", $_POST['product_id']);
            $stmt->execute();
            
            // We keep the image file for historical reference
        }
        
        // Redirect to prevent form resubmission
        header("Location: inventory.php");
        exit();
    }
}

// Fetch all active products (stock_quantity >= 0) - MODIFIED TO EXCLUDE SOFT DELETED ITEMS
$products = $conn->query("SELECT * FROM products WHERE stock_quantity >= 0 ORDER BY updated_at DESC")->fetch_all(MYSQLI_ASSOC);

$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .product-img { max-width: 80px; max-height: 80px; object-fit: cover; }
        .low-stock { color: #dc3545; font-weight: bold; }
        .sidebar { min-height: 100vh; }
        .sidebar.collapsed { display: none; }
        @media (min-width: 768px) {
            .sidebar.collapsed { display: block; }
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        .section-title:after {
            content: '';
            display: block;
            height: 4px;
            width: 70px;
            background-color: var(--primary);
            margin-top: 0.5rem;
        }
        /* New style for delete confirmation message */
        .delete-confirm-text {
            color: #d33;
            font-weight: bold;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <img src="Hachi_Logo.png" alt="PetShop Admin" height="40">
        </a>
    </div>
    <div>
        <span class="text-light me-3"><i class="fas fa-user-circle me-1"></i> Welcome, <?php echo $_SESSION['username'] ?? 'ADMIN'; ?></span>
        <a href="admin_login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-2 d-md-block bg-dark sidebar">
            <div class="position-sticky">
                <h4 class="text-light text-center py-3"><i class="fas fa-paw me-2"></i>Admin Menu</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#staffMenu">
                            <i class="fas fa-users me-2"></i>Staff Management
                        </a>
                        <div class="collapse" id="staffMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="manage_staff.php">
                                        <i class="fas fa-list me-2"></i>Staff List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="staff_logs.php">
                                        <i class="fas fa-history me-2"></i>Login/Logout Logs
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#customerMenu">
                            <i class="fas fa-user-friends me-2"></i>Customer Management
                        </a>
                        <div class="collapse" id="customerMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="customer_list.php">
                                        <i class="fas fa-list me-2"></i>Customer List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="customer_logs.php">
                                        <i class="fas fa-history me-2"></i>Login/Logout Logs
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="orders.php?show_disabled=1">
                                        <i class="fas fa-ban me-2"></i>Disabled Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_setting.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-boxes me-2"></i>Inventory Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </button>
            </div>

            <!-- Inventory Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-warehouse me-2"></i>Current Products
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['product_id']) ?></td>
                                    <td>
                                        <?php if ($product['image_url']): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-img rounded" alt="Product Image">
                                        <?php else: ?>
                                        <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td>
                                        <?php 
                                        $category = htmlspecialchars($product['Category']);
                                        if (strpos($category, '>') !== false) {
                                            $parts = explode('>', $category);
                                            echo trim($parts[1]);
                                        } else {
                                            echo $category;
                                        }
                                        ?>
                                    </td>
                                    <td>RM<?= number_format($product['price'], 2) ?></td>
                                    <td class="<?= $product['stock_quantity'] < 5 ? 'low-stock' : '' ?>">
                                        <?= htmlspecialchars($product['stock_quantity']) ?>
                                    </td>
                                    <td>
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($product['updated_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                            data-bs-target="#editModal" 
                                            data-id="<?= $product['product_id'] ?>"
                                            onclick="loadEditForm(this)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display:inline;" class="delete-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name*</label>
                            <input type="text" name="product_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category*</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories = $conn->query("SELECT * FROM pet_categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
                                foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['category_name']) ?>">
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
                                <input type="number" name="price" step="0.01" min="1" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity*</label>
                            <input type="number" name="stock_quantity" min="0" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="product_image" class="form-control" accept="image/*">
                        <small class="text-muted">Maximum file size: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <div class="modal-body">
                    <!-- Content loaded dynamically from get_product.php -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 offset-md-2">
                <div class="row">
                    <!-- Footer About -->
                    <div class="col-md-5 mb-4 mb-lg-0">
                        <div class="footer-about">
                            <div class="footer-logo">
                                <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
                            </div>
                            <p>Your trusted partner in pet products. We're dedicated to providing quality products for pet lovers everywhere.</p>
                            <div class="social-links">
                                <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="fab fa-facebook"></i></a>
                                <a href="https://www.instagram.com/smal.l7018/"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-md-7">
                        <h4 class="footer-title">Contact Us</h4>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?php echo !empty($shopSettings['address']) ? htmlspecialchars($shopSettings['address']) : 'Address not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-telephone"></i>
                                    <span><?php echo !empty($shopSettings['phone_number']) ? htmlspecialchars($shopSettings['phone_number']) : 'Phone number not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-envelope"></i>
                                    <span><?php echo !empty($shopSettings['contact_email']) ? htmlspecialchars($shopSettings['contact_email']) : 'Email not available'; ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="contact-info">
                                    <i class="bi bi-clock"></i>
                                    <span><?php echo !empty($shopSettings['opening_hours']) ? htmlspecialchars($shopSettings['opening_hours']) : 'Opening hours not available'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer Bottom -->
                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-12 text-center">
                            <p class="mb-0 text-white">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert for delete confirmation - MODIFIED FOR SOFT DELETE
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                html: '<div class="delete-confirm-text">This will hide the product from the store but keep it in the database for order history.</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Load edit form via AJAX
    function loadEditForm(button) {
        const productId = button.getAttribute('data-id');
        fetch('get_product.php?id=' + productId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editForm').querySelector('.modal-body').innerHTML = html;
            });
    }

    // Image preview for edit form
    function previewImage(event, previewId) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById(previewId);
            preview.src = reader.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    document.querySelector('#addItemModal form').addEventListener('submit', function(e) {
        const priceInput = this.querySelector('input[name="price"]');
        const price = parseFloat(priceInput.value);
        if (isNaN(price) || price < 1) {
            alert('Price must be at least 1.');
            priceInput.focus();
            e.preventDefault();
        }
    });
</script>
</body>
</html>