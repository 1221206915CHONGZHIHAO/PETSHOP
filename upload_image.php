<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $upload_dir = 'uploads/profile_images/';
    $file = $_FILES['profile_image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload failed with error code ' . $file['error'];
        echo json_encode($response);
        exit();
    }

    // Validate file type
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        $response['message'] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
        echo json_encode($response);
        exit();
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        $response['message'] = 'File size exceeds 2MB limit.';
        echo json_encode($response);
        exit();
    }

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $customer_id . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Update database
        $stmt = $conn->prepare("UPDATE customer SET profile_image = ? WHERE Customer_ID = ?");
        $stmt->bind_param("si", $filename, $customer_id);
        
        if ($stmt->execute()) {
            // Delete old image if exists
            $stmt = $conn->prepare("SELECT profile_image FROM customer WHERE Customer_ID = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_image = $result->fetch_assoc()['profile_image'];
            
            if ($old_image && file_exists($upload_dir . $old_image) && $old_image !== $filename) {
                unlink($upload_dir . $old_image);
            }

            $response['success'] = true;
            $response['message'] = 'Profile image uploaded successfully!';
            $response['image_path'] = $destination;
        } else {
            $response['message'] = 'Failed to update database: ' . $conn->error;
            unlink($destination); // Remove uploaded file if DB update fails
        }
    } else {
        $response['message'] = 'Failed to move uploaded file.';
    }
} else {
    $response['message'] = 'No file uploaded or invalid request.';
}

echo json_encode($response);
?>