<?php
session_start();
include 'db_connection.php'; // Assuming this establishes the database connection

// PHPMailer components
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $userMessage = $_POST['message'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($userMessage)) {
        $message = "Please fill all the fields";
        $messageType = "danger";
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
                $message = "Thank you for your message! We'll get back to you soon.";
                $messageType = "success";
                
                // Clear form data after successful submission
                $name = $email = $userMessage = "";
            } catch (Exception $e) {
                $message = "Message could not be sent. Error: " . $mail->ErrorInfo;
                $messageType = "danger";
            }
        } else {
            $message = "Unable to process your request at this time. Please try again later.";
            $messageType = "danger";
        }
    }
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

    <style>
        :root {
            --primary: #4e9f3d; /* Fresh green */
            --primary-light: #8fd14f;
            --primary-dark: #38761d;
            --secondary: #1e3a8a; /* Deep navy blue */
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
            overflow-x: hidden;
            padding-top: 114px; /* Adjusted for fixed navbar */
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        /* Navigation */
        .custom-nav {
            background-color: rgb(0, 0, 0);
            box-shadow: 0 2px 15px rgba(255, 255, 255, 0.205);
            padding: 12px 0;
            transition: all 0.3s ease;
        }

        .navbar-scrolled {
            padding: 8px 0;
        }

        .custom-nav .navbar-brand img {
            height: 90px;
            transition: all 0.3s ease;
        }

        .custom-nav .nav-link {
            color: var(--light);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .custom-nav .nav-link:hover,
        .custom-nav .nav-link.active {
            color: var(--primary);
        }

        .custom-nav .nav-link.active:after {
            content: '';
            position: absolute;
            width: 60%;
            height: 2px;
            background-color: var(--primary);
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
        }

        .nav-icons .nav-link {
            color: var(--light);
            padding: 0.5rem;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .nav-icons .nav-link:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 159, 61, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 159, 61, 0.4);
        }

        /* Contact Section */
        .contact-container {
            display: flex;
            max-width: 1000px;
            margin: 50px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .contact-form {
            background-color: #1a1a1a;
            color: white;
            padding: 40px;
            width: 50%;
        }

        .contact-form h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .underline {
            width: 50px;
            height: 3px;
            background-color: #ff3333;
            margin-bottom: 20px;
        }

        .contact-form form {
            display: flex;
            flex-direction: column;
        }

        .contact-form input,
        .contact-form textarea {
            background-color: #333;
            border: none;
            padding: 15px;
            margin-bottom: 15px;
            color: white;
            font-size: 14px;
        }

        .contact-form input::placeholder,
        .contact-form textarea::placeholder {
            color: #999;
            text-transform: uppercase;
        }

        .contact-form textarea {
            height: 100px;
            resize: none;
        }

        .contact-form button {
            background-color: #ff3333;
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            text-transform: uppercase;
        }

        .contact-form button:hover {
            background-color: #e62e2e;
        }
        
        /* Alert message styling */
        .alert {
            border-radius: 0;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #4e9f3d;
            border-color: #38761d;
            color: white;
        }
        
        .alert-danger {
            background-color: #ff3333;
            border-color: #e62e2e;
            color: white;
        }

        .contact-details {
            background-color: #e6e6e6;
            padding: 40px;
            width: 50%;
        }

        .contact-details .info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .contact-details .info p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            color: #333;
        }

        .contact-details .map {
            margin-top: 20px;
        }

        .contact-details .map iframe {
            width: 100%;
            border-radius: 5px;
        }

        /* Footer Enhancement */
        footer {
            background: linear-gradient(to bottom, rgb(134, 138, 135), rgba(46, 21, 1, 0.69));
            position: relative;
            overflow: hidden;
            color: white;
            padding-top: 70px;
            padding-bottom: 30px;
        }

        footer .container {
            position: relative;
            z-index: 1;
        }

        footer .footer-logo img {
            height: 60px;
            margin-bottom: 20px;
        }

        footer p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
        }

        footer .contact-info {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        footer .contact-info i {
            color: var(--primary);
            font-size: 1.2rem;
            margin-right: 15px;
            margin-top: 5px;
        }

        footer .contact-info span {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.5;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 20px;
        }

        /* Back to Top Button */
        .back-to-top {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            bottom: 30px;
            right: 30px;
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(145deg, var(--primary), var(--primary-dark));
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top i {
            color: white;
            font-size: 1.2rem;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }

        .dropdown-item {
            padding: 8px 20px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(78, 159, 61, 0.1);
            color: var(--primary);
        }

        .dropdown-item i {
            transition: all 0.3s ease;
        }

        .dropdown-item:hover i {
            transform: translateX(3px);
        }
        
        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }
            .contact-form, .contact-details {
                width: 100%;
            }
        }
    </style>
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
                    <!-- Search Icon with Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-search"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="searchDropdown" style="min-width: 300px;">
                            <form class="d-flex">
                                <input class="form-control me-2" type="search" placeholder="Search products..." aria-label="Search">
                                <button class="btn btn-primary" type="submit">Go</button>
                            </form>
                        </ul>
                    </li>

                    <!-- Cart Icon with item count -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart"></i>
                            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                    <?php echo count($_SESSION['cart']); ?>
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
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
                <p>123 Pet Street, Animal City<br>Singapore 123456</p>

                <h3>CALL US</h3>
                <p>+65 1234 5678</p>

                <h3>OPENING HOURS</h3>
                <p>Mon-Fri: 9am-6pm<br>Sat-Sun: 10am-4pm</p>
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
                            <a href="#"><i class="bi bi-facebook"></i></a>
                            <a href="#"><i class="bi bi-instagram"></i></a>
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
                                <span>123 Pet Street, Animal City<br>Singapore 123456</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-telephone"></i>
                                <span>+65 1234 5678</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-envelope"></i>
                                <span>info@hachipetshop.com</span>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="contact-info">
                                <i class="bi bi-clock"></i>
                                <span>Mon-Fri: 9am-6pm<br>Sat-Sun: 10am-4pm</span>
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