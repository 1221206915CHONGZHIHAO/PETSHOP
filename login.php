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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $captcha_verified = isset($_POST['captcha_verified']) && $_POST['captcha_verified'] === 'true';
    $login_input = trim($_POST['login_input']); 
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    if (empty($login_input) || empty($password)) {
        $error_message = "All fields are required.";
    } else {
        $sql = "SELECT Customer_id, Customer_name, Customer_email, Customer_password, is_active 
                FROM Customer 
                WHERE Customer_name = ? OR Customer_email = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("ss", $login_input, $login_input);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($db_customer_id, $db_username, $db_email, $db_password, $db_is_active);
                $stmt->fetch();

                if ($db_is_active != 1) {
                    $error_message = "Account is deactivated. Please contact administrator.";
                }
                elseif ($password === $db_password) {
                    $_SESSION['role'] = "customer";
                    $_SESSION['customer_id'] = $db_customer_id;
                    $_SESSION['customer_name'] = $db_username;
                    $_SESSION['email'] = $db_email;
                    $redirect_url = !empty($redirect) ? $redirect : 'userhomepage.php';
                    
                    $conn->query("INSERT INTO customer_login_logs (username, email, status) 
                                VALUES ('$db_username', '$db_email', 'login')");
                    
                    $success_message = "Login successful! Redirecting...";
                } else {
                    $error_message = "Invalid password.";
                    $conn->query("INSERT INTO customer_login_logs (username, email, status) 
            VALUES ('$login_input', '', 'failed')");
                }
            } else {
                $error_message = "Username or email not found.";
                $conn->query("INSERT INTO customer_login_logs (username, email, status) 
            VALUES ('$login_input', '', 'failed')");
            }
            $stmt->close();
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
    <title>Customer Login - Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e9f3d; /* Fresh green */
            --primary-light: #8fd14f;
            --primary-dark: #38761d;
            --secondary: #4e9f3d; /* Changed to match primary green */
            --accent: #ff7e2e; /* Warm orange */
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #f0f2f5;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
            background-color: var(--light-gray);
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url("Register_Page_Background.jpg") no-repeat center center;
            background-size: cover;
            z-index: -1;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.97);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 450px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .login-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .section-title:after {
            content: '';
            display: block;
            height: 4px;
            width: 70px;
            background-color: var(--primary);
            margin-top: 0.5rem;
        }

        .form-label {
            color: var(--dark);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .form-control, .form-select {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.25rem rgba(78, 159, 61, 0.25);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 159, 61, 0.3);
            border-radius: 8px;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 159, 61, 0.4);
        }

        .btn-guest {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(143, 209, 79, 0.3);
            border-radius: 8px;
            color: white;
        }

        .btn-guest:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 159, 61, 0.4);
        }

        #togglePassword {
            position: absolute;
            top: 70%;
            right: 15px;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: var(--gray);
            cursor: pointer;
        }

        #togglePassword:hover i {
            color: var(--primary);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px;
            margin-bottom: 25px;
        }

        .alert-danger {
            background-color: #ff3333;
            border-color: #e62e2e;
            color: white;
        }

        .alert-success {
            background-color: var(--primary);
            border-color: var(--primary-dark);
            color: white;
        }

        .text-center a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .text-center a:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .paw-print {
            position: absolute;
            width: 80px;
            height: 80px;
            background-image: url('cat_paw.png');
            background-size: contain;
            opacity: 0.05;
            z-index: 0;
        }

        .paw-top-right {
            top: 10px;
            right: 10px;
            transform: rotate(45deg);
        }

        .paw-bottom-left {
            bottom: 10px;
            left: 10px;
            transform: rotate(-45deg);
        }

        .login-banner {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-banner img {
            max-height: 100px;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .login-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .login-links a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-links a:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

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
            background-color: rgba(78, 159, 61, 0.7);
            border: 2px solid var(--primary);
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
            border: 2px dashed var(--primary);
            border-radius: 5px;
            top: 50%;
            right: 50px;
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
            background-color: var(--primary);
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

        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <!-- Paw print decorations -->
        <div class="paw-print paw-top-right"></div>
        <div class="paw-print paw-bottom-left"></div>
        
        <div class="login-banner">
            <h2 class="section-title">Customer Login</h2>
        </div>
        
        <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>
        <div class="alert alert-success" id="successAlert" style="display: none;"></div>
        
        <form method="POST" action="" id="loginForm">
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-person me-2" style="color: var(--primary);"></i>
                    Username or Email
                </label>
                <input type="text" name="login_input" class="form-control" required placeholder="Enter your username or email">
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

            <button type="button" id="guestBtn" class="btn btn-guest w-100 mt-3">
                <i class="bi bi-person me-2"></i>
                Continue as Guest
            </button>

            <div class="login-links">
                <p class="mb-0 text-center">New pet parent? <a href="register.php">Register here</a></p>
                <a href="forgot_password.php">
                    <i class="bi bi-question-circle"></i>
                    Forgot Password?
                </a>
            </div>
        </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

// Guest button functionality
document.getElementById('guestBtn').addEventListener('click', function() {
    window.location.href = 'userhomepage.php?guest=true';
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
</body>
</html>