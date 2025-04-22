<?php
require_once 'db_connection.php';
session_start();

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$success = false;
$errors = [];
$settings = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $shop_name = trim($conn->real_escape_string($_POST['shop_name'] ?? ''));
    $contact_email = trim($conn->real_escape_string($_POST['contact_email'] ?? ''));
    $phone_number = trim($conn->real_escape_string($_POST['phone_number'] ?? ''));
    $address = trim($conn->real_escape_string($_POST['address'] ?? ''));
    $primary_color = trim($conn->real_escape_string($_POST['primary_color'] ?? '#4e73df'));
    $secondary_color = trim($conn->real_escape_string($_POST['secondary_color'] ?? '#1cc88a'));
    $business_hours = trim($conn->real_escape_string($_POST['business_hours'] ?? ''));
    $timezone = trim($conn->real_escape_string($_POST['timezone'] ?? 'UTC'));
    $currency = trim($conn->real_escape_string($_POST['currency'] ?? 'USD'));
    $items_per_page = intval($_POST['items_per_page'] ?? 10);
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

    // Validation
    if (empty($shop_name)) {
        $errors[] = "Shop name is required";
    }
    if (empty($contact_email) || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary_color)) {
        $errors[] = "Invalid primary color format";
    }

    // Handle file upload
    $logo_path = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['logo']['tmp_name']);
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('logo_') . '.' . $file_ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                $logo_path = $target_path;
                // Delete old logo if exists
                if (!empty($_POST['existing_logo']) && file_exists($_POST['existing_logo'])) {
                    unlink($_POST['existing_logo']);
                }
            } else {
                $errors[] = "Failed to upload logo";
            }
        } else {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        }
    }

    if (empty($errors)) {
        // Build SQL query
        $sql = "UPDATE shop_settings SET 
                shop_name = ?,
                contact_email = ?,
                phone_number = ?,
                address = ?,
                primary_color = ?,
                secondary_color = ?,
                business_hours = ?,
                timezone = ?,
                currency = ?,
                items_per_page = ?,
                enable_2fa = ?,
                maintenance_mode = ?";
        
        $params = [
            $shop_name, $contact_email, $phone_number, $address,
            $primary_color, $secondary_color, $business_hours,
            $timezone, $currency, $items_per_page, $enable_2fa, $maintenance_mode
        ];
        
        if ($logo_path) {
            $sql .= ", logo_path = ?";
            $params[] = $logo_path;
        }
        
        $sql .= " WHERE id = 1";
        
        // Use prepared statement
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success = true;
                // Update session with new settings if needed
                $_SESSION['settings_updated'] = true;
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

// Get current settings
$sql = "SELECT * FROM shop_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
} else {
    // Initialize default settings
    $default_sql = "INSERT INTO shop_settings (shop_name, contact_email) VALUES ('PetShop', 'admin@petshop.com')";
    if ($conn->query($default_sql)) {
        $settings = [
            'shop_name' => 'PetShop',
            'contact_email' => 'admin@petshop.com',
            'primary_color' => '#4e73df',
            'secondary_color' => '#1cc88a',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'items_per_page' => 10
        ];
    } else {
        $errors[] = "Failed to initialize settings: " . $conn->error;
    }
}

