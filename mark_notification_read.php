<?php
session_start();
require_once 'dbconfig.php';

// Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Check if notification_id is set
if (isset($_GET['notification_id'])) {
    $notification_id = $_GET['notification_id'];
    
    // Verify the notification belongs to this seller and mark it as read
    $update_query = "UPDATE seller_notifications SET is_read = 1 
                    WHERE notification_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $notification_id, $seller_id);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating notification: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No notification ID provided']);
}

$conn->close();
?>