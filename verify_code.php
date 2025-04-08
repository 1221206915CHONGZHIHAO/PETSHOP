<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* [Same CSS as forgot_password.php] */
        
        .code-input {
            letter-spacing: 10px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        
        .otp-instruction {
            background-color: #f9f5f0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #ffab91;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="pet-container">
        <i class="fas fa-paw pet-paw-print paw-1"></i>
        <i class="fas fa-paw pet-paw-print paw-2"></i>
        
        <div class="pet-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Verify Your Email</h2>
            <p>We've sent a verification code to your email</p>
        </div>
        
        <div class="otp-instruction">
            <i class="fas fa-info-circle text-primary me-2"></i>
            Check your inbox for a 6-digit code. It may take a minute to arrive.
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Verification Code</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="text" name="otp" class="form-control code-input" placeholder="123456" required maxlength="6" pattern="\d{6}">
                </div>
                <small class="text-muted">Enter the 6-digit code sent to your email</small>
            </div>

            <button type="submit" class="btn btn-pet btn-primary w-100">
                <i class="fas fa-check-circle me-2"></i> Verify Code
            </button>
        </form>

        <div class="pet-footer">
            <a href="forgot_password.php"><i class="fas fa-arrow-left me-1"></i> Back to email entry</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto focus on OTP input
    document.querySelector('input[name="otp"]').focus();
    
    // Auto submit when 6 digits are entered
    document.querySelector('input[name="otp"]').addEventListener('input', function(e) {
        if (this.value.length === 6) {
            this.form.submit();
        }
    });
</script>
</body>
</html>