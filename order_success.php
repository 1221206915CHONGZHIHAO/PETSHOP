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

// Get order items with product details
$stmt = $conn->prepare("
    SELECT o.Order_ID, o.order_date, o.PaymentMethod as payment_method, 
           o.status, o.Address as shipping_address,
           oi.quantity, oi.unit_price, oi.subtotal,
           p.product_id, p.product_name, p.image_url,
           (SELECT SUM(subtotal) FROM Order_Items WHERE order_id = o.Order_ID) as Total
    FROM Orders o
    JOIN Order_Items oi ON o.Order_ID = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.Order_ID = ? AND o.Customer_ID = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get basic order info if items exist
if (!empty($order_items)) {
    $order = [
        'Order_ID' => $order_items[0]['Order_ID'],
        'order_date' => $order_items[0]['order_date'],
        'Total' => $order_items[0]['Total'],
        'payment_method' => $order_items[0]['payment_method'],
        'status' => $order_items[0]['status'],
        'shipping_address' => $order_items[0]['shipping_address'],
        'item_count' => count($order_items)
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
  <title>Hachi Pet Shop - Order Confirmation</title>
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
    
    /* PDF Button Styles */
    .btn-pdf {
      background-color: #dc3545;
      color: white;
    }
    .btn-pdf:hover {
      background-color: #bb2d3b;
      color: white;
    }
    
    /* Hide elements in PDF */
    .hide-in-pdf {
      display: none !important;
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg custom-nav fixed-top">
    <!-- Your existing navigation code -->
</nav>

<!-- Page Content -->
<div class="page-content">
  <main class="container py-5">
    <div class="success-container text-center" id="pdf-content">
      <i class="bi bi-check-circle-fill success-icon"></i>
      <h1 class="mb-3">Thank You for Your Order!</h1>
      <p class="lead mb-4">Your order #<?php echo $order['Order_ID']; ?> has been confirmed.</p>
      
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
            <p><strong>Total Amount:</strong> RM<?php echo number_format($order['Total'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Shipping Address:</strong></p>
            <div class="shipping-address"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
          </div>
        </div>
      </div>
      
      <div class="ordered-items">
        <h4><i class="bi bi-cart-check me-2"></i>Purchased Items</h4>
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
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                         class="img-thumbnail me-3">
                    <div>
                      <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                      <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                    </div>
                  </div>
                </td>
                <td>RM<?php echo number_format($item['unit_price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>RM<?php echo number_format($item['subtotal'], 2); ?></td>
              </tr>
              <?php endforeach; ?>
              <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>RM<?php echo number_format($order['Total'], 2); ?></strong></td>
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
        <a href="my_orders.php?order_id=<?php echo $order['Order_ID']; ?>" class="btn btn-outline-secondary btn-success-page">
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
    <!-- Your existing footer code -->
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
        
        // Create a clone of the content to modify for PDF
        const element = document.getElementById('pdf-content');
        const clone = element.cloneNode(true);
        
        // Hide all buttons in the clone
        clone.querySelectorAll('.btn-success-page').forEach(btn => {
            btn.classList.add('hide-in-pdf');
        });
        
        // Hide navigation and footer in the clone
        clone.querySelectorAll('nav, footer').forEach(el => {
            el.classList.add('hide-in-pdf');
        });
        
        // Temporarily append the clone to the document
        clone.style.position = 'absolute';
        clone.style.left = '-9999px';
        document.body.appendChild(clone);
        
        // Options for better quality PDF
        const options = {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            logging: true,
            backgroundColor: '#FFFFFF'
        };
        
        // Convert to canvas then to PDF
        html2canvas(clone, options).then(canvas => {
            // Remove the clone
            document.body.removeChild(clone);
            
            // Create PDF
            const pdf = new jsPDF('p', 'mm', 'a4');
            const imgData = canvas.toDataURL('image/png', 1.0);
            
            // Calculate dimensions
            const pdfWidth = pdf.internal.pageSize.getWidth() - 20; // 10mm margins
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
            
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }).catch(error => {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
            
            // Remove the clone if still exists
            if (document.body.contains(clone)) {
                document.body.removeChild(clone);
            }
            
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });
});
</script>
</body>
</html>