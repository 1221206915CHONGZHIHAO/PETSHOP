<?php
session_start();

function validateAdminSession() {
    // Check if all required session variables are set
    if (!isset($_SESSION['admin_logged_in']) || 
        $_SESSION['admin_logged_in'] !== true || 
        $_SESSION['role'] !== 'admin' ||
        !isset($_SESSION['user_agent']) || 
        !isset($_SESSION['ip_address']) ||
        !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check for session hijacking
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] || 
        $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    
    // Check for session timeout (30 minutes)
    if (time() - $_SESSION['last_activity'] > 1800) {
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

if (!validateAdminSession()) {
    // Clear session and redirect
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>