<?php
session_start();

if (!isset($_GET['order_id']) || !isset($_SESSION['customer_id'])) {
    header('Location: products.php');
    exit;
}

$order_id = intval($_GET['order_id']);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get shop settings
$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$settingsResult = $settingsQuery->get_result();

if ($settingsResult->num_rows > 0) {
    $shopSettings = $settingsResult->fetch_assoc();
}

// Get order items with product details and check for removed products
$stmt = $conn->prepare("
    SELECT o.Order_ID, o.order_date, o.PaymentMethod as payment_method, 
           o.status, o.Address as shipping_address,
           oi.quantity, oi.unit_price, oi.subtotal,
           p.product_id, p.product_name, p.image_url,
           (SELECT SUM(subtotal) FROM Order_Items WHERE order_id = o.Order_ID) as subtotal,
           CASE WHEN p.product_id IS NULL THEN 1 ELSE 0 END as is_removed
    FROM Orders o
    JOIN Order_Items oi ON o.Order_ID = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE o.Order_ID = ? AND o.Customer_ID = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate removed items count
$removed_items_count = 0;
foreach ($order_items as $item) {
    if ($item['is_removed']) {
        $removed_items_count++;
    }
}

// Get basic order info if items exist
if (!empty($order_items)) {
    $order = [
        'Order_ID' => $order_items[0]['Order_ID'],
        'order_date' => $order_items[0]['order_date'],
        'subtotal' => $order_items[0]['subtotal'],
        'shipping_fee' => 4.90,
        'total' => $order_items[0]['subtotal'] + 4.90,
        'payment_method' => $order_items[0]['payment_method'],
        'status' => $order_items[0]['status'],
        'shipping_address' => $order_items[0]['shipping_address'],
        'item_count' => count($order_items),
        'removed_items_count' => $removed_items_count
    ];
} else {
    header('Location: products.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hachi Pet Shop - Order Details</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/order_success.css">
  <link rel="stylesheet" href="userhomepage.css">
  <style>
    .success-container {
      max-width: 800px;
      margin: 0 auto;
    }
    .success-icon {
      color: #28a745;
      font-size: 5rem;
      margin-bottom: 1.5rem;
    }
    .order-details {
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .ordered-items {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }
    .ordered-items table {
      margin-bottom: 0;
    }
    .ordered-items th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
    .ordered-items .img-thumbnail {
      padding: 0;
      border: none;
      width: 80px;
      height: 80px;
      object-fit: cover;
    }
    .btn-success-page {
      padding: 10px 20px;
      margin: 0 10px;
    }
    .shipping-address {
      white-space: pre-wrap;
      background: white;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #dee2e6;
    }
    .total-row {
      font-weight: bold;
      background-color: #f8f9fa;
    }
    
    /* PDF Button Styles */
    .btn-pdf {
      background-color: #dc3545;
      color: white;
    }
    .btn-pdf:hover {
      background-color: #bb2d3b;
      color: white;
    }
    
    /* Hide elements during PDF generation */
    body.pdf-generation .action-buttons,
    body.pdf-generation nav,
    body.pdf-generation footer {
      display: none !important;
    }
    
    /* Styles for removed items */
    .removed-item {
      opacity: 0.7;
      background-color: #fff9e6;
    }
    .removed-item td {
      position: relative;
    }
    .removed-item td:first-child::before {
      content: "Removed Item";
      position: absolute;
      top: 0;
      left: 0;
      background-color: #ffc107;
      color: #856404;
      padding: 2px 8px;
      font-size: 12px;
      border-radius: 4px;
    }
    .removed-item-image {
      filter: grayscale(100%);
      opacity: 0.5;
    }
    .removed-item-name {
      text-decoration: line-through;
      color: #6c757d;
    }
    .removed-items-alert {
      background-color: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 0.75rem 1.25rem;
      margin-bottom: 1rem;
      border-radius: 4px;
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <div class="container">
      <a class="navbar-brand" href="userhomepage.php">
        <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
        </ul>

        <ul class="navbar-nav ms-auto nav-icons">
          <li class="nav-item">
            <a class="nav-link" href="cart.php">
              <i class="bi bi-cart"></i>
              <?php if(isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                  <?php echo $_SESSION['cart_count']; ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
          
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
              <?php else: ?>
                <i class="bi bi-person"></i>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if(isset($_SESSION['customer_id'])): ?>
                <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="logout.php?type=customer">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
              <?php else: ?>
                <li><a class="dropdown-item" href="login.php">Login</a></li>
                <li><a class="dropdown-item" href="register.php">Register</a></li>
              <?php endif; ?>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<!-- Page Content -->
<div class="page-content">
  <main class="container py-5">
    <div class="success-container text-center" id="pdf-content">
      <i class="bi bi-receipt success-icon"></i>
      <h1 class="mb-3">Order Details</h1>
      <p class="lead mb-4">Details for your order #<?php echo $order['Order_ID']; ?></p>
      
      <?php if ($order['removed_items_count'] > 0): ?>
        <div class="removed-items-alert text-start">
          <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
          <?php echo $order['removed_items_count']; ?> item(s) in this order have been removed and are no longer available
        </div>
      <?php endif; ?>
      
      <div class="order-details">
        <h3><i class="bi bi-receipt me-2"></i>Order Summary</h3>
        
        <div class="row text-start">
          <div class="col-md-6 mb-3">
            <p><strong>Order Number:</strong> #<?php echo $order['Order_ID']; ?></p>
            <p><strong>Date Placed:</strong> <?php echo date('F j, Y \a\t g:i a', strtotime($order['order_date'])); ?></p>
            <p><strong>Items:</strong> <?php echo $order['item_count']; ?></p>
            <p><strong>Status:</strong> <span class="badge bg-<?php 
              echo $order['status'] == 'Completed' ? 'success' : 
                   ($order['status'] == 'Processing' ? 'primary' : 'warning'); 
            ?>"><?php echo htmlspecialchars($order['status']); ?></span></p>
          </div>
          <div class="col-md-6 mb-3">
            <p><strong>Subtotal:</strong> RM<?php echo number_format($order['subtotal'], 2); ?></p>
            <p><strong>Shipping Fee:</strong> RM<?php echo number_format($order['shipping_fee'], 2); ?></p>
            <p><strong>Total Amount:</strong> RM<?php echo number_format($order['total'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Shipping Address:</strong></p>
            <div class="shipping-address"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
          </div>
        </div>
      </div>
      
      <div class="ordered-items">
        <h4><i class="bi bi-cart-check me-2"></i>Order Items</h4>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order_items as $item): ?>
              <tr class="<?php echo $item['is_removed'] ? 'removed-item' : ''; ?>">
                <td>
                  <div class="d-flex align-items-center">
                    <?php if ($item['is_removed']): ?>
                      <div class="img-thumbnail me-3 removed-item-image">
                        <i class="bi bi-box-seam" style="font-size: 3rem; color: #6c757d;"></i>
                      </div>
                    <?php else: ?>
                      <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                           alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                           class="img-thumbnail me-3">
                    <?php endif; ?>
                    <div>
                      <h6 class="<?php echo $item['is_removed'] ? 'removed-item-name' : ''; ?>">
                        <?php echo $item['is_removed'] ? 'Removed Product' : htmlspecialchars($item['product_name']); ?>
                      </h6>
                      <?php if (!$item['is_removed']): ?>
                        <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>RM<?php echo number_format($item['unit_price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>RM<?php echo number_format($item['subtotal'], 2); ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                <td><strong>RM<?php echo number_format($order['subtotal'], 2); ?></strong></td>
              </tr>
              <tr>
                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                <td><strong>RM<?php echo number_format($order['shipping_fee'], 2); ?></strong></td>
              </tr>
              <tr class="total-row">
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>RM<?php echo number_format($order['total'], 2); ?></strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="delivery-info alert alert-info">
        <i class="bi bi-truck me-2"></i>
        <p class="mb-0">We're preparing your order for shipment.</p>
      </div>
      
      <div class="action-buttons mt-5">
        <a href="products.php" class="btn btn-primary btn-success-page">
          <i class="bi bi-arrow-left me-2"></i>Continue Shopping
        </a>
        <a href="my_orders.php" class="btn btn-outline-secondary btn-success-page">
          <i class="bi bi-list-check me-2"></i>View Order History
        </a>
        <button id="downloadPdf" class="btn btn-pdf btn-success-page">
          <i class="bi bi-file-earmark-pdf me-2"></i>Download PDF
        </button>
      </div>
    </div>
  </main>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-5 mb-4 mb-lg-0">
        <div class="footer-about">
          <div class="footer-logo">
            <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
          </div>
          <p>Your trusted partner in pet care since 2015. We're dedicated to providing quality products and exceptional service for pet lovers everywhere.</p>
          <div class="social-links">
            <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="bi bi-facebook"></i></a>
            <a href="https://www.instagram.com/smal.l7018/"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
      </div>
      
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
    
    <div class="footer-bottom">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- PDF Export Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- PDF Export Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize jsPDF
    const { jsPDF } = window.jspdf;
    
    document.getElementById('downloadPdf').addEventListener('click', function() {
        // Show loading indicator
        const button = this;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass me-2"></i>Generating PDF...';
        button.disabled = true;
        
        // Add PDF generation class to body
        document.body.classList.add('pdf-generation');
        
        // Options for PDF generation
        const options = {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            logging: true,
            backgroundColor: '#FFFFFF',
            onclone: function(clonedDoc) {
                // This ensures buttons are hidden in the cloned version used for PDF
                clonedDoc.body.classList.add('pdf-generation');
            }
        };
        
        // Generate PDF from the content div
        html2canvas(document.getElementById('pdf-content'), options).then(canvas => {
            const pdf = new jsPDF('p', 'mm', 'a4');
            const imgData = canvas.toDataURL('image/png', 1.0);
            const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            
            // Add image to PDF
            pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth, pdfHeight);
            
            // Set document properties
            pdf.setProperties({
                title: 'Order Confirmation #<?php echo $order['Order_ID']; ?>',
                subject: 'Order Details from Hachi Pet Shop',
                author: 'Hachi Pet Shop',
                keywords: 'order, confirmation, receipt',
                creator: 'Hachi Pet Shop'
            });
            
            // Save the PDF
            pdf.save('Hachi_Order_<?php echo $order['Order_ID']; ?>.pdf');
            
            // Remove PDF generation class
            document.body.classList.remove('pdf-generation');
            
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }).catch(error => {
            console.error('Error generating PDF:', error);
            // Remove PDF generation class on error
            document.body.classList.remove('pdf-generation');
            button.innerHTML = originalText;
            button.disabled = false;
            alert('Error generating PDF. Please try again.');
        });
    });
});
</script>
</body>
</html>