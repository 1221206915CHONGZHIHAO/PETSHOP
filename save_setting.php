<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsType = $_POST['settings_type'] ?? '';
    $message = '';
    $messageType = 'danger';

    try {
        // Prepare the base query
        $query = "INSERT INTO admin_settings SET 
                 shop_name = ?, 
                 contact_email = ?, 
                 shop_address = ?, 
                 phone_number = ?, 
                 business_hours = ?, 
                 timezone = ?, 
                 currency = ?, 
                 items_per_page = ?, 
                 maintenance_mode = ?, 
                 enable_2fa = ?, 
                 login_alerts = ?
                 ON DUPLICATE KEY UPDATE 
                 shop_name = VALUES(shop_name),
                 contact_email = VALUES(contact_email),
                 shop_address = VALUES(shop_address),
                 phone_number = VALUES(phone_number),
                 business_hours = VALUES(business_hours),
                 timezone = VALUES(timezone),
                 currency = VALUES(currency),
                 items_per_page = VALUES(items_per_page),
                 maintenance_mode = VALUES(maintenance_mode),
                 enable_2fa = VALUES(enable_2fa),
                 login_alerts = VALUES(login_alerts)";

        $stmt = $conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param(
            "ssssssssiii",
            $_POST['shop_name'],
            $_POST['contact_email'],
            $_POST['shop_address'],
            $_POST['phone_number'],
            $_POST['business_hours'],
            $_POST['timezone'],
            $_POST['currency'],
            $_POST['items_per_page'],
            $maintenanceMode,
            $enable2fa,
            $loginAlerts
        );

        // Set checkbox values (0 or 1)
        $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
        $enable2fa = isset($_POST['enable_2fa']) ? 1 : 0;
        $loginAlerts = isset($_POST['login_alerts']) ? 1 : 0;

        if ($stmt->execute()) {
            $message = 'Settings saved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error saving settings: ' . $conn->error;
        }
    } catch (Exception $e) {
        $message = 'Database error: ' . $e->getMessage();
    }

    $_SESSION['settings_message'] = $message;
    $_SESSION['settings_message_type'] = $messageType;
    header('Location: admin_setting.php');
    exit;
}

header('Location: admin_setting.php');
exit;