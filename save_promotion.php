<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $message = '';
    $messageType = 'danger';

    try {
        if ($action === 'add') {
            // Validate inputs
            $required = ['promo_code', 'discount', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("All fields are required");
                }
            }

            // Check if promo code exists
            $promoCode = $conn->real_escape_string($_POST['promo_code']);
            $result = $conn->query("SELECT promo_code FROM promotion WHERE promo_code = '$promoCode'");
            if ($result->num_rows > 0) {
                throw new Exception("Promo code already exists");
            }

            // Prepare data
            $discount = (int)$_POST['discount'];
            $startDate = $conn->real_escape_string($_POST['start_date']);
            $endDate = $conn->real_escape_string($_POST['end_date']);
            $usageLimit = (int)$_POST['usage_limit'];

            // Insert new promotion
            $conn->query("INSERT INTO promotion
                (promo_code, discount, start_date, end_date, usage_limit) 
                VALUES ('$promoCode', $discount, '$startDate', '$endDate', $usageLimit)");
            
            $message = "Promotion created successfully";
            $messageType = "success";
        }
        elseif ($action === 'edit') {
            // Validate inputs
            if (empty($_POST['promo_code'])) {
                throw new Exception("Invalid promotion code");
            }

            // Prepare data
            $promoCode = $conn->real_escape_string($_POST['promo_code']);
            $discount = (int)$_POST['discount'];
            $startDate = $conn->real_escape_string($_POST['start_date']);
            $endDate = $conn->real_escape_string($_POST['end_date']);
            $usageLimit = (int)$_POST['usage_limit'];
            $status = $conn->real_escape_string($_POST['status']);

            // Update promotion
            $conn->query("UPDATE promotion SET 
                discount = $discount,
                start_date = '$startDate',
                end_date = '$endDate',
                usage_limit = $usageLimit,
                status = '$status'
                WHERE promo_code = '$promoCode'");
            
            $message = "Promotion updated successfully";
            $messageType = "success";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $messageType;
    header("Location: promotion.php");
    exit;
}

header("Location: promotion.php");
exit;