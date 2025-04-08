<?php
include('db_connection.php');
session_start();

// Verify admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Generate temporary password and reset token
    $temp_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    $reset_token = bin2hex(random_bytes(32));
    
    // Update staff record
    $sql = "UPDATE Staff SET 
            status = 'Active',
            password = ?,
            login_attempts = 0,
            last_failed_login = NULL,
            password_reset_token = ?,
            token_expiry = DATE_ADD(NOW(), INTERVAL 1 DAY)
            WHERE Staff_ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $hashed_password, $reset_token, $id);
    
    if ($stmt->execute()) {
        // Send email with temporary password
        $email_sql = "SELECT Staff_Email, Staff_name FROM Staff WHERE Staff_ID = ?";
        $email_stmt = $conn->prepare($email_sql);
        $email_stmt->bind_param("i", $id);
        $email_stmt->execute();
        $result = $email_stmt->get_result();
        $staff = $result->fetch_assoc();
        
        // Email configuration (pseudo-code)
        $to = $staff['Staff_Email'];
        $subject = "Account Reactivation - PetShop";
        $message = "Hello " . $staff['Staff_name'] . ",\n\n";
        $message .= "Your account has been reactivated. Use this temporary password to login:\n\n";
        $message .= "Temporary Password: " . $temp_password . "\n\n";
        $message .= "You must reset your password after login.\n";
        $message .= "Login: http://yourdomain.com/login.php";
        $headers = "From: noreply@yourdomain.com";
        
        mail($to, $subject, $message, $headers);
        
        $_SESSION['success'] = "Account reactivated. Temporary password sent to staff email.";
    } else {
        $_SESSION['error'] = "Error reactivating account: " . $conn->error;
    }
    
    header("Location: manage_staff.php");
    exit();
}
?>