// Timezone and currency options
$timezones = DateTimeZone::listIdentifiers();
$currencies = [
    'USD' => 'US Dollar ($)',
    'EUR' => 'Euro (€)',
    'GBP' => 'British Pound (£)',
    'MYR' => 'Malaysian Ringgit (RM)',
    'INR' => 'Indian Rupee (₹)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyYourActualKeyHere&callback=initMap&libraries=places" async defer></script>
    <link rel="stylesheet" href="admin_home.css">
    <style>
        :root {
            --primary: <?= htmlspecialchars($settings['primary_color'] ?? '#4e73df') ?>;
            --secondary: <?= htmlspecialchars($settings['secondary_color'] ?? '#1cc88a') ?>;
        }
        .sidebar {
            background-color: var(--primary);
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .text-primary {
            color: var(--primary) !important;
        }
        .logo-preview {
            max-width: 150px;
            max-height: 150px;
        }
        .settings-section {
            margin-bottom: 2rem;
        }
        /* Ensure map container has dimensions */
#store-map {
    height: 400px;
    width: 100%;
    min-height: 300px;
    background-color: #eee;
}
    </style>
</head>
<body>
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
            <!-- Sidebar -->
                    <!-- Sidebar -->
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
                        <a class="nav-link text-light" href="promotion.php">
                            <i class="fas fa-tag me-2"></i>Promotions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light active" href="admin_setting.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4"><i class="fas fa-cog me-2"></i> System Settings</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Settings updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5 class="alert-heading">Error!</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($settings['logo_path'] ?? '') ?>">
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-store me-2"></i> Shop Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Shop Name *</label>
                                        <input type="text" class="form-control" name="shop_name" 
                                               value="<?= htmlspecialchars($settings['shop_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Contact Email *</label>
                                        <input type="email" class="form-control" name="contact_email" 
                                               value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone_number" 
                                               value="<?= htmlspecialchars($settings['phone_number'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?= 
                                            htmlspecialchars($settings['address'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Shop Logo</label>
                                        <input type="file" class="form-control" name="logo" accept="image/*">
                                        <?php if (!empty($settings['logo_path'])): ?>
                                            <div class="mt-3">
                                                <p class="mb-1">Current Logo:</p>
                                                <img src="<?= htmlspecialchars($settings['logo_path']) ?>" 
                                                     class="img-thumbnail logo-preview" alt="Current Logo">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Business Hours</label>
                                        <textarea class="form-control" name="business_hours" rows="3"><?= 
                                            htmlspecialchars($settings['business_hours'] ?? '') ?></textarea>
                                        <small class="text-muted">Example: Monday-Friday: 9AM-6PM, Saturday: 10AM-4PM</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-palette me-2"></i> Theme Customization
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Primary Color</label>
                                        <input type="color" class="form-control form-control-color" name="primary_color" 
                                               value="<?= htmlspecialchars($settings['primary_color'] ?? '#4e73df') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Secondary Color</label>
                                        <input type="color" class="form-control form-control-color" name="secondary_color" 
                                               value="<?= htmlspecialchars($settings['secondary_color'] ?? '#1cc88a') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-globe me-2"></i> Regional Settings
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-select" name="timezone">
                                            <?php foreach ($timezones as $tz): ?>
                                                <option value="<?= htmlspecialchars($tz) ?>" 
                                                    <?= ($settings['timezone'] ?? 'UTC') === $tz ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tz) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Currency</label>
                                        <select class="form-select" name="currency">
                                            <?php foreach ($currencies as $code => $name): ?>
                                                <option value="<?= htmlspecialchars($code) ?>" 
                                                    <?= ($settings['currency'] ?? 'USD') === $code ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-lock me-2"></i> Security Settings
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="enable_2fa" id="enable2fa" 
                                       <?= ($settings['enable_2fa'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enable2fa">Enable Two-Factor Authentication</label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" 
                                       <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                                <small class="text-muted d-block">When enabled, only administrators can access the site</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-tools me-2"></i> System Preferences
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Items Per Page</label>
                                <input type="number" class="form-control" name="items_per_page" min="5" max="100" 
                                       value="<?= htmlspecialchars($settings['items_per_page'] ?? 10) ?>">
                                <small class="text-muted">Number of items to display per page in lists</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Save All Settings
                        </button>
                    </div>
                        <!-- Map Controls -->
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.7436995642674!2d102.2761136!3d2.2494935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d1e56b9710cf4b%3A0x66b6b12b75469278!2sMultimedia%20University!5e0!3m2!1sen!2smy!4v1745332527830!5m2!1sen!2smy" 
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live preview of logo upload
        document.querySelector('input[name="logo"]').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.querySelector('.logo-preview');
                    if (preview) {
                        preview.src = event.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'img-thumbnail logo-preview mt-3';
                        img.alt = 'New Logo Preview';
                        e.target.parentNode.appendChild(img);
                    }
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
    <script>
let map;
let marker;

// Initialize Map
function initMap() {
    // Default to Kuala Lumpur coordinates if no data
    const defaultPosition = { 
        lat: <?= $settings['store_lat'] ?? 3.1390 ?>, 
        lng: <?= $settings['store_lng'] ?? 101.6869 ?> 
    };

    // Create map
    map = new google.maps.Map(document.getElementById('store-map'), {
        center: defaultPosition,
        zoom: 15,
        mapTypeId: 'roadmap'
    });

    // Add marker if coordinates exist
    <?php if (!empty($settings['store_lat']) && !empty($settings['store_lng'])): ?>
        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true
        });
    <?php endif; ?>

    // Click listener to add/move marker
    map.addListener('click', (e) => {
        if (marker) {
            marker.setPosition(e.latLng);
        } else {
            marker = new google.maps.Marker({
                position: e.latLng,
                map: map,
                draggable: true
            });
        }
        updateCoordinates(e.latLng.lat(), e.latLng.lng());
    });
}

// Update form fields with coordinates
function updateCoordinates(lat, lng) {
    document.getElementById('store-lat').value = lat;
    document.getElementById('store-lng').value = lng;
}

// Initialize when page loads
window.onload = initMap;
</script>
</body>
</html>