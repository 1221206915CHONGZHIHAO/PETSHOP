<?php
session_start();
include 'db_connection.php'; // Assuming this establishes the database connection

// Fetch shop settings from database
$shopSettings = [];
$settingsQuery = $conn->prepare("SELECT * FROM shop_settings WHERE id = 1");
$settingsQuery->execute();
$result = $settingsQuery->get_result();

if ($result->num_rows > 0) {
    $shopSettings = $result->fetch_assoc();
}

// PHPMailer components
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$message = '';
$messageType = '';
$name = $email = $userMessage = '';

// Check if there's a flash message from a previous submission
if(isset($_SESSION['contact_message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['contact_message'];
    $messageType = $_SESSION['message_type'];
    
    // Clear the flash message after displaying it
    unset($_SESSION['contact_message']);
    unset($_SESSION['message_type']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $userMessage = $_POST['message'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($userMessage)) {
        $_SESSION['contact_message'] = "Please fill all the fields";
        $_SESSION['message_type'] = "danger";
    } else {
        // Fetch staff email for notification (sending to all staff members with 'Active' status)
        $stmt = $conn->prepare("SELECT Staff_Email FROM staff WHERE status = 'Active'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'zheya1810@gmail.com'; // Use the same email from forgot password
                $mail->Password = 'rbzs duxv qmho ywlv'; // Use the same App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
            
                $mail->setFrom($email, $name);
                $mail->addReplyTo($email, $name);
                
                // Add all active staff members as recipients
                while ($row = $result->fetch_assoc()) {
                    $mail->addAddress($row['Staff_Email']);
                }
            
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'New Contact Form Submission from Hachi Pet Shop';
                
                // Create email body
                $emailBody = "
                    <h2>New Contact Form Submission</h2>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Message:</strong></p>
                    <p>{$userMessage}</p>
                    <hr>
                    <p>This email was sent from the contact form on Hachi Pet Shop website.</p>
                ";
                
                $mail->Body = $emailBody;
                $mail->AltBody = "Name: {$name}\nEmail: {$email}\nMessage: {$userMessage}";
                
                // For debugging - set to 0 for production
                $mail->SMTPDebug = 0;
                
                $mail->send();
                $_SESSION['contact_message'] = "Thank you for your message! We'll get back to you soon.";
                $_SESSION['message_type'] = "success";
                
                // Clear form data after successful submission
                $name = $email = $userMessage = "";
            } catch (Exception $e) {
                $_SESSION['contact_message'] = "Message could not be sent. Error: " . $mail->ErrorInfo;
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['contact_message'] = "Unable to process your request at this time. Please try again later.";
            $_SESSION['message_type'] = "danger";
        }
    }
    
    // Redirect back to the contact page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hachi Pet Shop - Contact Us</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Your Custom Styles -->
    <link rel="stylesheet" href="userhomepage.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg custom-nav fixed-top">
        <div class="container">
            <!-- Brand on the left -->
            <a class="navbar-brand" href="userhomepage.php">
                <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
            </a>
            
            <!-- Toggler for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main nav links centered -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="userhomepage.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact_us.php">Contact Us</a></li>
                </ul>

                <!-- Icons on the right -->
                <ul class="navbar-nav ms-auto nav-icons">
                    <!-- Search Icon with Dropdown - Modified to redirect to products.php -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-search"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end search-dropdown" aria-labelledby="searchDropdown">
                            <form class="d-flex search-form" action="products.php" method="GET">
                                <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search" required>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                            </form>
                        </ul>
                    </li>

                    <!-- Cart Icon with item count -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart"></i>
                            <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                    <?php echo htmlspecialchars($_SESSION['cart_count']); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User Icon with Dynamic Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if(isset($_SESSION['customer_id'])): ?>
                                <span class="me-1"><?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                            <?php else: ?>
                                <i class="bi bi-person"></i>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if(isset($_SESSION['customer_id'])): ?>
                                <li><a class="dropdown-item" href="user_dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="my_orders.php"><i class="bi bi-box me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="favorites.php"><i class="bi bi-heart me-2"></i>My Favorites</a></li>
                                <li><a class="dropdown-item" href="myprofile_address.php"><i class="bi bi-person-lines-fill me-2"></i>My Profile/Address</a></li>
                                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="logout.php?type=customer">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                  </a>
                </li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                                <li><a class="dropdown-item" href="register.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Us Section -->
    <div class="contact-container">
        <!-- Left Section: Contact Form -->
        <div class="contact-form">
            <h1>Get in touch!</h1>
            <div class="underline"></div>
            
            <?php if(!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="text" name="name" placeholder="ENTER YOUR NAME" required 
                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                       
                <input type="email" name="email" placeholder="ENTER A VALID EMAIL ADDRESS" required
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                       
                <textarea name="message" placeholder="ENTER YOUR MESSAGE" required><?php echo isset($userMessage) ? htmlspecialchars($userMessage) : ''; ?></textarea>
                
                <button type="submit">SUBMIT</button>
            </form>
        </div>

        <!-- Right Section: Contact Details and Map -->
        <div class="contact-details">
            <div class="info">
                <h3>ADDRESS</h3>
                <p><?php echo !empty($shopSettings['address']) ? htmlspecialchars($shopSettings['address']) : 'Address not available'; ?></p>

                <h3>CALL US</h3>
                <p><?php echo !empty($shopSettings['phone_number']) ? htmlspecialchars($shopSettings['phone_number']) : 'Phone number not available'; ?></p>

                <h3>OPENING HOURS</h3>
                <p><?php echo !empty($shopSettings['opening_hours']) ? htmlspecialchars($shopSettings['opening_hours']) : 'Opening hours not available'; ?></p>
            </div>
            <div class="map">
                <!-- Embed a Google Map (you can replace the iframe src with your location) -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d996.7225547848768!2d102.24827109081896!3d2.1952332685660716!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1szh-CN!2smy!4v1746430278172!5m2!1szh-CN!2smy" width="600" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <!-- Footer About -->
                <div class="col-md-5 mb-4 mb-lg-0">
                    <div class="footer-about">
                        <div class="footer-logo">
                            <img src="Hachi_Logo.png" alt="Hachi Pet Shop">
                        </div>
                        <p>Your trusted partner in pet product. We're dedicated to providing quality products for pet lovers everywhere.</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/profile.php?id=61575717095389"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.instagram.com/smal.l7018/"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-7">
                    <h4 class="footer-title">Contact Us</h4>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo !empty($shopSettings['address']) ? htmlspecialchars($shopSettings['address']) : 'Address not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-telephone"></i>
                                <span><?php echo !empty($shopSettings['phone_number']) ? htmlspecialchars($shopSettings['phone_number']) : 'Phone number not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-envelope"></i>
                                <span><?php echo !empty($shopSettings['contact_email']) ? htmlspecialchars($shopSettings['contact_email']) : 'Email not available'; ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-clock"></i>
                                <span><?php echo !empty($shopSettings['opening_hours']) ? htmlspecialchars($shopSettings['opening_hours']) : 'Opening hours not available'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-md-0">© 2025 Hachi Pet Shop. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JavaScript -->
    <script>
        // Initialize AOS Animation
        AOS.init({
            once: true,
            duration: 800,
            offset: 100
        });

        // Navbar Scroll Effect
        const navbar = document.querySelector('.custom-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });
    </script>
</body>
</html>