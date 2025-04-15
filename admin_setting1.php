<?php
// Start session and check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Database connection
require_once 'db_connection.php';

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT * FROM admin_settings LIMIT 1");
if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; }
        .sidebar.collapsed { display: none; }
        @media (min-width: 768px) {
            .sidebar.collapsed { display: block; }
        }
        .settings-group { margin-bottom: 2rem; }
    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">PetShop Admin</a>
    </div>
    <div>
        <span class="text-light me-3">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-2 d-md-block bg-dark sidebar">
            <div class="position-sticky">
                <h4 class="text-light text-center py-3"><i class="fas fa-paw me-2"></i>Admin Menu</h4>
                <ul class="nav flex-column">
                    <!-- Your existing menu items -->
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="admin_setting.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-cog me-2"></i>Admin Settings</h1>
            </div>

            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['settings_message'])): ?>
                <div class="alert alert-<?= $_SESSION['settings_message_type'] ?> alert-dismissible fade show">
                    <?= $_SESSION['settings_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['settings_message'], $_SESSION['settings_message_type']); ?>
            <?php endif; ?>

            <!-- Main Settings Form -->
            <form method="POST" action="save_setting.php">
                <input type="hidden" name="settings_type" value="main">

                <div class="settings-group card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-store me-2"></i>Shop Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Shop Name*</label>
                                <input type="text" name="shop_name" class="form-control" 
                                       value="<?= htmlspecialchars($settings['shop_name'] ?? 'PetShop') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Email*</label>
                                <input type="email" name="contact_email" class="form-control" 
                                       value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shop Address</label>
                            <textarea name="shop_address" class="form-control" rows="2"><?= 
                                htmlspecialchars($settings['shop_address'] ?? '') ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone_number" class="form-control" 
                                       value="<?= htmlspecialchars($settings['phone_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Hours</label>
                                <input type="text" name="business_hours" class="form-control" 
                                       value="<?= htmlspecialchars($settings['business_hours'] ?? '9:00 AM - 6:00 PM') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-group card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-globe me-2"></i>System Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Timezone*</label>
                                <select name="timezone" class="form-select" required>
                                    <?php
                                    $timezones = [
                                        'UTC' => 'UTC',
                                        'America/New_York' => 'Eastern Time (EST/EDT)',
                                        'America/Chicago' => 'Central Time (CST/CDT)',
                                        'America/Los_Angeles' => 'Pacific Time (PST/PDT)'
                                    ];
                                    $currentTz = $settings['timezone'] ?? 'UTC';
                                    foreach ($timezones as $tz => $label): ?>
                                        <option value="<?= $tz ?>" <?= $tz === $currentTz ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency*</label>
                                <select name="currency" class="form-select" required>
                                    <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                                    <option value="EUR" <?= ($settings['currency'] ?? 'USD') === 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Items Per Page*</label>
                                <input type="number" name="items_per_page" class="form-control" 
                                       value="<?= htmlspecialchars($settings['items_per_page'] ?? '10') ?>" min="5" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="settings-group card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-lock me-2"></i>Security Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" 
                                   <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="enable_2fa" id="enable2FA" 
                                   <?= ($settings['enable_2fa'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable2FA">Enable Two-Factor Authentication</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="login_alerts" id="loginAlerts" 
                                   <?= ($settings['login_alerts'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="loginAlerts">Receive Login Alerts</label>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });
});
</script>
</body>
</html>