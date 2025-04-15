<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PetShop</title>
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
                    <small class="text-muted">Staff Member</small>
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
                        <a class="nav-link text-light" href="customer_service.php">
                            <i class="fas fa-headset me-2"></i>Customer Service
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="staff_email.php">
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
                    <i class="fas fa-envelope me-2"></i>Order Messages
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Messages</a></li>
                            <li><a class="dropdown-item" href="#">Unread</a></li>
                            <li><a class="dropdown-item" href="#">Order Related</a></li>
                            <li><a class="dropdown-item" href="#">Customer Support</a></li>
                            <li><a class="dropdown-item" href="#">Internal</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="fas fa-plus me-1"></i> New Message
                    </button>
                </div>
            </div>

            <!-- Message Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-primary stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">UNREAD MESSAGES</h6>
                                    <h2 class="mb-0">8</h2>
                                </div>
                                <i class="fas fa-envelope fa-3x"></i>
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
                                    <h6 class="card-title">REPLIED TODAY</h6>
                                    <h2 class="mb-0">12</h2>
                                </div>
                                <i class="fas fa-reply fa-3x"></i>
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
                                    <h6 class="card-title">PENDING RESPONSE</h6>
                                    <h2 class="mb-0">5</h2>
                                </div>
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-warning bg-opacity-10 d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">Respond Now</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">URGENT MESSAGES</h6>
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

            <div class="row">
                <!-- Message List -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search messages...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0 message-list">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action active">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order #1005 Delivery Question</h6>
                                        <small>1 hour ago</small>
                                    </div>
                                    <p class="mb-1">From: customer@example.com</p>
                                    <small><span class="badge bg-primary">Order Related</span> <span class="badge bg-danger">Urgent</span></small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Product Return Request</h6>
                                        <small>3 hours ago</small>
                                    </div>
                                    <p class="mb-1">From: another@customer.com</p>
                                    <small><span class="badge bg-primary">Order Related</span></small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Inventory Update</h6>
                                        <small>Yesterday</small>
                                    </div>
                                    <p class="mb-1">From: manager@petshop.com</p>
                                    <small><span class="badge bg-secondary">Internal</span></small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Team Meeting Reminder</h6>
                                        <small>2 days ago</small>
                                    </div>
                                    <p class="mb-1">From: hr@petshop.com</p>
                                    <small><span class="badge bg-secondary">Internal</span></small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order #1002 Payment Issue</h6>
                                        <small>3 days ago</small>
                                    </div>
                                    <p class="mb-1">From: payment@customer.com</p>
                                    <small><span class="badge bg-primary">Order Related</span></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message Content -->
                <div class="col-lg-8 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-envelope-open me-2"></i>Order #1005 Delivery Question
                            </h6>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-4">
                                <div>
                                    <h5>From: customer@example.com</h5>
                                    <p class="text-muted">To: orders@petshop.com</p>
                                </div>
                                <div class="text-end">
                                    <p class="text-muted">Date: 2025-03-05 14:30</p>
                                    <span class="badge bg-primary me-1">Order Related</span>
                                    <span class="badge bg-danger">Urgent</span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Subject: Order #1005 Delivery Question</h6>
                                <p class="text-muted">Regarding order #1005 placed on 2025-03-01</p>
                            </div>
                            
                            <div class="message-content mb-4">
                                <p>Hello PetShop Team,</p>
                                <p>I placed an order (#1005) on March 1st for 3 items (Premium Dog Food, Chew Toy, and Pet Grooming Kit). The order status shows "Shipped" but I haven't received any delivery confirmation or tracking information yet.</p>
                                <p>Could you please provide an update on:</p>
                                <ul>
                                    <li>Current delivery status</li>
                                    <li>Estimated delivery date</li>
                                    <li>Tracking number if available</li>
                                </ul>
                                <p>I need these items by Friday for my dog's birthday, so this is quite urgent.</p>
                                <p>Thank you for your prompt assistance,</p>
                                <p>Michael Brown<br>123 Pet Lover Lane<br>Dogtown, DT 12345</p>
                            </div>
                            
                            <div class="attachments mb-4">
                                <h6><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                                <div class="d-flex mt-2">
                                    <div class="border p-2 me-2">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        <small>order_1005_confirmation.pdf</small>
                                    </div>
                                    <div class="border p-2">
                                        <i class="fas fa-file-image text-primary me-2"></i>
                                        <small>dog_birthday_invite.jpg</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reply-section">
                                <h6 class="mb-3">Quick Reply</h6>
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" placeholder="Type your reply here..." id="replyTextarea" style="height: 150px"></textarea>
                                    <label for="replyTextarea">Type your reply here...</label>
                                </div>
                                <div class="attachment-box mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-paperclip me-2"></i>
                                            <small class="text-muted">No attachments</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-plus me-1"></i> Add Attachment
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="markAsResolved">
                                        <label class="form-check-label" for="markAsResolved">
                                            Mark as resolved
                                        </label>
                                    </div>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Send Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal fade" id="newMessageModal" tabindex="-1" aria-labelledby="newMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newMessageModalLabel">
                    <i class="fas fa-envelope me-2"></i>Compose New Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="recipientSelect" class="form-label">Recipient</label>
                        <select class="form-select" id="recipientSelect">
                            <option selected>Select recipient</option>
                            <option>Customer Support Team</option>
                            <option>Order Processing Team</option>
                            <option>Inventory Management</option>
                            <option>Management</option>
                            <option>+ Add external recipient</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" placeholder="Enter message subject">
                    </div>
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message</label>
                        <textarea class="form-control" id="messageContent" rows="6" placeholder="Type your message here..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="priority" id="priorityNormal" checked>
                            <label class="form-check-label" for="priorityNormal">
                                Normal
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="priority" id="priorityHigh">
                            <label class="form-check-label" for="priorityHigh">
                                High
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="priority" id="priorityUrgent">
                            <label class="form-check-label" for="priorityUrgent">
                                Urgent
                            </label>
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
                <button type="button" class="btn btn-primary">Send Message</button>
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
    
    // Message list item click handler
    const messageItems = document.querySelectorAll('.list-group-item-action');
    messageItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn')) {
                // Remove active class from all items
                messageItems.forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
                // Here you would typically load the message content via AJAX
            }
        });
    });
    
    // Simulate unread count update when viewing a message
    const activeMessage = document.querySelector('.list-group-item-action.active');
    if (activeMessage) {
        const unreadBadge = document.querySelector('.nav-link.active .badge');
        if (unreadBadge) {
            const currentCount = parseInt(unreadBadge.textContent);
            if (currentCount > 0) {
                unreadBadge.textContent = currentCount - 1;
            }
        }
    }
    
    // Mark as resolved checkbox handler
    const resolveCheckbox = document.getElementById('markAsResolved');
    if (resolveCheckbox) {
        resolveCheckbox.addEventListener('change', function() {
            if (this.checked) {
                const activeMessageItem = document.querySelector('.list-group-item-action.active');
                if (activeMessageItem) {
                    activeMessageItem.classList.remove('active');
                    // Here you would typically move the message to "resolved" or mark it as completed
                }
            }
        });
    }
});
</script>

</body>
</html>