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

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'userhomepage.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role']; 
    $login_input = trim($_POST['login_input']); 
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'userhomepage.php';

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
            $redirect_url = "admin_homepage.php";
        }
        else {
            if ($role === "staff") {
                $sql = "SELECT Staff_id, Staff_Username, Staff_Email, Staff_Password, status, password_reset_token 
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
                            $stmt->bind_result($db_staff_id, $db_username, $db_email, $db_password, $db_status, $db_reset_token);
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
                                $conn->query("UPDATE Staff SET password_reset_token = NULL WHERE Staff_id = $db_staff_id");
                                $redirect_url = "staff_homepage.php";
                            } elseif ($role === "customer") {
                                $_SESSION['customer_id'] = $db_customer_id;
                                $_SESSION['customer_name'] = $db_username;
                                $_SESSION['email'] = $db_email;
                                $redirect_url = "userhomepage.php";
                                
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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="Login.css">
    <style>
        #togglePassword {
            position: absolute;
            top: 70%; 
            right: 10px;
            transform: translateY(-50%); 
            border: none;
            background: none;
            opacity: 1; 
            padding: 0; 
            color: #6c757d; 
        }
        #togglePassword:hover i {
            color: black; 
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <h2 class="text-center mb-4">Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Login as:</label>
                <select name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="customer">Customer</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Username or Email:</label>
                <input type="text" name="login_input" class="form-control" required>
            </div>

            <div class="mb-3 position-relative">
                <label class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

             <!-- Add hidden input for redirect parameter -->
             <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            Don't have an account? <a href="register.php">Register here</a><br>
            <a href="forgot_password.php">Forgot Password?</a>
        </p>
    </div>
</div>

<script>
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

    // Check if password reset is required
if ($role === "staff" && !empty($db_reset_token)) {
    if (password_verify($password, $db_password)) {
        $_SESSION['reset_token'] = $db_reset_token;
        $_SESSION['staff_id'] = $db_staff_id;
        $redirect_url = "force_password_reset.php";
        $success_message = "Please set a new password";
    } else {
        $error_message = "Invalid temporary password";
    }
}
});

<?php if (!empty($redirect_url)): ?>
setTimeout(function () {
    window.location.href = "<?php echo $redirect_url; ?>";
}, 2000); // Redirect after 2 seconds
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>