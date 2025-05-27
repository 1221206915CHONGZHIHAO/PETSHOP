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

// Turn off the NO_AUTO_VALUE_ON_ZERO mode to avoid the error
$conn->query("SET SESSION sql_mode = ''");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$error_message = "";
$success_message = "";
$redirect = false;
$show_otp_form = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If OTP verification form is submitted
    if (isset($_POST['verify_otp'])) {
        $user_otp = $_POST['otp'];
        
        if ($user_otp == $_SESSION['otp']) {
            // OTP verified, proceed with registration
            $username = $_SESSION['reg_username'];
            $email = $_SESSION['reg_email'];
            $password = $_SESSION['reg_password'];
            
            // Check if username/email still available (in case someone else registered while they were verifying)
            $check_username = "SELECT * FROM Customer WHERE Customer_name = ?";
            $stmt_username = $conn->prepare($check_username);
            $stmt_username->bind_param("s", $username);
            $stmt_username->execute();
            $result_username = $stmt_username->get_result();
            
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
                    $success_message = "Registration successful! Redirecting to login page...";
                    $redirect = true;
                    
                    // Clear session variables
                    unset($_SESSION['otp']);
                    unset($_SESSION['reg_username']);
                    unset($_SESSION['reg_email']);
                    unset($_SESSION['reg_password']);
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                $stmt->close();
            }
            
            $stmt_username->close();
            $stmt_email->close();
        } else {
            $error_message = "Invalid OTP. Please try again.";
            $show_otp_form = true;
        }
    } 
    // If registration form is submitted
    else {
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
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error_message = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error_message = "Password must contain at least one number.";
        } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $error_message = "Password must contain at least one special character.";
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
                // Generate OTP
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['reg_username'] = $username;
                $_SESSION['reg_email'] = $email;
                $_SESSION['reg_password'] = $password;
                
                // Send OTP via email with improved settings
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'zheya1810@gmail.com'; // Your Gmail address
                    $mail->Password = 'rbzs duxv qmho ywlv'; // Use App Password (2FA must be enabled)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    
                    // Important settings to improve deliverability
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    
                    // From address must match your Gmail address
                    $mail->setFrom('yourgmail@gmail.com', 'Pet Shop Registration');
                    $mail->addAddress($email);
                    $mail->addReplyTo('support@petshop.com', 'Pet Shop Support');
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Pet Shop Registration OTP Code';
                    
                    // Improved email body with plain text alternative
                    $mail->Body = '
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #4e9f3d; color: white; padding: 15px; text-align: center; }
                                .content { padding: 20px; background-color: #f9f9f9; }
                                .otp-code { font-size: 24px; font-weight: bold; color: #4e9f3d; text-align: center; margin: 20px 0; }
                                .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="header">
                                    <h2>Pet Shop Registration</h2>
                                </div>
                                <div class="content">
                                    <p>Hello ' . htmlspecialchars($username) . ',</p>
                                    <p>Thank you for registering with Pet Shop. Please use the following OTP code to verify your email address:</p>
                                    <div class="otp-code">' . $otp . '</div>
                                    <p>This code will expire in 10 minutes.</p>
                                    <p>If you didn\'t request this, please ignore this email.</p>
                                </div>
                                <div class="footer">
                                    <p>&copy; ' . date('Y') . ' Pet Shop. All rights reserved.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ';
                    
                    $mail->AltBody = "Hello $username,\n\nThank you for registering with Pet Shop.\n\nYour OTP code is: $otp\n\nThis code will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.";
                    
                    // Important headers to prevent spam classification
                    $mail->addCustomHeader('MIME-Version: 1.0');
                    $mail->addCustomHeader('X-Mailer: PHP/' . phpversion());
                    $mail->addCustomHeader('X-Priority: 1 (Highest)');
                    $mail->addCustomHeader('X-MSMail-Priority: High');
                    $mail->addCustomHeader('Importance: High');
                    
                    // Disable debugging for production
                    $mail->SMTPDebug = 0;
                    
                    $mail->send();
                    
                    $success_message = "OTP has been sent to your email. Please check your inbox (and spam folder if you don't see it).";
                    $show_otp_form = true;
                } catch (Exception $e) {
                    $error_message = "Email could not be sent. Error: " . $mail->ErrorInfo;
                }
            }
            
            $stmt_username->close();
            $stmt_email->close();
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
    <title>Pet Shop Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Register.css">
    <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="3;url=login.php">
    <?php endif; ?>
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
            justify-content: center;
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

        .register-container {
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

        .register-container::before {
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
            margin-bottom: 1.5rem;
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

        .form-control, .form-select {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
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

        .register-banner {
            text-align: center;
            margin-bottom: 25px;
        }

        .register-banner img {
            max-height: 100px;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .password-requirements {
            margin-top: 8px;
            font-size: 13px;
            color: var(--gray);
            background-color: rgba(240, 242, 245, 0.8);
            padding: 10px 15px;
            border-radius: 8px;
        }

        .requirement {
            margin-bottom: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .requirement i {
            margin-right: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .requirement.text-success {
            color: var(--primary) !important;
        }

        /* Animation for validation icons */
        .requirement i.bi-check-circle {
            animation: fadeInScale 0.3s ease;
        }

        @keyframes fadeInScale {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* OTP form styles */
        .otp-form {
            display: <?php echo $show_otp_form ? 'block' : 'none'; ?>;
        }
        
        .otp-input {
            letter-spacing: 2px;
            font-size: 1.5rem;
            text-align: center;
        }
        
        .otp-message {
            margin-bottom: 20px;
            font-size: 15px;
            color: var(--gray);
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
        /* Additional styles for OTP form */
        .otp-instructions {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary);
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .otp-instructions i {
            color: var(--primary);
            margin-right: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="register-container">
        <!-- Paw print decorations -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="register-banner">
            <h2 class="section-title">Join Our Family</h2>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" id="registrationForm" <?php if ($show_otp_form) echo 'style="display: none;"'; ?>>
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-person me-2" style="color: var(--primary);"></i>
                    Your Name
                </label>
                <input type="text" name="username" class="form-control" placeholder="Enter your name" required value="<?php echo isset($_SESSION['reg_username']) ? htmlspecialchars($_SESSION['reg_username']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-envelope me-2" style="color: var(--primary);"></i>
                    Email Address
                </label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="<?php echo isset($_SESSION['reg_email']) ? htmlspecialchars($_SESSION['reg_email']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-lock me-2" style="color: var(--primary);"></i>
                    Password
                </label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                <div class="password-requirements mt-2">
                    <div class="requirement" id="length-check">
                        <i class="bi bi-x-circle text-danger"></i>
                        <i class="bi bi-check-circle text-success d-none"></i>
                        <span>At least 8 characters</span>
                    </div>
                    <div class="requirement" id="uppercase-check">
                        <i class="bi bi-x-circle text-danger"></i>
                        <i class="bi bi-check-circle text-success d-none"></i>
                        <span>At least 1 uppercase letter</span>
                    </div>
                    <div class="requirement" id="number-check">
                        <i class="bi bi-x-circle text-danger"></i>
                        <i class="bi bi-check-circle text-success d-none"></i>
                        <span>At least 1 number</span>
                    </div>
                    <div class="requirement" id="symbol-check">
                        <i class="bi bi-x-circle text-danger"></i>
                        <i class="bi bi-check-circle text-success d-none"></i>
                        <span>At least 1 special character</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-shield-lock me-2" style="color: var(--primary);"></i>
                    Confirm Password
                </label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-plus me-2"></i>
                Create Account
            </button>
        </form>

        <!-- OTP Verification Form -->
        <form method="POST" action="" id="otpForm" class="otp-form">
            <div class="otp-message">
                <p>We've sent a 6-digit verification code to your email address.</p>
                
                <div class="otp-instructions">
                    <p><i class="bi bi-info-circle"></i> <strong>Can't find the email?</strong></p>
                    <ul>
                        <li>Check your spam or junk folder</li>
                        <li>Wait a few minutes - emails may take time to arrive</li>
                        <li>Make sure you entered the correct email address</li>
                    </ul>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-shield-lock me-2" style="color: var(--primary);"></i>
                    Verification Code
                </label>
                <input type="text" name="otp" class="form-control otp-input" placeholder="Enter 6-digit code" required maxlength="6" pattern="\d{6}" title="Please enter a 6-digit number">
            </div>

            <button type="submit" name="verify_otp" class="btn btn-primary w-100">
                <i class="bi bi-check-circle me-2"></i>
                Verify & Complete Registration
            </button>
            
            <p class="text-center mt-3">
                Didn't receive code? <a href="#" onclick="resendOTP(); return false;">Resend OTP</a>
            </p>
        </form>

        <p class="text-center mt-4">
            Already have an account? <a href="login.php" class="d-inline-block">Login here</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const lengthCheck = document.getElementById('length-check');
    const uppercaseCheck = document.getElementById('uppercase-check');
    const numberCheck = document.getElementById('number-check');
    const symbolCheck = document.getElementById('symbol-check');
    
    // Validation functions
    function checkPasswordLength(password) {
        return password.length >= 8;
    }
    
    function checkPasswordUppercase(password) {
        return /[A-Z]/.test(password);
    }
    
    function checkPasswordNumber(password) {
        return /[0-9]/.test(password);
    }
    
    function checkPasswordSymbol(password) {
        return /[^A-Za-z0-9]/.test(password);
    }
    
    // Toggle icon visibility
    function toggleIconVisibility(element, isValid) {
        const crossIcon = element.querySelector('.bi-x-circle');
        const checkIcon = element.querySelector('.bi-check-circle');
        
        if (isValid) {
            crossIcon.classList.add('d-none');
            checkIcon.classList.remove('d-none');
            element.classList.add('text-success');
            element.classList.remove('text-danger');
        } else {
            crossIcon.classList.remove('d-none');
            checkIcon.classList.add('d-none');
            element.classList.add('text-danger');
            element.classList.remove('text-success');
        }
    }
    
    // Validate password on input
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        
        // Check length requirement
        toggleIconVisibility(lengthCheck, checkPasswordLength(password));
        
        // Check uppercase requirement
        toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
        
        // Check number requirement
        toggleIconVisibility(numberCheck, checkPasswordNumber(password));
        
        // Check symbol requirement
        toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
    });

    // Also validate when the page loads in case form was submitted with errors
    if (passwordInput.value) {
        const password = passwordInput.value;
        toggleIconVisibility(lengthCheck, checkPasswordLength(password));
        toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
        toggleIconVisibility(numberCheck, checkPasswordNumber(password));
        toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
    }

    <?php if ($redirect): ?>
    setTimeout(function () {
        window.location.href = "login.php";
    }, 3000); // Redirect after 3 seconds
    <?php endif; ?>
});

function resendOTP() {
    // Show loading state
    const resendLink = document.querySelector('a[onclick="resendOTP()"]');
    resendLink.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Sending...';
    resendLink.style.pointerEvents = 'none';
    
    // Use AJAX to resend OTP
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'resend_otp=1&email=<?php echo isset($_SESSION["reg_email"]) ? urlencode($_SESSION["reg_email"]) : ""; ?>'
    })
    .then(response => response.text())
    .then(data => {
        // Show success message
        alert('OTP has been resent successfully!');
        resendLink.innerHTML = 'Resend OTP';
        resendLink.style.pointerEvents = 'auto';
    })
    .catch(error => {
        alert('Error resending OTP. Please try again.');
        resendLink.innerHTML = 'Resend OTP';
        resendLink.style.pointerEvents = 'auto';
    });
}
</script>
</body>
</html>