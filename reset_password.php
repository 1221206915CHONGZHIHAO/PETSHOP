<?php
session_start();
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database Config
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "petshop";

$conn = new mysqli($host, $username_db, $password_db, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = "";
$success = "";

// Verify Token
if (isset($_GET['token']) && isset($_GET['role'])) {
    $token = $_GET['token'];
    $role = $_GET['role'];
    
    $table = ($role == "staff") ? "Staff" : "Customer";
    $sql = "SELECT ".$role."_ID FROM $table WHERE reset_token=? AND reset_token_expires > NOW()";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            $error = "Invalid or expired token!";
        }
        $stmt->close();
    } else {
        $error = "Database error!";
    }
} else {
    $error = "Invalid request!";
}

// Process Reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password != $confirm) {
        $error = "Passwords don't match!";
    } else {
        $table = ($role == "staff") ? "Staff" : "Customer";
        $password_column = ($role == "staff") ? "Staff_Password" : "Customer_Password";
        
        $sql = "UPDATE $table SET $password_column=?, reset_token=NULL, reset_token_expires=NULL WHERE reset_token=?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ss", $password, $token);
            if ($stmt->execute()) {
                $success = "Password updated successfully! <a href='admin_login.php'>Login now</a>";
            } else {
                $error = "Password update failed!";
            }
            $stmt->close();
        } else {
            $error = "Database error!";
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
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="text-center mb-4">Reset Password</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($token) && empty($success) && empty($error)): ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
        
        <div class="mb-3">
            <label class="form-label">New Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Confirm Password:</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>