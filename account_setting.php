<?php
session_start();

// 如果用户未登录，则重定向到登录页面
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// 数据库连接信息
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "petshop";

// 创建数据库连接
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Failed to connect to database: " . $conn->connect_error);
}

// 从 session 中获取当前用户 ID
$customer_id = $_SESSION['customer_id'];

// 使用预处理语句获取当前用户信息
$sql = "SELECT Customer_id, Customer_name, Customer_email FROM customer WHERE Customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// 如果没有查询到用户记录，则重定向登出
if ($result->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

// 将用户信息存入数组
$customer = $result->fetch_assoc();

// 处理表单提交更新用户名和邮箱
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name  = trim($_POST['Customer_name']);
    $new_email = trim($_POST['Customer_email']);

    // 更新数据库中对应的信息
    $update_sql = "UPDATE customer SET Customer_name = ?, Customer_email = ? WHERE Customer_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ssi", $new_name, $new_email, $customer_id);

    if ($stmt_update->execute()) {
        $_SESSION['customer_name'] = $new_name;
        $success = "Your account information has been updated successfully!";
        
        // 重新获取最新用户信息
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
    } else {
        $error = "Failed to update. Please try again later.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Settings</title>
  <link rel="stylesheet" href="account_setting.css">
</head>
<body>

<div class="edit-profile-container">
  <h2>Account Settings</h2>
  
  <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>
  
  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="post" action="account_setting.php" class="edit-profile-form">
    <label for="Customer_name">Username</label>
    <input 
      type="text" 
      id="Customer_name" 
      name="Customer_name" 
      value="<?php echo htmlspecialchars($customer['Customer_name']); ?>" 
      required
    >

    <label for="Customer_email">Email</label>
    <input 
      type="email" 
      id="Customer_email" 
      name="Customer_email" 
      value="<?php echo htmlspecialchars($customer['Customer_email']); ?>" 
      required
    >

    <button type="submit">Save Changes</button>
    <a href="userhomepage.php" class="back-button">Back to Home</a>
  </form>
</div>

</body>
</html>
