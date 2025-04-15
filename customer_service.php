<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="staff.css">
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
                    <i class="fas fa-user-circle me-1"></i>Welcome, John
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
                    <h5 class="text-white mb-1">John Doe</h5>
                    <small class="text-muted">Customer Support</small>
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
                                    <a class="nav-link text-light" href="">
                                        <i class="fas fa-history me-2"></i>Order History
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
                        <a class="nav-link text-light active" href="customer_service.php">
                            <i class="fas fa-headset me-2"></i>Customer Service
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_email.php">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <span class="badge bg-danger float-end">3</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="staff_tasks.php">
                            <i class="fas fa-tasks me-2"></i>My Tasks
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
                    <i class="fas fa-headset me-2"></i>Customer Service
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Tickets</a></li>
                            <li><a class="dropdown-item" href="#">Open</a></li>
                            <li><a class="dropdown-item" href="#">In Progress</a></li>
                            <li><a class="dropdown-item" href="#">Resolved</a></li>
                            <li><a class="dropdown-item" href="#">Escalated</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                        <i class="fas fa-plus me-1"></i> New Ticket
                    </button>
                </div>
            </div>

            <!-- Customer Service Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-primary stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">OPEN TICKETS</h6>
                                    <h2 class="mb-0">15</h2>
                                </div>
                                <i class="fas fa-envelope-open fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">View All</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-success stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">RESOLVED TODAY</h6>
                                    <h2 class="mb-0">8</h2>
                                </div>
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-success bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">View Details</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-warning stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">IN PROGRESS</h6>
                                    <h2 class="mb-0">5</h2>
                                </div>
                                <i class="fas fa-spinner fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-warning bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">Continue Work</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">URGENT TICKETS</h6>
                                    <h2 class="mb-0">3</h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-danger bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">Handle Now</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search tickets...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" placeholder="From date">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" placeholder="To date">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Tickets -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-ticket-alt me-2"></i>Customer Support Tickets
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">CSV</a></li>
                            <li><a class="dropdown-item" href="#">Excel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#CS-1005</td>
                                    <td>Sarah Johnson</td>
                                    <td>Order not delivered - urgent!</td>
                                    <td>15 minutes ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" title="Assign">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#CS-1004</td>
                                    <td>Michael Brown</td>
                                    <td>Product damaged in shipping</td>
                                    <td>2 hours ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" title="Reply">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#CS-1003</td>
                                    <td>Emily Davis</td>
                                    <td>Return request for order #1002</td>
                                    <td>1 day ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Reopen">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#CS-1002</td>
                                    <td>Robert Wilson</td>
                                    <td>Payment issue - order not processed</td>
                                    <td>1 day ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" title="Assign">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
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

<!-- New Ticket Modal -->
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-labelledby="newTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTicketModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Create New Support Ticket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customerSelect" class="form-label">Customer</label>
                            <select class="form-select" id="customerSelect">
                                <option selected>Select customer</option>
                                <option>Sarah Johnson (sarah@example.com)</option>
                                <option>Michael Brown (michael@example.com)</option>
                                <option>Emily Davis (emily@example.com)</option>
                                <option>Robert Wilson (robert@example.com)</option>
                                <option>+ Add new customer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="relatedOrder" class="form-label">Related Order (optional)</label>
                            <select class="form-select" id="relatedOrder">
                                <option selected>Select order</option>
                                <option>#1005 - Sarah Johnson</option>
                                <option>#1004 - Michael Brown</option>
                                <option>#1003 - Emily Davis</option>
                                <option>#1002 - Robert Wilson</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ticketSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="ticketSubject" placeholder="Enter ticket subject">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ticketDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="ticketDescription" rows="4" placeholder="Describe the issue in detail..."></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ticketPriority" id="priorityLow">
                                <label class="form-check-label" for="priorityLow">
                                    Low
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ticketPriority" id="priorityMedium" checked>
                                <label class="form-check-label" for="priorityMedium">
                                    Medium
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ticketPriority" id="priorityHigh">
                                <label class="form-check-label" for="priorityHigh">
                                    High
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="assignTo" class="form-label">Assign To</label>
                            <select class="form-select" id="assignTo">
                                <option selected>Assign to me</option>
                                <option>Support Team 1</option>
                                <option>Support Team 2</option>
                                <option>Manager</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Attachments</label>
                        <div class="border p-3 text-center">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Drag files here or click to browse</p>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-folder-open me-1"></i> Browse Files
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Ticket</button>
            </div>
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
    
    // Ticket row click handler
    const ticketRows = document.querySelectorAll('tbody tr');
    ticketRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn')) {
                // Here you would typically open the ticket detail modal
                const ticketModal = new bootstrap.Modal(document.getElementById('ticketDetailModal'));
                ticketModal.show();
            }
        });
    });
});
</script>

</body>
</html>