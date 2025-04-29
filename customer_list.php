<?php
session_start();

// Check if admin is logged in - using the correct session variable from login.php
if (!isset($_SESSION['admin_logged_in']) {
    header("Location: login.php?redirect=customer_list.php");
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

// Fetch all customers
$sql = "SELECT * FROM customer";
$result = $conn->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .action-btns .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container-fluid">
    <div class="row">
        <?php include('admin_sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-users me-2"></i>Customer List</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table me-2"></i>Registered Customers
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['Customer_id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Customer_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Customer_email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Customer_phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Customer_address'] ?? ''); ?></td>
                                    <td class="action-btns">
                                        <a href="view_customer.php?id=<?php echo $customer['Customer_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $customer['Customer_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(customerId) {
    if (confirm('Are you sure you want to delete this customer?')) {
        window.location.href = 'delete_customer.php?id=' + customerId;
    }
}
</script>
</body>
</html>