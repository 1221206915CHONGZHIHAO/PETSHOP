<?php
include('db_connection.php');
session_start();

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Generate temporary password and token
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
        // Get staff email for notification
        $email_sql = "SELECT Staff_Email, Staff_name FROM Staff WHERE Staff_ID = ?";
        $email_stmt = $conn->prepare($email_sql);
        $email_stmt->bind_param("i", $id);
        $email_stmt->execute();
        $result = $email_stmt->get_result();
        $staff = $result->fetch_assoc();
        
        // Send reactivation email (in production, use PHPMailer or similar)
        $to = $staff['Staff_Email'];
        $subject = "Your PetShop Account Has Been Reactivated";
        $message = "Hello " . $staff['Staff_name'] . ",\n\n";
        $message .= "Your account has been reactivated. Please use the following temporary password to login:\n\n";
        $message .= "Temporary Password: " . $temp_password . "\n\n";
        $message .= "You will be required to set a new password upon first login.\n\n";
        $message .= "Login here: http://yourpetshop.com/staff_login.php\n\n";
        $message .= "This temporary password will expire in 24 hours.";
        $headers = "From: admin@yourpetshop.com";
        
        mail($to, $subject, $message, $headers);
        
        $_SESSION['success_message'] = "Staff account reactivated successfully. Temporary password sent to staff email.";
    } else {
        $_SESSION['error_message'] = "Error reactivating account: " . $conn->error;
    }
    
    header("Location: manage_staff.php");
    exit();
}
?>