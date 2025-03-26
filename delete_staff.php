<?php
include('db_connection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM Staff WHERE Staff_ID = $id";

    if ($conn->query($sql) === TRUE) {
        $conn->query("SET @count = 0;");
        $conn->query("UPDATE Staff SET Staff_ID = @count:= @count + 1;");
        $conn->query("ALTER TABLE Staff AUTO_INCREMENT = 1;");
        header("Location: manage_staff.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
