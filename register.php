<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        // Check if username already exists
        $check_username = "SELECT * FROM Customer WHERE Customer_name = ?";
        $stmt_username = $conn->prepare($check_username);
        $stmt_username->bind_param("s", $username);
        $stmt_username->execute();
        $result_username = $stmt_username->get_result();
        
        // Check if email already exists
        $check_email = "SELECT * FROM Customer WHERE Customer_email = ?";
        $stmt_email = $conn->prepare($check_email);
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();
        
        if ($result_username->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } elseif ($result_email->num_rows > 0) {
            $error_message = "Email already registered. Please use a different email address.";
        } else {
            // Username and email are unique, proceed with registration
            $sql = "INSERT INTO Customer (Customer_name, Customer_email, Customer_password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                $success_message = "Customer registration successful! Redirecting to login page...";
                $username = $email = $password = $confirm_password = "";
                $redirect = true;
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
        
        $stmt_username->close();
        $stmt_email->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Register.css">
    <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="3;url=login.php">
    <?php endif; ?>
</head>
<body>
<div class="register-container">
    <h2 class="text-center mb-4">Create New Account</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="username" class="form-control" placeholder="Enter your name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="name@domain.com" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>