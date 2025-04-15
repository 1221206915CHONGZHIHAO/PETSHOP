<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - PetShop</title>
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
        <!-- Sidebar - Full Navigation Restored -->
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
                        <a class="nav-link text-light active" href="tasks.php">
                            <i class="fas fa-tasks me-2"></i>My Tasks
                            <span class="badge bg-primary float-end" id="taskCount">0</span>
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


<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tasks me-2"></i>My Tasks
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">All Tasks</a></li>
                            <li><a class="dropdown-item" href="#">Today</a></li>
                            <li><a class="dropdown-item" href="#">Overdue</a></li>
                            <li><a class="dropdown-item" href="#">Completed</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                        <i class="fas fa-plus me-1"></i> New Task
                    </button>
                </div>
            </div>

            <!-- Task Stats Cards - Will be populated by JS -->
            <div class="row mb-4" id="taskStatsContainer">
                <!-- Stats will be inserted here -->
            </div>

            <!-- Progress Bar - Will be populated by JS -->
            <div class="card mb-4">
                <div class="card-body" id="progressContainer">
                    <!-- Progress will be inserted here -->
                </div>
            </div>

            <div class="row">
                <!-- Task List -->
                <div class="col-lg-8 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-list-check me-2"></i>My Task List
                            </h6>
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control form-control-sm" placeholder="Search tasks..." id="taskSearch">
                                <button class="btn btn-sm btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="taskTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40px"></th>
                                            <th>Task</th>
                                            <th>Category</th>
                                            <th>Due Date</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="taskTableBody">
                                        <!-- Tasks will be inserted here -->
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No tasks found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <nav aria-label="Task pagination" id="taskPagination">
                                <!-- Pagination will be inserted here -->
                            </nav>
                        </div>
                    </div>
                </div>
                
                <!-- Task Details -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle me-2"></i>Task Details
                            </h6>
                            <div>
                                <button class="btn btn-sm btn-outline-primary me-2" id="editTaskBtn" disabled>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger" id="deleteTaskBtn" disabled>
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="taskDetailsContainer">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-tasks fa-3x mb-3"></i>
                                <p>Select a task to view details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- New Task Modal -->
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTaskModalLabel">
                    <i class="fas fa-plus me-2"></i>Create New Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" rows="3" placeholder="Enter task description"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="taskCategory" class="form-label">Category</label>
                            <select class="form-select" id="taskCategory" required>
                                <option value="" selected disabled>Select category</option>
                                <option>Order Processing</option>
                                <option>Customer Support</option>
                                <option>Inventory</option>
                                <option>Catalog</option>
                                <option>Reports</option>
                                <option>Returns</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taskDueDate" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="taskDueDate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Attachments</label>
                        <div class="border p-3 text-center">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-2">Drag files here or click to browse</p>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="uploadBtn">
                                <i class="fas fa-folder-open me-1"></i> Browse Files
                            </button>
                            <input type="file" id="fileInput" style="display: none;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="taskForm">Create Task</button>
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

    // File upload button
    document.getElementById('uploadBtn').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });

    // Form submission handler
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would handle the form submission to your backend
        alert('Task creation would be handled here - connect to your backend');
        // Close modal after submission
        bootstrap.Modal.getInstance(document.getElementById('newTaskModal')).hide();
    });

    // Initialize empty task management system
    // This would be replaced with your actual database connection logic
    function loadTasks() {
        // This is where you would fetch tasks from your database
        console.log("Loading tasks from database...");
        
        // For now, we'll leave it empty since you're connecting to a DB
        document.getElementById('taskTableBody').innerHTML = 
            '<tr><td colspan="6" class="text-center py-4 text-muted">No tasks found</td></tr>';
        
        // Update stats to show 0 tasks
        updateTaskStats(0, 0, 0, 0);
    }
    
    function updateTaskStats(total, completed, dueToday, overdue) {
        document.getElementById('taskStatsContainer').innerHTML = `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-white bg-primary stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">TOTAL TASKS</h6>
                                <h2 class="mb-0">${total}</h2>
                            </div>
                            <i class="fas fa-tasks fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-white bg-success stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">COMPLETED</h6>
                                <h2 class="mb-0">${completed}</h2>
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
                                <h6 class="card-title">DUE TODAY</h6>
                                <h2 class="mb-0">${dueToday}</h2>
                            </div>
                            <i class="fas fa-calendar-day fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card text-white bg-danger stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">OVERDUE</h6>
                                <h2 class="mb-0">${overdue}</h2>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const completionPercentage = total > 0 ? Math.round((completed / total) * 100) : 0;
        
        document.getElementById('progressContainer').innerHTML = `
            <div class="d-flex justify-content-between mb-2">
                <h6 class="mb-0">Task Completion Progress</h6>
                <span class="text-primary">${completionPercentage}%</span>
            </div>
            <div class="progress progress-thin mb-3">
                <div class="progress-bar bg-primary" role="progressbar" style="width: ${completionPercentage}%" 
                    aria-valuenow="${completionPercentage}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">${completed} of ${total} tasks completed</small>
                <small class="text-muted">${total - completed} remaining</small>
            </div>
        `;
    }
    
    // Initial load
    loadTasks();
});
</script>
</body>
</html>