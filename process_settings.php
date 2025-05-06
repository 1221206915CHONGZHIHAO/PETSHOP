<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'petshop');
if ($db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$staff_id = $_SESSION['staff_id'];
$response = ['success' => false, 'message' => ''];

// Handle password change
if ($_POST['action'] === 'change_password') {
    $current_password = $_POST['currentPassword'];
    $new_password = $_POST['newPassword'];
    $confirm_password = $_POST['confirmPassword'];
    
    // Fetch current password hash
    $stmt = $db->prepare("SELECT Staff_password FROM staff WHERE Staff_ID = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    if (!password_verify($current_password, $staff['Staff_password'])) {
        $response['message'] = 'Current password is incorrect';
        echo json_encode($response);
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($new_password) < 8) {
        $response['message'] = 'Password must be at least 8 characters';
        echo json_encode($response);
        exit();
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE staff SET Staff_password = ? WHERE Staff_ID = ?");
    $stmt->bind_param("si", $hashed_password, $staff_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password updated successfully';
    } else {
        $response['message'] = 'Failed to update password';
    }
}

// Handle profile picture upload
if ($_POST['action'] === 'upload_avatar') {
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profileImage']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response['message'] = 'Only JPG, PNG, and GIF files are allowed';
            echo json_encode($response);
            exit();
        }
        
        $upload_dir = 'staff_avatars/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
        $avatar_filename = $staff_id . '.' . $file_extension;
        $avatar_path = $upload_dir . $avatar_filename;
        
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $avatar_path)) {
            $response['success'] = true;
            $response['message'] = 'Profile picture updated successfully';
        } else {
            $response['message'] = 'Failed to upload image';
        }
    } else {
        $response['message'] = 'No file uploaded or upload error';
    }
}

$db->close();
echo json_encode($response);
?>