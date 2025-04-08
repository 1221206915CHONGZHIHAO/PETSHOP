<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        /* Custom modal styles */
        .action-modal .modal-header {
            background-color: #4e73df;
            color: white;
        }
        .delete-modal .modal-header {
            background-color: #dc3545;
        }
        .order-details {
            padding: 15px;
        }
        .order-details p {
            margin-bottom: 10px;
        }
        .order-details strong {
            display: inline-block;
            width: 120px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">PetShop Admin</a>
    </div>
    <div>
        <span class="text-light me-3">Welcome, Admin</span>
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
                                    <a class="nav-link text-light" href="staff_email.php">
                                        <i class="fas fa-envelope me-2"></i>Email Management
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light active" href="orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="order_history.php">
                                        <i class="fas fa-history me-2"></i>Order History
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-tag me-2"></i>Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-shopping-cart me-2"></i>Order Management</h1>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Manage Orders
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#1001</td>
                                    <td>John Doe</td>
                                    <td>Persian Cat (1)</td>
                                    <td>$1,200</td>
                                    <td>2025-03-01</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-order-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewOrderModal"
                                                data-order-id="1001"
                                                data-customer="John Doe"
                                                data-products="Persian Cat (1)"
                                                data-total="1200"
                                                data-date="2025-03-01"
                                                data-status="Completed">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-order-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteOrderModal"
                                                data-order-id="1001"
                                                data-customer="John Doe">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade action-modal" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel"><i class="fas fa-file-invoice me-2"></i>Order Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body order-details">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order ID:</strong> <span id="viewOrderId"></span></p>
                        <p><strong>Customer:</strong> <span id="viewCustomer"></span></p>
                        <p><strong>Order Date:</strong> <span id="viewOrderDate"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span id="viewOrderStatus" class="badge"></span></p>
                        <p><strong>Total Amount:</strong> $<span id="viewOrderTotal"></span></p>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3"><i class="fas fa-box-open me-2"></i>Products</h6>
                <p id="viewOrderProducts"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="printInvoiceBtn" href="#" target="_blank" class="btn btn-primary">Print Invoice</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-modal" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteOrderModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
                <p><strong>Order ID:</strong> <span id="deleteOrderId"></span></p>
                <p><strong>Customer:</strong> <span id="deleteCustomer"></span></p>
                <p class="text-danger"><small>This action cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteOrder">Delete Order</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Order Modal Handler
    const viewOrderModal = document.getElementById('viewOrderModal');
viewOrderModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    document.getElementById('viewOrderId').textContent = button.getAttribute('data-order-id');
    document.getElementById('viewCustomer').textContent = button.getAttribute('data-customer');
    document.getElementById('viewOrderProducts').textContent = button.getAttribute('data-products');
    document.getElementById('viewOrderTotal').textContent = button.getAttribute('data-total');
    document.getElementById('viewOrderDate').textContent = button.getAttribute('data-date');

    const statusBadge = document.getElementById('viewOrderStatus');
    statusBadge.textContent = button.getAttribute('data-status');
    statusBadge.className = 'badge bg-' + (button.getAttribute('data-status') === 'Completed' ? 'success' : 
                                          (button.getAttribute('data-status') === 'Processing' ? 'warning' : 'danger'));

    // ✅ 動態設定 print 按鈕連結
    document.getElementById('printInvoiceBtn').href = "invoice.php?order_id=" + button.getAttribute('data-order-id');
});


    // Delete Order Modal Handler
    const deleteOrderModal = document.getElementById('deleteOrderModal');
    deleteOrderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('deleteOrderId').textContent = button.getAttribute('data-order-id');
        document.getElementById('deleteCustomer').textContent = button.getAttribute('data-customer');
    });

    // Confirm Order Deletion
    document.getElementById('confirmDeleteOrder').addEventListener('click', function() {
        const orderId = document.getElementById('deleteOrderId').textContent;
        // Add actual deletion logic here
        alert('Order #' + orderId + ' will be deleted (implement AJAX request in production)');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(deleteOrderModal);
        modal.hide();
        
        // Add page refresh or table row removal logic here
    });
});
</script>
</body>
</html>