<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'petshop');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch staff details
$staff_id = $_SESSION['staff_id'];
$query = "SELECT Staff_name, position, Staff_Email FROM staff WHERE Staff_ID = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if (!$staff) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Update session
$_SESSION['staff_name'] = $staff['Staff_name'];
$_SESSION['position'] = $staff['position'];
$_SESSION['staff_email'] = $staff['Staff_Email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - PetShop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        #sidebar {
            background-color: #343a40;
            min-height: 100vh;
            transition: transform 0.3s ease;
        }
        #sidebar.collapsed {
            transform: translateX(-100%);
        }
        .main-content {
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 992px) {
            #sidebar {
                position: fixed;
                z-index: 1050;
                width: 250px;
            }
            .main-content.expanded {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<!-- Navbar -->
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
                    <i class="fas fa-user-circle me-1"></i>
                    Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>
                </span>
            </li>
            <a href="logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </ul>
    </div>
</nav>

<!-- Page layout -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block sidebar bg-dark">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <img src="staff_avatars/<?php echo htmlspecialchars($_SESSION['staff_id']); ?>.jpg" 
                         class="rounded-circle mb-2" 
                         alt="Staff Avatar" 
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.src='default_avatar.jpg'">
                    <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
                </div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="staff_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse show" id="orderMenu">
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

                    <!-- Add more menu items if needed -->
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-lg-10 ms-sm-auto p-4 main-content">
            <h2>Welcome to your dashboard</h2>
            <p>This is your staff homepage.</p>
            <!-- Add dashboard content here -->
        </main>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });

    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 &&
            !sidebar.contains(e.target) &&
            !sidebarToggle.contains(e.target)) {
            sidebar.classList.add('collapsed');
            mainContent.classList.remove('expanded');
        }
    });
});
</script>
</body>
</html>
