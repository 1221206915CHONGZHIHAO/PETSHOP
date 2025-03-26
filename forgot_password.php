<?php
session_start();
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database Configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "petshop";

$conn = new mysqli($host, $username_db, $password_db, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    if (empty($email) || empty($role)) {
        $error = "All fields are required!";
    } else {
        // Check if email exists in the correct table
        if ($role === "staff") {
            $sql = "SELECT Staff_ID, Staff_Username, Staff_Email FROM Staff WHERE Staff_Email = ?";
        } elseif ($role === "customer") {
            $sql = "SELECT Customer_ID, Customer_Name, Customer_Email FROM Customer WHERE Customer_Email = ?";
        } else {
            $error = "Invalid role selected!";
        }

        if (empty($error)) {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    // Generate token
                    $token = bin2hex(random_bytes(32));
                    $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiry
                    
                    // Bind results (including email)
                    if ($role === "staff") {
                        $stmt->bind_result($user_id, $username, $user_email);
                    } else {
                        $stmt->bind_result($user_id, $username, $user_email);
                    }
                    $stmt->fetch();
                    
                    // Update database with token
                    $update_sql = ($role === "staff") 
                        ? "UPDATE Staff SET reset_token=?, reset_token_expires=? WHERE Staff_Email=?" 
                        : "UPDATE Customer SET reset_token=?, reset_token_expires=? WHERE Customer_Email=?";
                    
                    $update_stmt = $conn->prepare($update_sql);
                    if ($update_stmt) {
                        $update_stmt->bind_param("sss", $token, $expires, $email);
                        if ($update_stmt->execute()) {
                            // Send email to the user's registered email
                            try {
                                $mail = new PHPMailer(true);
                                
                                // SMTP Configuration (Gmail Example)
                                $mail->isSMTP();
                                $mail->Host = 'smtp.gmail.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'your@gmail.com'; // Your service email
                                $mail->Password = 'your-app-password'; // App password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port = 587;
                                
                                // Email Content
                                $mail->setFrom('no-reply@petshop.com', 'PetShop');
                                $mail->addAddress($user_email, $username); // Dynamic email from DB
                                $mail->isHTML(false);
                                $mail->Subject = 'Password Reset Request';
                                
                                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token&role=$role";
                                $mail->Body = "Hello $username,\n\n"
                                           . "Click to reset your password (valid for 1 hour):\n"
                                           . "$reset_link\n\n"
                                           . "If you didn't request this, please ignore this email.";
                                
                                $mail->send();
                                $success = "Password reset link sent to your registered email!";
                            } catch (Exception $e) {
                                $error = "Failed to send email: " . $e->getMessage();
                            }
                        } else {
                            $error = "Database update failed!";
                        }
                        $update_stmt->close();
                    }
                } else {
                    $error = "Email not found for selected role!";
                }
                $stmt->close();
            } else {
                $error = "Database error!";
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
    <title>Forgot Password</title>
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
    <h2 class="text-center mb-4">Forgot Password</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Select Role:</label>
            <select name="role" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="staff">Staff</option>
                <option value="customer">Customer</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Registered Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Send  Reset Link</button>
    </form>

    <p class="text-center mt-3">
        Remember password? <a href="admin_login.php">Login here</a>
    </p>
</div>
</body>
</html>