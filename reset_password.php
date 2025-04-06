<?php
session_start();
if (!isset($_SESSION['reset_user_id'])) {
    die("Please verify your email first");
}

$conn = new mysqli("localhost", "root", "", "petshop");
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if passwords match
    if ($password != $confirm_password) {
        $error = "The password and confirmation password do not match!";
    } else {
        $user_id = $_SESSION['reset_user_id'];
        
        // Plain text password storage (as per your request)
        $stmt = $conn->prepare("UPDATE Customer SET Customer_Password = ? WHERE Customer_ID = ?");
        $stmt->bind_param("si", $password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Password reset successfully! <a href='admin_login.php' class='alert-link'>Login now</a>";
            session_destroy();
        } else {
            $error = "Password update failed";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PetShop</title>
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
        }
        
        .btn-pet:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .password-strength {
            height: 5px;
            background-color: #eee;
            margin-top: 5px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s;
        }
        
        .alert-link {
            color: #5d4037;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .alert-link:hover {
            color: #3e2723;
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
    </style>
</head>
<body>
<div class="container">
    <div class="pet-container">
        <i class="fas fa-paw pet-paw-print paw-1"></i>
        <i class="fas fa-paw pet-paw-print paw-2"></i>
        
        <div class="pet-header">
            <i class="fas fa-key"></i>
            <h2>Set New Password</h2>
            <p>Create a secure password for your pet shop account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($success) || empty($success)): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Woof!123" required minlength="6">
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength mt-2">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <small class="text-muted">Use at least 6 characters</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Woof!123" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="password-match" class="mt-2"></div>
            </div>

            <button type="submit" class="btn btn-pet btn-primary w-100">
                <i class="fas fa-save me-2"></i> Update Password
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
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('strength-meter');
    const passwordMatch = document.getElementById('password-match');
    const confirmPassword = document.getElementById('confirm_password');
    
    passwordInput.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        strengthMeter.style.width = strength.percentage + '%';
        strengthMeter.style.backgroundColor = strength.color;
    });
    
    confirmPassword.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            passwordMatch.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Passwords don\'t match</span>';
        } else {
            passwordMatch.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Passwords match</span>';
        }
    });
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length > 5) strength += 1;
        if (password.length > 8) strength += 1;
        
        // Character variety
        if (/[A-Z]/.test(password)) strength += 1; // Uppercase
        if (/[a-z]/.test(password)) strength += 1; // Lowercase
        if (/[0-9]/.test(password)) strength += 1; // Numbers
        if (/[^A-Za-z0-9]/.test(password)) strength += 1; // Special chars
        
        // Calculate percentage and color
        const percentage = Math.min(strength * 20, 100);
        let color = '#ff5252'; // Red
        
        if (percentage >= 60) color = '#ffab40'; // Orange
        if (percentage >= 80) color = '#4caf50'; // Green
        
        return { percentage, color };
    }
</script>
</body>
</html>