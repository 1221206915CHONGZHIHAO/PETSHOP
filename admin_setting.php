<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_home.css">
</head>
<body>

<!-- Reuse the same navigation from admin_homepage.php -->
<nav class="navbar navbar-dark bg-dark px-3">
    <div class="d-flex align-items-center">
        <button class="btn btn-dark me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="#">PetShop Admin</a>
    </div>
    <div>
        <span class="text-light me-3">Welcome, Admin</span>
        <a href="login.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (same as homepage) -->
        <nav id="sidebar" class="col-md-2 d-md-block bg-dark sidebar">
            <div class="position-sticky">
                <h4 class="text-light text-center py-3"><i class="fas fa-paw me-2"></i>Admin Menu</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_homepage.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#staffMenu">
                            <i class="fas fa-users me-2"></i>Staff Management
                        </a>
                        <div class="collapse" id="staffMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="manage_staff.php">
                                        <i class="fas fa-list me-2"></i>Staff List
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" data-bs-toggle="collapse" href="#orderMenu">
                            <i class="fas fa-shopping-cart me-2"></i>Order Management
                        </a>
                        <div class="collapse" id="orderMenu">
                            <ul class="nav flex-column ps-4">
                                <li class="nav-item">
                                    <a class="nav-link text-light" href="orders.php">
                                        <i class="fas fa-list me-2"></i>Current Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-chart-line me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-tag me-2"></i>Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="admin_settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-cog me-2"></i>System Settings</h1>
            </div>

            <!-- Settings Tabs -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">Email</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">Payment</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">Security</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="settingsTabContent">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shopName" class="form-label">Shop Name</label>
                                <input type="text" class="form-control" id="shopName" value="PetShop">
                            </div>
                            <div class="col-md-6">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone">
                                    <option value="UTC" selected>UTC</option>
                                    <option value="EST">Eastern Time (EST)</option>
                                    <option value="PST">Pacific Time (PST)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency">
                                    <option value="USD" selected>US Dollar (USD)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                    <option value="GBP">British Pound (GBP)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="dateFormat" class="form-label">Date Format</label>
                                <select class="form-select" id="dateFormat">
                                    <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="shopDescription" class="form-label">Shop Description</label>
                            <textarea class="form-control" id="shopDescription" rows="3">Premium pet supplies and accessories</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>

                <!-- Email Settings -->
                <div class="tab-pane fade" id="email" role="tabpanel">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smtpHost" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtpHost" value="smtp.example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="smtpPort" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtpPort" value="587">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smtpUser" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" id="smtpUser" value="admin@example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="smtpPass" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" id="smtpPass" placeholder="Enter password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="emailFrom" class="form-label">From Email Address</label>
                                <input type="email" class="form-control" id="emailFrom" value="noreply@example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="emailFromName" class="form-label">From Name</label>
                                <input type="text" class="form-control" id="emailFromName" value="PetShop Admin">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            <label class="form-check-label" for="emailNotifications">
                                Enable Email Notifications
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Email Settings</button>
                    </form>
                </div>

                <!-- Payment Settings -->
                <div class="tab-pane fade" id="payment" role="tabpanel">
                    <form>
                        <h5 class="mb-3">Payment Methods</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="enablePaypal" checked>
                            <label class="form-check-label" for="enablePaypal">
                                PayPal
                            </label>
                        </div>
                        <div class="row mb-3 ms-3">
                            <div class="col-md-6">
                                <label for="paypalEmail" class="form-label">PayPal Email</label>
                                <input type="email" class="form-control" id="paypalEmail" value="payments@example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="paypalMode" class="form-label">PayPal Mode</label>
                                <select class="form-select" id="paypalMode">
                                    <option value="sandbox">Sandbox</option>
                                    <option value="live" selected>Live</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="enableStripe" checked>
                            <label class="form-check-label" for="enableStripe">
                                Stripe
                            </label>
                        </div>
                        <div class="row mb-3 ms-3">
                            <div class="col-md-6">
                                <label for="stripePublishableKey" class="form-label">Publishable Key</label>
                                <input type="text" class="form-control" id="stripePublishableKey" value="pk_test_xxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label for="stripeSecretKey" class="form-label">Secret Key</label>
                                <input type="password" class="form-control" id="stripeSecretKey" placeholder="Enter secret key">
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="enableCOD">
                            <label class="form-check-label" for="enableCOD">
                                Cash on Delivery (COD)
                            </label>
                        </div>
                        
                        <h5 class="mb-3 mt-4">Tax Settings</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="taxRate" class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control" id="taxRate" value="8.25" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label for="taxType" class="form-label">Tax Type</label>
                                <select class="form-select" id="taxType">
                                    <option value="inclusive" selected>Inclusive in price</option>
                                    <option value="exclusive">Added at checkout</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Payment Settings</button>
                    </form>
                </div>

                <!-- Security Settings -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="adminUsername" class="form-label">Admin Username</label>
                                <input type="text" class="form-control" id="adminUsername" value="admin" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="adminEmail" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="adminEmail" value="admin@example.com">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="force2FA">
                            <label class="form-check-label" for="force2FA">
                                Enable Two-Factor Authentication
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="loginAlerts" checked>
                            <label class="form-check-label" for="loginAlerts">
                                Receive login alerts
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

   // Form submission handling with AJAX
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const settingsType = this.closest('.tab-pane').id;
        formData.append('settings_type', settingsType);
        
        fetch('save_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + error.message);
        });
    });
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('main');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
});
</script>

</body>
</html>