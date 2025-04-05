<?php
session_start();


$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "petshop";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    
    $sql = "SELECT * FROM customer WHERE customer_name='$username' AND customer_password='$password'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['customer_id'] = $row['customer_id'];
        $_SESSION['customer_name'] = $row['customer_name'];
        header("Location: userhomepage.php");
        exit();
    } else {
        $login_error = "用户名或密码错误";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pet Shop - Home</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="products.css">
</head>
<body>
  <div class="container my-4">
    <h1 class="mb-4">Products</h1>
    <div class="row">
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <img src="<?php echo $row['product_image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($row['product_name']); ?></h5>
              <p class="card-text">$<?php echo number_format($row['product_price'], 2); ?></p>
              <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
