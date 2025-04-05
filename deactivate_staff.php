<?php
include('db_connection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Update status to 'Inactive' instead of deleting
    $sql = "UPDATE Staff SET status = 'Inactive' WHERE Staff_ID = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: manage_staff.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>