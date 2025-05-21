<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connection.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $promoCode = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM promotion WHERE promo_code = '$promoCode'");
    $_SESSION['message'] = "Promotion deleted successfully";
    $_SESSION['message_type'] = "success";
    header("Location: promotion.php");
    exit;
}

// Fetch all promotions
$promotion = [];
$result = $conn->query("SELECT * FROM promotion ORDER BY start_date DESC");
if ($result) {
    $promotion = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
         <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .sidebar { min-height: 100vh; }
        .sidebar.collapsed { display: none; }
        @media (min-width: 768px) {
            .sidebar.collapsed { display: block; }
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
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
        <span class="text-light me-3"><i class="fas fa-user-circle me-1"></i> Welcome, <?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                        <a class="nav-link text-light " href="admin_homepage.php">
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
                        <a class="nav-link text-light active" href="promotion.php">
                            <i class="fas fa-tag me-2"></i>Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="inventory.php">
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

<main class="col-md-10 ms-sm-auto px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-tag me-2"></i>Promotion Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromoModal">
            <i class="fas fa-plus me-2"></i>Create Promotion
        </button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <!-- Promotions Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-fire me-2"></i>Promotions
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Promo Code</th>
                            <th>Discount (%)</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Usage Limit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promotion as $promo): 
                            $currentDate = date('Y-m-d');
                            $status = $promo['status'];
                            
                            // Auto-update status if needed
                            if ($status === 'Active') {
                                if ($currentDate > $promo['end_date']) {
                                    $status = 'Expired';
                                    $conn->query("UPDATE promotion SET status = 'inactive' WHERE promo_code = '{$promo['promo_code']}'");
                                } elseif ($currentDate < $promo['start_date']) {
                                    $status = 'Pending';
                                }
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($promo['promo_code']) ?></td>
                            <td><?= htmlspecialchars($promo['discount']) ?>%</td>
                            <td><?= date('M j, Y', strtotime($promo['start_date'])) ?></td>
                            <td><?= date('M j, Y', strtotime($promo['end_date'])) ?></td>
                            <td><?= $promo['usage_limit'] ? htmlspecialchars($promo['usage_limit']) : 'Unlimited' ?></td>
                            <td>
                                <?php if ($status === 'Active'): ?>
                                    <span class="badge bg-success status-badge">Active</span>
                                <?php elseif ($status === 'inactive'): ?>
                                    <span class="badge bg-secondary status-badge">Inactive</span>
                                <?php elseif ($status === 'Expired'): ?>
                                    <span class="badge bg-warning text-dark status-badge">Expired</span>
                                <?php else: ?>
                                    <span class="badge bg-info status-badge">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                    data-bs-target="#editPromoModal" 
                                    onclick="loadPromoData('<?= $promo['promo_code'] ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="promotion.php?delete=<?= $promo['promo_code'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this promotion?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($promotion)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No promotions found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Promotion Modal -->
<div class="modal fade" id="addPromoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="save_promotion.php">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Promo Code*</label>
                        <input type="text" name="promo_code" class="form-control" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount (%)*</label>
                        <input type="number" name="discount" min="1" max="100" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date*</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date*</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usage Limit (0 for unlimited)</label>
                        <input type="number" name="usage_limit" min="0" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Promotion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Promotion Modal -->
<div class="modal fade" id="editPromoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="save_promotion.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="promo_code" id="editPromoCode">
                <div class="modal-body" id="editPromoContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
          <!-- Footer with same style as user homepage -->
  <footer style="background: linear-gradient(to bottom,rgb(134, 138, 135),rgba(46, 21, 1, 0.69));">
    <div class="container">
      <div class="row">
        <!-- Footer About -->
        <div class="col-md-5 mb-4 mb-lg-0">
          <div class="footer-about">
            <div class="footer-logo">
              <img src="Hachi_Logo.png" alt="Hachi Pet Shop" height="60">
            </div>
            <p>Your trusted partner in pet product. We're dedicated to providing quality products for pet lovers everywhere.</p>
            <div class="social-links">
              <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="fab fa-facebook"></i></a>
              <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
        
        <!-- Contact Info -->
        <div class="col-md-7">
          <h4 class="text-white mb-3">Contact Us</h4>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-map-marker-alt"></i>
                <span>123 Pet Street, Animal City</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-phone"></i>
                <span>+1 (555) 123-4567</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-envelope"></i>
                <span>info@hachipetshop.com</span>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="contact-info">
                <i class="fas fa-clock"></i>
                <span>Mon-Fri: 9AM - 6PM</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer Bottom -->
      <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: 40px; padding-top: 20px;">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-md-0 text-white">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });
});

function loadPromoData(promoCode) {
    fetch('get_promotion.php?code=' + promoCode)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editPromoContent').innerHTML = html;
            document.getElementById('editPromoCode').value = promoCode;
        });
}
</script>
</body>
</html>