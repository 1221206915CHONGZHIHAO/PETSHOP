<?php
session_start();

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "petshop";

$conn = new mysqli($host, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";
$redirect_url = "";

// Get redirect URL from GET parameter or set default
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role']; 
    $login_input = trim($_POST['login_input']); 
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    if (empty($login_input) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        // Admin login
        if ($role === "admin" && $login_input === "ADMIN" && $password === "PETSHOP1") {
            $_SESSION['role'] = "admin";
            $_SESSION['admin_logged_in'] = true; 
            $_SESSION['username'] = "ADMIN";
            $_SESSION['email'] = "admin@petshop.com";
            $success_message = "Login successful! Redirecting...";
            
            // Set redirect URL for admin
            $redirect_url = !empty($redirect) ? $redirect : 'admin_homepage.php';
        }
        else {
            if ($role === "staff") {
                $sql = "SELECT Staff_id, Staff_Username, Staff_Email, Staff_Password, status, password_reset_token, img_URL 
                        FROM Staff 
                        WHERE (Staff_Username = ? OR Staff_Email = ?)";
            } elseif ($role === "customer") {
                $sql = "SELECT Customer_id, Customer_name, Customer_email, Customer_password 
                        FROM Customer 
                        WHERE (Customer_name = ? OR Customer_email = ?)";
            } else {
                $error_message = "Invalid role selected.";
            }

            if (empty($error_message)) {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $error_message = "Database error. Please try again later.";
                } else {
                    $stmt->bind_param("ss", $login_input, $login_input);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        if ($role === "staff") {
                            $stmt->bind_result($db_staff_id, $db_username, $db_email, $db_password, $db_status, $db_reset_token, $db_img_url);
                        } elseif ($role === "customer") {
                            $stmt->bind_result($db_customer_id, $db_username, $db_email, $db_password);
                        }
                        $stmt->fetch();

                        if ($role === "staff" && $db_status !== 'Active') {
                            $error_message = "Account is inactive. Please contact administrator.";
                        } 
                        elseif ($role === "staff" && !empty($db_reset_token)) {
                            if ($password === $db_password) {
                                $_SESSION['reset_token'] = $db_reset_token;
                                $_SESSION['staff_id'] = $db_staff_id;
                                $redirect_url = "force_password_reset.php";
                                $success_message = "Please set a new password.";
                            } else {
                                $error_message = "Invalid temporary password.";
                            }
                        }
                        elseif ($password === $db_password) {
                            $_SESSION['role'] = $role;
                            if ($role === "staff") {
                                $_SESSION['staff_id'] = $db_staff_id;
                                $_SESSION['username'] = $db_username;
                                $_SESSION['email'] = $db_email;
                                $_SESSION['avatar_path'] = $db_img_url; // Store avatar path in session
                                $conn->query("UPDATE Staff SET password_reset_token = NULL WHERE Staff_id = $db_staff_id");
                                
                                // Record successful staff login
                                $conn->query("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                                            VALUES ($db_staff_id, '$db_username', '$db_email', 'login')");
                                
                                $redirect_url = !empty($redirect) ? $redirect : 'staff_homepage.php';
                            } elseif ($role === "customer") {
                                $_SESSION['customer_id'] = $db_customer_id;
                                $_SESSION['customer_name'] = $db_username;
                                $_SESSION['email'] = $db_email;
                                $redirect_url = !empty($redirect) ? $redirect : 'userhomepage.php';
                                
                                // Record successful customer login
                                $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                            VALUES ('$db_username', '$db_email', 'login')");
                            }
                            $success_message = "Login successful! Redirecting...";
                        } else {
                            $error_message = "Invalid password.";
                            if ($role === "staff") {
                                $conn->query("UPDATE Staff SET 
                                    login_attempts = login_attempts + 1, 
                                    last_failed_login = NOW() 
                                    WHERE Staff_id = $db_staff_id");
                                
                                // Record failed staff login attempt
                                $conn->query("INSERT INTO staff_login_logs (staff_id, username, email, status, ip_address) 
                                            VALUES ($db_staff_id, '$db_username', '$db_email', 'failed', '{$_SERVER['REMOTE_ADDR']}')");
                            }
                            
                            // Record failed login attempt if customer
                            if ($role === "customer") {
                                $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                            VALUES ('', '$login_input', 'failed')");
                            }
                        }
                    } else {
                        $error_message = "Username or email not found.";
                        // Record failed login attempt if customer
                        if ($role === "customer") {
                            $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                        VALUES ('', '$login_input', 'failed')");
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Login.css">
</head>
<body>
<div class="container">
    <div class="login-container">
        <!-- Paw print decorations (replace with actual cat_paw.png when available) -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="login-banner">
            <!-- Replace with your actual logo -->
            <h2 class="section-title">Welcome</h2>
        </div>
        
        <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>
        <div class="alert alert-success" id="successAlert" style="display: none;"></div>
        
        <form method="POST" action="" id="loginForm">
            <!-- Role selector with icons -->
            <div class="role-selector mb-4">
                <label class="role-option" id="adminOption">
                    <input type="radio" name="role" value="admin" required>
                    <i class="bi bi-shield-lock"></i>
                    Admin
                </label>
                <label class="role-option" id="staffOption">
                    <input type="radio" name="role" value="staff" required>
                    <i class="bi bi-person-badge"></i>
                    Staff
                </label>
                <label class="role-option active" id="customerOption">
                    <input type="radio" name="role" value="customer" required checked>
                    <i class="bi bi-person-heart"></i>
                    Pet Owner
                </label>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-person me-2" style="color: var(--primary);"></i>
                    Username or Email
                </label>
                <input type="text" name="login_input" class="form-control" required placeholder="Enter your username or email">
            </div>

            <div class="mb-4 position-relative">
                <label class="form-label">
                    <i class="bi bi-lock me-2" style="color: var(--primary);"></i>
                    Password
                </label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                <button type="button" id="togglePassword" class="btn">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <input type="hidden" name="redirect" id="redirectInput" value="">

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Login
            </button>
        </form>

        <p class="text-center mt-4">
            New pet parent? <a href="register.php" class="d-inline-block">Register here</a><br>
            <a href="forgot_password.php" class="mt-2 d-inline-block">
                <i class="bi bi-question-circle me-1"></i>
                Forgot Password?
            </a>
        </p>
    </div>
</div>

<script>
// Parse URL to get redirect parameter
const urlParams = new URLSearchParams(window.location.search);
const redirect = urlParams.get('redirect') || '';
document.getElementById('redirectInput').value = redirect;

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye'); 
    }
});

// Role selector functionality
const roleOptions = document.querySelectorAll('.role-option');
roleOptions.forEach(option => {
    option.addEventListener('click', function() {
        roleOptions.forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});

// Display PHP messages via JavaScript
<?php if (!empty($error_message)): ?>
    document.getElementById('errorAlert').textContent = "<?php echo $error_message; ?>";
    document.getElementById('errorAlert').style.display = 'block';
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    document.getElementById('successAlert').textContent = "<?php echo $success_message; ?>";
    document.getElementById('successAlert').style.display = 'block';
<?php endif; ?>

<?php if (!empty($redirect_url)): ?>
setTimeout(function () {
    window.location.href = "<?php echo $redirect_url; ?>";
}, 2000); // Redirect after 2 seconds
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>