<?php
include('db_connection.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "UPDATE Staff SET status = 'Inactive' WHERE Staff_ID = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: manage_staff.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
