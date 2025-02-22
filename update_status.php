<?php
include("dbconfig.php");

if (isset($_GET['reg_id']) && isset($_GET['status'])) {
    $reg_id = $_GET['reg_id'];
    $status = $_GET['status'];

    // Update the user status
    $query = "UPDATE table_reg SET status = '$status' WHERE reg_id = $reg_id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('User status updated successfully!'); window.location.href = 'admindash.php';</script>";
    } else {
        echo "<script>alert('Error updating user status!'); window.location.href = 'admindash.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href = 'admindash.php';</script>";
}
?>
