<?php
include('db_connection.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    
    $query = "SELECT COUNT(*) as count FROM (
                SELECT Staff_Email as email FROM Staff WHERE Staff_Email = ?
                UNION ALL
                SELECT Customer_email as email FROM Customer WHERE Customer_email = ?
            ) as combined";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode(['exists' => $row['count'] > 0]);
    exit();
}

echo json_encode(['exists' => false]);
?>