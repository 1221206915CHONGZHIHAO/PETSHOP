<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['verified']) || !$_SESSION['verified']) {
    header("Location: forgot_password.php");
    exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $email = $_SESSION['email'];
        $plain_password = $new_password;

        $stmt = $conn->prepare("UPDATE customer SET Customer_password = ? WHERE Customer_email = ?");
        $stmt->bind_param("ss", $plain_password, $email);

        if ($stmt->execute()) {
            $success = "Password has been reset successfully!";
            session_destroy();
        } else {
            $error = "Error resetting password. Please try again.";
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
        /* [Same CSS as forgot_password.php] */
        
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
        
        .toggle-password {
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
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
            <p>Create a new secure password for your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $success ?> You can now <a href="login.php" class="alert-link">login</a>.
            </div>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Woof!123" required minlength="6">
                    <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength mt-2">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <small class="text-muted">Minimum 6 characters</small>
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
    const passwordInput = document.getElementById('new_password');
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
            passwordMatch.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords don\'t match</span>';
        } else {
            passwordMatch.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</span>';
        }
    });
    
</script>
</body>
</html>