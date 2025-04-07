<?php
session_start();
include('db_connection.php');

// Verify reset token
if (!isset($_SESSION['reset_token']) || !isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Validate token
$sql = "SELECT * FROM staff 
        WHERE staff_id = ? 
        AND password_reset_token = ?
        AND token_expiry > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $_SESSION['staff_id'], $_SESSION['reset_token']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    $_SESSION['error'] = "Invalid or expired reset link";
    header("Location: login.php");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords don't match";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE staff SET 
                      password = ?,
                      password_reset_token = NULL,
                      token_expiry = NULL
                      WHERE staff_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $_SESSION['staff_id']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password updated successfully! Please login";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error updating password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Set New Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required minlength="8">
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Set Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>