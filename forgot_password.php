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
            $mail->SMTPDebug = 0; 
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
    <title>Forgot Password - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e9f3d; /* Fresh green */
            --primary-light: #8fd14f;
            --primary-dark: #38761d;
            --secondary: #1e3a8a; /* Deep navy blue */
            --accent: #ff7e2e; /* Warm orange */
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #f0f2f5;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
            background-color: var(--light-gray);
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url("Register_Page_Background.jpg") no-repeat center center;
            background-size: cover;
            z-index: -1;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        .forgot-container {
            background-color: rgba(255, 255, 255, 0.97);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 450px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .forgot-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            display: block;
            height: 4px;
            width: 70px;
            background-color: var(--primary);
            margin-top: 0.5rem;
        }
        
        .form-label {
            color: var(--dark);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.25rem rgba(78, 159, 61, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 159, 61, 0.3);
            border-radius: 8px;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 159, 61, 0.4);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .alert-danger {
            background-color: #ff3333;
            border-color: #e62e2e;
            color: white;
        }
        
        .text-center a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .text-center a:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
            display: inline-block;
        }
        
        .paw-print {
            position: absolute;
            width: 80px;
            height: 80px;
            background-image: url('cat_paw.png');
            background-size: contain;
            opacity: 0.05;
            z-index: 0;
        }
        
        .paw-top-right {
            top: 10px;
            right: 10px;
            transform: rotate(45deg);
        }
        
        .paw-bottom-left {
            bottom: 10px;
            left: 10px;
            transform: rotate(-45deg);
        }
        
        .forgot-banner {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .forgot-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .input-group-text {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px 0 0 8px;
        }
        
        .text-muted {
            color: var(--gray) !important;
            font-size: 13px;
            margin-top: 6px;
        }
        
        @media (max-width: 576px) {
            .forgot-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="forgot-container">
        <!-- Paw print decorations -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="forgot-banner">
            <div class="forgot-icon">
                <i class="bi bi-question-circle"></i>
            </div>
            <h2 class="section-title">Forgot Password?</h2>
            <p>Enter your email to receive a verification code</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-envelope me-2" style="color: var(--primary);"></i>
                    Your Email Address
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    We'll send a verification code to this email
                </small>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-send me-2"></i> Send Verification Code
            </button>
        </form>

        <p class="text-center mt-4">
            Remembered your password? <a href="login.php" class="d-inline-block">Login here</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>