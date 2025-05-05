<?php
session_start();

// Redirect if not staff
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "petshop";

$conn = new mysqli($host, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch staff details for sidebar
$staff_id = $_SESSION['staff_id'];
$staff_query = "SELECT Staff_name, position FROM staff WHERE Staff_ID = ?";
$stmt = $conn->prepare($staff_query);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff_result = $stmt->get_result();
$staff = $staff_result->fetch_assoc();

// Get logs from database
$dateFilter = "";
if (isset($_GET['logDate']) && !empty($_GET['logDate'])) {
    $dateFilter = " WHERE DATE(timestamp) = '" . $conn->real_escape_string($_GET['logDate']) . "'";
}

$result = $conn->query("SELECT username, email, status, timestamp 
                       FROM customer_login_logs 
                       $dateFilter
                       ORDER BY timestamp DESC");

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login Logs | PetShop Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        #sidebar {
            background-color: #343a40;
            min-height: 100vh;
            transition: transform 0.3s ease;
        }
        @media (max-width: 992px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                transform: translateX(-100%);
            }
            #sidebar.show {
                transform: translateX(0);
            }
        }
        .status-login { color: #28a745; }
        .status-logout { color: #dc3545; }
        .status-failed { color: #ffc107; }
        .table-responsive { 
            max-height: 600px; 
            overflow-y: auto; 
        }
        .scroll-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .scroll-buttons button {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-paw me-2"></i>PetShop Staff
        </a>
    </div>
    <div>
        <span class="text-light me-3">
            <i class="fas fa-user-circle me-1"></i>
            Welcome, <?php echo htmlspecialchars($_SESSION['staff_name']); ?>
        </span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-lg-2 d-lg-block bg-dark sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <?php
            // Path to the staff avatar image
            $avatar_path = "staff_avatars/" . $_SESSION['staff_id'] . ".jpg";
            
            // Check if the avatar exists, if so, display it
            if (file_exists($avatar_path)) {
                echo '<img src="' . $avatar_path . '" class="rounded-circle mb-2" alt="Staff Avatar" style="width: 80px; height: 80px; object-fit: cover;">';
            }
            ?>
            <h5 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['staff_name']); ?></h5>
            <small class="text-muted"><?php echo htmlspecialchars($_SESSION['position']); ?></small>
        </div>

        <!-- Sidebar Menu -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-light" href="staff_homepage.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-light" data-bs-toggle="collapse" href="#customerMenu">
                    <i class="fas fa-user-friends me-2"></i>Customer Management
                </a>
                <div class="collapse show" id="customerMenu">
                    <ul class="nav flex-column ps-4">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="staff_customer_list.php">
                                <i class="fas fa-list me-2"></i>Customer List
                            </a>
                        </li>
                        <li class="nav-item">
                                    <a class="nav-link text-light active" href="staff_customer_logs.php">
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
                            <a class="nav-link text-light" href="staff_orders.php">
                                <i class="fas fa-list me-2"></i>Current Orders
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                        <a class="nav-link text-light" href="staff_reports.php">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>

            <li class="nav-item">
                <a class="nav-link text-light" href="staff_inventory.php">
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
                    <i class="fas fa-user-clock me-2"></i>Customer Login Activity
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Date Filter Form -->
            <form method="GET" action="" class="row mb-3">
                <div class="col-md-4">
                    <input type="date" name="logDate" class="form-control" 
                           value="<?php echo isset($_GET['logDate']) ? htmlspecialchars($_GET['logDate']) : date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <?php if(isset($_GET['logDate'])): ?>
                        <a href="staff_customer_logs.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php if ($row['status'] === 'login'): ?>
                                                    <span class="status-login"><i class="fas fa-sign-in-alt"></i> Login</span>
                                                <?php elseif ($row['status'] === 'logout'): ?>
                                                    <span class="status-logout"><i class="fas fa-sign-out-alt"></i> Logout</span>
                                                <?php else: ?>
                                                    <span class="status-failed"><i class="fas fa-exclamation-triangle"></i> Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No login records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Scroll to Top/Bottom Buttons -->
<div class="scroll-buttons">
    <button id="scrollToTopBtn" class="btn btn-dark rounded-circle shadow">
        <i class="fas fa-arrow-up"></i>
    </button>
    <button id="scrollToBottomBtn" class="btn btn-dark rounded-circle shadow">
        <i class="fas fa-arrow-down"></i>
    </button>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Scroll functionality
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    const scrollToBottomBtn = document.getElementById('scrollToBottomBtn');
    
    if (scrollToTopBtn && scrollToBottomBtn) {
        // Scroll to top
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Scroll to bottom
        scrollToBottomBtn.addEventListener('click', () => {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        });
        
        // Show/hide based on scroll position
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.display = 'flex';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
            
            if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight - 100) {
                scrollToBottomBtn.style.display = 'none';
            } else {
                scrollToBottomBtn.style.display = 'flex';
            }
        });
        
        // Initialize
        scrollToTopBtn.style.display = 'none';
    }
});
</script>
</body>
</html>