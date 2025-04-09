<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

session_start();
include 'db_connection.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'zheya1810@gmail.com';
            $mail->Password = 'rbzs duxv qmho ywlv'; // Use App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
        
            $mail->setFrom('zheya1810@gmail.com', 'Petshop OTP System');
            $mail->addAddress($email); 
            $mail->addReplyTo('zheya1810@gmail.com', 'Petshop Support'); 
            
        
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Hi! Your OTP is <b>$otp</b>. Please use this code to verify your account. Thanks!";
            
        
            // Set to 0 for production
            $mail->SMTPDebug = 2; 
            $mail->Debugoutput = 'html';
          
            $mail->send();
            header("Location: verify_code.php");
            exit();
        } catch (Exception $e) {
            $error = "Email could not be sent. Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Email address not found in our system.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6d4c41;
            --secondary-color: #ffab91;
            --accent-color: #5d4037;
        }
        
        body {
            background-color: #f9f5f0;
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M30,15 Q50,5 70,15 Q80,30 70,45 Q50,55 30,45 Q20,30 30,15 Z" fill="%23ffab91" opacity="0.1"/></svg>');
            background-size: 150px;
        }
        
        .pet-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 2px solid var(--secondary-color);
            position: relative;
            overflow: hidden;
        }
        
        .pet-header {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        
        .pet-header i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .pet-header h2 {
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 171, 145, 0.25);
        }
        
        .btn-pet {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-pet:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .pet-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--primary-color);
        }
        
        .pet-footer a {
            color: var(--secondary-color);
            font-weight: bold;
            text-decoration: none;
        }
        
        .pet-footer a:hover {
            text-decoration: underline;
        }
        
        .pet-paw-print {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
        }
        
        .paw-1 {
            top: 20px;
            right: 20px;
            transform: rotate(30deg);
            font-size: 40px;
        }
        
        .paw-2 {
            bottom: 20px;
            left: 20px;
            transform: rotate(-20deg);
            font-size: 30px;
        }
        
        .input-group-text {
            background-color: #ffab91;
            color: white;
            border: none;
        }
        
        .alert-danger {
            background-color: #ffebee;
            border-color: #ef9a9a;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="pet-container">
        <i class="fas fa-paw pet-paw-print paw-1"></i>
        <i class="fas fa-paw pet-paw-print paw-2"></i>
        
        <div class="pet-header">
            <i class="fas fa-question-circle"></i>
            <h2>Forgot Your Password?</h2>
            <p>Enter your email to receive a verification code</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Your Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="meow@example.com" required>
                </div>
                <small class="text-muted">We'll send a verification code to this email</small>
            </div>

            <button type="submit" class="btn btn-pet btn-primary w-100">
                <i class="fas fa-paper-plane me-2"></i> Send Verification Code
            </button>
        </form>

        <div class="pet-footer">
            Remembered your password? <a href="login.php">Login here</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>