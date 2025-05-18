<?php
session_start();

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "petshop";

$conn = new mysqli($host, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";
$redirect_url = "";

// Get redirect URL from GET parameter or set default
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Check if the form was submitted with proper captcha verification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if captcha verification is included
    $captcha_verified = isset($_POST['captcha_verified']) && $_POST['captcha_verified'] === 'true';
    
    $role = $_POST['role']; 
    $login_input = trim($_POST['login_input']); 
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    if (empty($login_input) || empty($password)) {
        $error_message = "All fields are required.";
    } 
    // Skip captcha check for development/testing purposes - in production you'd require verification
    // else if (!$captcha_verified) {
    //    $error_message = "Please complete the security verification.";
    // } 
    else {
        // Admin login
        if ($role === "admin" && $login_input === "ADMIN" && $password === "PETSHOP1") {
            $_SESSION['role'] = "admin";
            $_SESSION['admin_logged_in'] = true; 
            $_SESSION['username'] = "ADMIN";
            $_SESSION['email'] = "admin@petshop.com";
            $success_message = "Login successful! Redirecting...";
            
            // Set redirect URL for admin
            $redirect_url = !empty($redirect) ? $redirect : 'admin_homepage.php';
        }
        else {
            if ($role === "staff") {
                $sql = "SELECT Staff_id, Staff_Username, Staff_Email, Staff_Password, status, password_reset_token, img_URL 
                        FROM Staff 
                        WHERE Staff_Username = ?";
            } elseif ($role === "customer") {
                $sql = "SELECT Customer_id, Customer_name, Customer_email, Customer_password 
                        FROM Customer 
                        WHERE Customer_name = ?";
            } else {
                $error_message = "Invalid role selected.";
            }

            if (empty($error_message)) {
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $error_message = "Database error. Please try again later.";
                } else {
                    $stmt->bind_param("s", $login_input);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        if ($role === "staff") {
                            $stmt->bind_result($db_staff_id, $db_username, $db_email, $db_password, $db_status, $db_reset_token, $db_img_url);
                        } elseif ($role === "customer") {
                            $stmt->bind_result($db_customer_id, $db_username, $db_email, $db_password);
                        }
                        $stmt->fetch();

                        if ($role === "staff" && $db_status !== 'Active') {
                            $error_message = "Account is inactive. Please contact administrator.";
                        } 
                        elseif ($role === "staff" && !empty($db_reset_token)) {
                            if ($password === $db_password) {
                                $_SESSION['reset_token'] = $db_reset_token;
                                $_SESSION['staff_id'] = $db_staff_id;
                                $redirect_url = "force_password_reset.php";
                                $success_message = "Please set a new password.";
                            } else {
                                $error_message = "Invalid temporary password.";
                            }
                        }
                        elseif ($password === $db_password) {
                            $_SESSION['role'] = $role;
                            if ($role === "staff") {
                                $_SESSION['staff_id'] = $db_staff_id;
                                $_SESSION['username'] = $db_username;
                                $_SESSION['email'] = $db_email;
                                $_SESSION['avatar_path'] = $db_img_url; // Store avatar path in session
                                $conn->query("UPDATE Staff SET password_reset_token = NULL WHERE Staff_id = $db_staff_id");
                                
                                // Record successful staff login
                                $conn->query("INSERT INTO staff_login_logs (staff_id, username, email, status) 
                                            VALUES ($db_staff_id, '$db_username', '$db_email', 'login')");
                                
                                $redirect_url = !empty($redirect) ? $redirect : 'staff_homepage.php';
                            } elseif ($role === "customer") {
                                $_SESSION['customer_id'] = $db_customer_id;
                                $_SESSION['customer_name'] = $db_username;
                                $_SESSION['email'] = $db_email;
                                $redirect_url = !empty($redirect) ? $redirect : 'userhomepage.php';
                                
                                // Record successful customer login
                                $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                            VALUES ('$db_username', '$db_email', 'login')");
                            }
                            $success_message = "Login successful! Redirecting...";
                        } else {
                            $error_message = "Invalid password.";
                            if ($role === "staff") {
                                $conn->query("UPDATE Staff SET 
                                    login_attempts = login_attempts + 1, 
                                    last_failed_login = NOW() 
                                    WHERE Staff_id = $db_staff_id");
                                
                                // Record failed staff login attempt
                                $conn->query("INSERT INTO staff_login_logs (staff_id, username, email, status, ip_address) 
                                            VALUES ($db_staff_id, '$db_username', '$db_email', 'failed', '{$_SERVER['REMOTE_ADDR']}')");
                            }
                            
                            // Record failed login attempt if customer
                            if ($role === "customer") {
                                $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                            VALUES ('', '$login_input', 'failed')");
                            }
                        }
                    } else {
                        $error_message = "Username not found.";
                        // Record failed login attempt if customer
                        if ($role === "customer") {
                            $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                        VALUES ('', '$login_input', 'failed')");
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Shop Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Login.css">
    <style>
        /* Captcha Styles */
        .captcha-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
        }

        .captcha-image {
            position: relative;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .captcha-puzzle-piece {
            position: absolute;
            width: 45px;
            height: 45px;
            background-color: rgba(76, 175, 80, 0.7); /* Green with transparency */
            border: 2px solid #4CAF50;
            border-radius: 5px;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            transition: left 0.1s;
            z-index: 10;
        }

        .captcha-puzzle-target {
            position: absolute;
            width: 45px;
            height: 45px;
            border: 2px dashed #4CAF50; /* Green dashed border */
            border-radius: 5px;
            top: 50%;
            right: 50px; /* Fixed position on the right side */
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.2);
            z-index: 5;
        }

        .slider-container {
            position: relative;
            height: 40px;
            margin-top: 15px;
        }

        .slider-track {
            position: relative;
            height: 40px;
            background-color: #f1f1f1;
            border-radius: 20px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .slider-thumb {
            position: absolute;
            width: 40px;
            height: 40px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            left: 0;
            top: 0;
            font-size: 18px;
            transition: transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .slider-thumb:hover {
            transform: scale(1.05);
        }

        .slider-thumb:active {
            transform: scale(0.95);
        }

        .slider-text {
            text-align: center;
            margin-top: 10px;
            color: #777;
            font-size: 14px;
        }

        .slider-success .slider-track {
            background-color: rgba(40, 167, 69, 0.2);
        }

        .slider-success .slider-thumb {
            background-color: #28a745;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.5s;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
            100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
        }

        .pulse {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <!-- Paw print decorations (replace with actual cat_paw.png when available) -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="login-banner">
            <!-- Replace with your actual logo -->
            <h2 class="section-title">Welcome</h2>
        </div>
        
        <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>
        <div class="alert alert-success" id="successAlert" style="display: none;"></div>
        
        <form method="POST" action="" id="loginForm">
            <!-- Role selector with icons -->
            <div class="role-selector mb-4">
                <label class="role-option" id="adminOption">
                    <input type="radio" name="role" value="admin" required>
                    <i class="bi bi-shield-lock"></i>
                    Admin
                </label>
                <label class="role-option" id="staffOption">
                    <input type="radio" name="role" value="staff" required>
                    <i class="bi bi-person-badge"></i>
                    Staff
                </label>
                <label class="role-option active" id="customerOption">
                    <input type="radio" name="role" value="customer" required checked>
                    <i class="bi bi-person-heart"></i>
                    Pet Owner
                </label>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-person me-2" style="color: var(--primary);"></i>
                    Username
                </label>
                <input type="text" name="login_input" class="form-control" required placeholder="Enter your username">
            </div>

            <div class="mb-4 position-relative">
                <label class="form-label">
                    <i class="bi bi-lock me-2" style="color: var(--primary);"></i>
                    Password
                </label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                <button type="button" id="togglePassword" class="btn">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <input type="hidden" name="redirect" id="redirectInput" value="">
            <input type="hidden" name="captcha_verified" id="captchaVerified" value="false">

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Login
            </button>
        </form>

        <p class="text-center mt-4">
            New pet parent? <a href="register.php" class="d-inline-block">Register here</a><br>
            <a href="forgot_password.php" class="mt-2 d-inline-block">
                <i class="bi bi-question-circle me-1"></i>
                Forgot Password?
            </a>
        </p>
    </div>
</div>

<!-- Captcha Modal -->
<div class="modal fade" id="captchaModal" tabindex="-1" aria-labelledby="captchaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="captchaModalLabel">Security Verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <div class="captcha-container">
          <!-- Add the image container -->
          <div class="captcha-image">
            <!-- Background image -->
            <img src="captcha_bg.png" alt="Captcha Background" style="width: 100%; height: 100%; object-fit: cover;">
            
            <!-- Target puzzle outline -->
            <div class="captcha-puzzle-target"></div>
            
            <!-- Moving puzzle piece -->
            <div class="captcha-puzzle-piece"></div>
          </div>
          
          <div class="slider-container mt-3">
            <div class="slider-track">
              <div class="slider-thumb" id="sliderThumb">→</div>
            </div>
            <div class="slider-text">Slide right to fit puzzle piece</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Parse URL to get redirect parameter
const urlParams = new URLSearchParams(window.location.search);
const redirect = urlParams.get('redirect') || '';
document.getElementById('redirectInput').value = redirect;

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye'); 
    }
});

// Role selector functionality
const roleOptions = document.querySelectorAll('.role-option');
roleOptions.forEach(option => {
    option.addEventListener('click', function() {
        roleOptions.forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});

// Display PHP messages via JavaScript
<?php if (!empty($error_message)): ?>
    document.getElementById('errorAlert').textContent = "<?php echo $error_message; ?>";
    document.getElementById('errorAlert').style.display = 'block';
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    document.getElementById('successAlert').textContent = "<?php echo $success_message; ?>";
    document.getElementById('successAlert').style.display = 'block';
<?php endif; ?>

<?php if (!empty($redirect_url)): ?>
setTimeout(function () {
    window.location.href = "<?php echo $redirect_url; ?>";
}, 2000); // Redirect after 2 seconds
<?php endif; ?>

// Updated Slider Captcha Implementation
function initSliderCaptcha() {
    const sliderThumb = document.getElementById('sliderThumb');
    const sliderTrack = document.querySelector('.slider-track');
    const captchaModal = document.getElementById('captchaModal');
    const puzzlePiece = document.querySelector('.captcha-puzzle-piece');
    const puzzleTarget = document.querySelector('.captcha-puzzle-target');
    
    let isDragging = false;
    let startPositionX = 0;
    let currentPositionX = 0;
    
    // Add pulse animation to the target to make it more noticeable
    puzzleTarget.classList.add('pulse');
    
    // Reset positions
    sliderThumb.style.left = '0px';
    puzzlePiece.style.left = '10px';
    
    // Touch events for mobile
    sliderThumb.addEventListener('touchstart', startDrag);
    document.addEventListener('touchmove', drag);
    document.addEventListener('touchend', endDrag);
    
    // Mouse events for desktop
    sliderThumb.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', endDrag);
    
    function startDrag(e) {
        isDragging = true;
        startPositionX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        currentPositionX = sliderThumb.offsetLeft;
        
        // Prevent default behavior
        e.preventDefault();
    }
    
    function drag(e) {
        if (!isDragging) return;
        
        // Calculate the new position
        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        let deltaX = clientX - startPositionX;
        let newPosition = currentPositionX + deltaX;
        
        // Apply constraints
        const maxPosition = sliderTrack.offsetWidth - sliderThumb.offsetWidth;
        newPosition = Math.max(0, Math.min(newPosition, maxPosition));
        
        // Update slider thumb position
        sliderThumb.style.left = newPosition + 'px';
        
        // Calculate puzzle piece position based on slider position
        const imageContainer = document.querySelector('.captcha-image');
        const maxPuzzlePosition = imageContainer.offsetWidth - puzzlePiece.offsetWidth - 10;
        const puzzlePosition = (newPosition / maxPosition) * maxPuzzlePosition + 10;
        
        // Update puzzle piece position
        puzzlePiece.style.left = puzzlePosition + 'px';
    }
    
    function endDrag() {
        if (!isDragging) return;
        isDragging = false;
        
        // Get current position of the puzzle piece
        const puzzlePieceRect = puzzlePiece.getBoundingClientRect();
        const imageRect = document.querySelector('.captcha-image').getBoundingClientRect();
        const puzzlePieceLeft = puzzlePieceRect.left - imageRect.left;
        
        // Get target position
        const targetRect = puzzleTarget.getBoundingClientRect();
        const targetLeft = targetRect.left - imageRect.left;
        
        // Check if the puzzle piece is close enough to the target
        const isSuccess = Math.abs(puzzlePieceLeft - targetLeft) < 15; // 15px tolerance
        
        if (isSuccess) {
            // If successful, add success class and submit the form
            document.querySelector('.slider-container').classList.add('slider-success');
            
            // Match the puzzle piece exactly to the target
            puzzlePiece.style.left = targetLeft + 'px';
            
            // Mark captcha as verified
            document.getElementById('captchaVerified').value = 'true';
            
            // Stop the pulse animation on the target
            puzzleTarget.classList.remove('pulse');
            
            // Close the modal after a brief delay
            setTimeout(() => {
                // Hide modal
                bootstrap.Modal.getInstance(captchaModal).hide();
                
                // Submit the form
                document.getElementById('loginForm').submit();
            }, 1000);
        } else {
            // If failed, reset the slider and puzzle piece
            sliderThumb.style.left = '0px';
            puzzlePiece.style.left = '10px';
            
            // Visual feedback for failure
            sliderThumb.classList.add('shake');
            setTimeout(() => {
                sliderThumb.classList.remove('shake');
            }, 500);
        }
    }
}

// Your existing login form event listener
document.getElementById('loginForm').addEventListener('submit', function(e) {
    // Only show captcha if not already verified
    if (document.getElementById('captchaVerified').value !== 'true') {
        e.preventDefault(); // Prevent form from submitting immediately
        
        // Show the captcha modal
        const captchaModal = new bootstrap.Modal(document.getElementById('captchaModal'));
        captchaModal.show();
        
        // Initialize the slider captcha
        initSliderCaptcha();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>