<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
include 'db_connection.php';

$error = '';
$success = '';

// Function to send OTP email
function sendOTP($email, $otp) {
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
        
        $mail->SMTPDebug = 0; 
        $mail->Debugoutput = 'html';
      
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp'])) {
    $user_otp = $_POST['otp'];
    $session_otp = $_SESSION['otp'] ?? null;

    if ($user_otp == $session_otp) {
        $_SESSION['verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid OTP code. Please try again.";
    }
}

// Handle resend OTP
if (isset($_POST['resend_otp'])) {
    $email = $_SESSION['email'] ?? '';
    
    if (empty($email)) {
        $error = "Email not found. Please go back to the forgot password page.";
    } else {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT * FROM customer WHERE Customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Generate new OTP
            $new_otp = rand(100000, 999999);
            $_SESSION['otp'] = $new_otp;
            
            // Send new OTP
            if (sendOTP($email, $new_otp)) {
                $success = "A new verification code has been sent to your email.";
            } else {
                $error = "Could not send the verification code. Please try again.";
            }
        } else {
            $error = "Email not found in our records.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Pet Shop</title>
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
        
        .verify-container {
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
        
        .verify-container::before {
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
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            background-color: transparent;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
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
        
        .alert-success {
            background-color: var(--primary);
            border-color: var(--primary-dark);
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
        
        .verify-banner {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .verify-icon {
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
        
        .otp-instruction {
            background-color: rgba(78, 159, 61, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: flex-start;
        }
        
        .otp-instruction i {
            color: var(--primary);
            font-size: 1.2rem;
            margin-right: 10px;
            margin-top: 2px;
        }
        
        .code-input {
            letter-spacing: 12px;
            font-size: 1.6rem;
            text-align: center;
            font-weight: 600;
            padding-left: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .resend-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .countdown {
            display: inline-block;
            margin-left: 10px;
            font-weight: 600;
            color: var(--accent);
        }
        
        /* Animation for OTP input */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(78, 159, 61, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(78, 159, 61, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(78, 159, 61, 0);
            }
        }
        
        .code-input:focus {
            animation: pulse 1.5s infinite;
        }
        
        @media (max-width: 576px) {
            .verify-container {
                padding: 30px 20px;
                margin: 20px;
            }
            .code-input {
                letter-spacing: 8px;
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="verify-container">
        <!-- Paw print decorations -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="verify-banner">
            <div class="verify-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <h2 class="section-title">Verify Email</h2>
            <p>Enter the verification code sent to your email</p>
        </div>
        
        <div class="otp-instruction">
            <i class="bi bi-info-circle"></i>
            <div>
                We've sent a 6-digit code to <strong><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'your email' ?></strong>. Check your inbox and spam folder.
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= $success ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="verifyForm">
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-123 me-2" style="color: var(--primary);"></i>
                    Verification Code
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="text" name="otp" class="form-control code-input" placeholder="······" required maxlength="6" pattern="\d{6}" inputmode="numeric">
                </div>
                <small class="text-muted">
                    <i class="bi bi-cursor-text me-1"></i>
                    Enter the 6-digit code we sent to your email
                </small>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check2-circle me-2"></i> Verify & Continue
            </button>
            
            <div class="resend-container">
                <button type="button" id="resendBtn" class="btn btn-outline-primary" onclick="resendOTP()" disabled>
                    <i class="bi bi-send me-2"></i> Resend code
                </button>
                <span class="countdown" id="countdown">in 60s</span>
            </div>
        </form>
        
        <!-- Hidden form for resending OTP -->
        <form method="POST" id="resendForm" style="display: none;">
            <input type="hidden" name="resend_otp" value="1">
        </form>

        <div class="text-center mt-3">
            <a href="forgot_password.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to email entry
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto focus on OTP input when page loads
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('input[name="otp"]').focus();
        startCountdown();
    });
    
    // Auto submit when 6 digits are entered
    document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Auto submit when 6 digits are entered
        if (this.value.length === 6) {
            setTimeout(() => {
                document.getElementById('verifyForm').submit();
            }, 300); // Short delay for better UX
        }
    });
    
    // Function to resend OTP
    function resendOTP() {
        document.getElementById('resendBtn').disabled = true;
        document.getElementById('resendForm').submit();
        startCountdown();
    }
    
    // Countdown timer for resend button
    function startCountdown() {
        let seconds = 60;
        const countdownEl = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');
        
        resendBtn.disabled = true;
        
        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = `in ${seconds}s`;
            
            if (seconds <= 0) {
                clearInterval(interval);
                countdownEl.textContent = 'now';
                resendBtn.disabled = false;
            }
        }, 1000);
    }
</script>
</body>
</html>