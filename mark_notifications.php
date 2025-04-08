<?php
session_start();
require_once 'dbconfig.php';

if (!isset($_SESSION['seller_email'])) {
    header("Location: seller_login.php");
    exit();
}

$seller_email = $_SESSION['seller_email'];

// Mark all notifications as read
$query = "UPDATE seller_notifications SET is_read = 1 WHERE seller_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $seller_email);
$stmt->execute();

// Redirect back to the previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?> 