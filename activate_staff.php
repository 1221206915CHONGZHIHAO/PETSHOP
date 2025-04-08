<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "UPDATE Staff SET 
        status = 'Active',
        login_attempts = 0,
        last_failed_login = NULL,
        password_reset_token = NULL,
        token_expiry = NULL
        WHERE Staff_ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Account reactivated successfully.";
    } else {
        $_SESSION['error'] = "Error reactivating account: " . $conn->error;
    }

    $stmt->close();
    header("Location: manage_staff.php");
    exit();
}
?>
