<?php
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
        // Delete product
        elseif ($_POST['action'] === 'delete') {
            // Delete associated image first
            $result = $conn->query("SELECT image_url FROM products WHERE product_id = " . $_POST['product_id']);
            $imagePath = $result->fetch_assoc()['image_url'];
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }

            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $_POST['product_id']);
            $stmt->execute();
        }
        
        // Redirect to prevent form resubmission
        header("Location: staff_inventory.php");
        exit();
    }
}

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY updated_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
    <style>
        .product-img { 
            max-width: 80px; 
            max-height: 80px; 
            object-fit: cover; 
            border-radius: 4px;
        }
        .low-stock { 
            color: #dc3545; 
            font-weight: bold; 
        }
        .search-box {
            width: 250px;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-paw me-2"></i>PetShop Staff
        </a>
    </div>
    <div class="navbar-collapse justify-content-end">
        <ul class="navbar-nav">
            <li class="nav-item">
                <span class="nav-link text-light me-2">
                    <i class="fas fa-user-circle me-1"></i>Welcome, Staff
                </span>
            </li>
            <li class="nav-item">
                <a href="login.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="staff_example.png" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                    <h5 class="text-white mb-1">Staff Member</h5>
                    <small class="text-muted">Position</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="manage_orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="returns.php">
                                        <i class="fas fa-undo me-2"></i>Returns
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="customer_service.php">
                            <i class="fas fa-headset me-2"></i>Customer Service
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_email.php">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <span class="badge bg-danger float-end" id="messageCount">0</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_tasks.php">
                            <i class="fas fa-tasks me-2"></i>My Tasks
                            <span class="badge bg-primary float-end" id="taskCount">0</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="staff_inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    
                    <li class="nav-item mt-3">
                        <a class="nav-link text-light" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-boxes me-2"></i>Inventory
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="fas fa-plus me-1"></i>Add New Product
                    </button>
                    <div class="input-group search-box">
                        <input type="text" class="form-control form-control-sm" placeholder="Search inventory..." id="inventorySearch">
                        <button class="btn btn-sm btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-primary stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">TOTAL ITEMS</h6>
                                    <h2 class="mb-0"><?= count($products) ?></h2>
                                </div>
                                <i class="fas fa-boxes fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-success stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">IN STOCK</h6>
                                    <h2 class="mb-0">
                                        <?= count(array_filter($products, function($p) { return $p['stock_quantity'] > 10; })) ?>
                                    </h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-warning stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">LOW STOCK</h6>
                                    <h2 class="mb-0">
                                        <?= count(array_filter($products, function($p) { return $p['stock_quantity'] > 0 && $p['stock_quantity'] <= 10; })) ?>
                                    </h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">OUT OF STOCK</h6>
                                    <h2 class="mb-0">
                                        <?= count(array_filter($products, function($p) { return $p['stock_quantity'] <= 0; })) ?>
                                    </h2>
                                </div>
                                <i class="fas fa-times-circle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-warehouse me-2"></i>Current Inventory
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <button class="btn btn-sm btn-outline-primary" id="printBtn">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="inventoryTable">
                            <thead class="table-light">
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
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-img" alt="Product Image">
                                        <?php else: ?>
                                        <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td><?= htmlspecialchars($product['Category']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td class="<?= $product['stock_quantity'] < 5 ? 'low-stock' : '' ?>">
                                        <?= htmlspecialchars($product['stock_quantity']) ?>
                                    </td>
                                    <td>
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <span class="badge bg-success status-badge">In Stock</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <span class="badge bg-warning text-dark status-badge">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger status-badge">Out of Stock</span>
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
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">
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
                <div class="card-footer bg-white">
                    <nav aria-label="Inventory pagination">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
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
                                <option value="Dogs">Dogs</option>
                                <option value="Cats">Cats</option>
                                <option value="Birds">Birds</option>
                                <option value="Fish">Fish</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price*</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" required>
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
                    <!-- Content loaded dynamically -->
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

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Search functionality
    document.getElementById('inventorySearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#inventoryTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Export button
    document.getElementById('exportBtn').addEventListener('click', function() {
        alert('Export functionality would be implemented here');
    });

    // Print button
    document.getElementById('printBtn').addEventListener('click', function() {
        window.print();
    });
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
</script>
</body>
</html>