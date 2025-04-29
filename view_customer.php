<?php
session_start();
// Restrict access to admin only
if (!isset($_SESSION['admin_loggedin'])) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$database = "petshop";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$customer = [];
if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    $sql = "SELECT * FROM customer WHERE Customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container-fluid">
    <div class="row">
        <?php include('admin_sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user me-2"></i>Customer Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="customer_list.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            <?php if (empty($customer)): ?>
                <div class="alert alert-danger">Customer not found</div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        Customer Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Basic Information</h5>
                                <p><strong>ID:</strong> <?php echo htmlspecialchars($customer['Customer_id'] ?? ''); ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['Customer_name'] ?? ''); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['Customer_email'] ?? ''); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['Customer_phone'] ?? ''); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['Customer_address'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>