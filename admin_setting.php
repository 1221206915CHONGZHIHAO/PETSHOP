<?php
include 'db_connection.php';

function get_admin_settings($conn) {
    $sql = "SELECT * FROM admin_settings WHERE id = 1";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result) ?? [];
}

function update_admin_settings($conn, $shop_name, $contact_email, $phone_number, $address, $logo_path) {
    $sql = "UPDATE admin_settings SET shop_name=?, contact_email=?, phone_number=?, address=?, logo_path=? WHERE id=1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $shop_name, $contact_email, $phone_number, $address, $logo_path);
    return mysqli_stmt_execute($stmt);
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_name = $_POST["shop_name"] ?? "";
    $contact_email = $_POST["contact_email"] ?? "";
    $phone_number = $_POST["phone_number"] ?? "";
    $address = $_POST["address"] ?? "";
    $logo_path = "";

    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $target_dir = "uploads/";
        $logo_path = $target_dir . basename($_FILES["logo"]["name"]);
        if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $logo_path)) {
            $error_message = "Failed to upload logo.";
        }
    } else {
        $settings = get_admin_settings($conn);
        $logo_path = $settings['logo_path'] ?? '';
    }

    if (empty($error_message)) {
        if (update_admin_settings($conn, $shop_name, $contact_email, $phone_number, $address, $logo_path)) {
            $success_message = "Settings updated successfully.";
        } else {
            $error_message = "Failed to update settings.";
        }
    }
}

$settings = get_admin_settings($conn);
$settings = array_merge([
    'shop_name' => '',
    'contact_email' => '',
    'phone_number' => '',
    'address' => '',
    'logo_path' => ''
], $settings);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Admin Settings</h2>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Shop Name</label>
            <input type="text" name="shop_name" class="form-control" value="<?php echo htmlspecialchars($settings['shop_name']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($settings['phone_number']); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control"><?php echo htmlspecialchars($settings['address']); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Logo</label>
            <input type="file" name="logo" class="form-control">
            <?php if (!empty($settings['logo_path']) && file_exists($settings['logo_path'])): ?>
                <div class="mt-2">
                    <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Current Logo" width="150">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
</body>
</html>
