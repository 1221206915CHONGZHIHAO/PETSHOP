<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['staff_verified']) || !$_SESSION['staff_verified']) {
    header("Location: staff_forgot_password.php");
    exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Password must contain at least one number.";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error = "Password must contain at least one special character.";
    } else {
        $email = $_SESSION['staff_email'];
        $plain_password = $new_password;

        $stmt = $conn->prepare("UPDATE staff SET Staff_Password = ? WHERE Staff_Email = ?");
        $stmt->bind_param("ss", $plain_password, $email);

        if ($stmt->execute()) {
            $success = "Password has been reset successfully!";
            session_unset();
            session_destroy();
        } else {
            $error = "Error resetting password. Please try again. Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Pet Shop Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e9f3d;
            --primary-light: #8fd14f;
            --primary-dark: #38761d;
            --secondary: #1e3a8a;
            --accent: #ff7e2e;
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
        
        .reset-container {
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
        
        .reset-container::before {
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
        
        .alert-success {
            background-color: var(--primary);
            border-color: var(--primary-dark);
            color: white;
        }
        
        .alert-link {
            color: white;
            font-weight: 700;
            text-decoration: underline;
        }
        
        .alert-link:hover {
            color: var(--light);
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
        
        .reset-banner {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .reset-icon {
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
        
        .toggle-password {
            background-color: var(--light-gray);
            border-color: #e1e1e1;
            color: var(--gray);
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        
        .toggle-password:hover {
            background-color: #e9ecef;
            color: var(--primary);
        }
        
        .password-strength {
            height: 6px;
            background-color: #eee;
            margin-top: 10px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
        
        .text-muted {
            color: var(--gray) !important;
            font-size: 13px;
            margin-top: 6px;
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
        
        @media (max-width: 576px) {
            .reset-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="reset-container">
        <!-- Paw print decorations -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="reset-banner">
            <div class="reset-icon">
                <i class="bi bi-key"></i>
            </div>
            <h2 class="section-title">Reset Password</h2>
            <p>Create a new secure password for your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= $success ?> You can now <a href="admin_login.php" class="alert-link">login</a>.
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock me-2" style="color: var(--primary);"></i>
                        New Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Create a password" required>
                        <button class="btn toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength mt-2">
                        <div class="strength-meter" id="strength-meter"></div>
                    </div>
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
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                        <button class="btn toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="password-match" class="text-muted mt-2"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check2-circle me-2"></i> Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
    
    // Password strength meter and requirements
    const passwordInput = document.getElementById('new_password');
    const strengthMeter = document.getElementById('strength-meter');
    const passwordMatch = document.getElementById('password-match');
    const confirmPassword = document.getElementById('confirm_password');
    
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
    
    // Calculate password strength
    function calculatePasswordStrength(password) {
        let score = 0;
        let percentage = 0;
        let color = '#ddd';
        
        if (password.length > 0) {
            // Start with 1 point for any entry
            score = 1;
            
            // Add points for length
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Add points for complexity
            if (/[A-Z]/.test(password)) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            // Calculate percentage (max score is 7)
            percentage = Math.min(100, Math.round((score / 7) * 100));
            
            // Determine color based on score
            if (score < 3) {
                color = '#ff3333'; // Red (weak)
            } else if (score < 5) {
                color = '#ffa500'; // Orange (moderate)
            } else if (score < 6) {
                color = '#4e9f3d'; // Green (strong)
            } else {
                color = '#38761d'; // Dark green (very strong)
            }
        }
        
        return { score, percentage, color };
    }
    
    // Validate password on input
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        
        // Update strength meter
        const strength = calculatePasswordStrength(password);
        strengthMeter.style.width = strength.percentage + '%';
        strengthMeter.style.backgroundColor = strength.color;
        
        // Check requirements
        toggleIconVisibility(lengthCheck, checkPasswordLength(password));
        toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
        toggleIconVisibility(numberCheck, checkPasswordNumber(password));
        toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
        
        // Update match status if confirm password has value
        if (confirmPassword.value) {
            updatePasswordMatch();
        }
    });
    
    // Check password match
    function updatePasswordMatch() {
        if (confirmPassword.value === '') {
            passwordMatch.innerHTML = '';
        } else if (confirmPassword.value !== passwordInput.value) {
            passwordMatch.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords don\'t match</span>';
        } else {
            passwordMatch.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</span>';
        }
    }
    
    confirmPassword.addEventListener('input', updatePasswordMatch);
    
    // Initialize validation on page load
    if (passwordInput.value) {
        const password = passwordInput.value;
        const strength = calculatePasswordStrength(password);
        strengthMeter.style.width = strength.percentage + '%';
        strengthMeter.style.backgroundColor = strength.color;
        
        toggleIconVisibility(lengthCheck, checkPasswordLength(password));
        toggleIconVisibility(uppercaseCheck, checkPasswordUppercase(password));
        toggleIconVisibility(numberCheck, checkPasswordNumber(password));
        toggleIconVisibility(symbolCheck, checkPasswordSymbol(password));
        
        if (confirmPassword.value) {
            updatePasswordMatch();
        }
    }
</script>
</body>
</html>