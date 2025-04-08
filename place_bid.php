<?php
session_start();
require_once 'dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bid_id']) && isset($_POST['bid_amount'])) {
    $bid_id = $_POST['bid_id'];
    $bid_amount = $_POST['bid_amount'];
    $current_bid = $_POST['current_bid'];

    // Check if bid amount is a valid increment of 50
    if (($bid_amount - $current_bid) % 50 != 0 || $bid_amount <= $current_bid) {
        $_SESSION['error'] = "Invalid bid amount. You must bid at least 50 more than the current amount.";
        header("Location: bidding.php");
        exit();
    }

    // Store the new bid
    // FIXED: The SQL query now has 4 placeholders to match the 4 columns
    $insert_query = "INSERT INTO bid_history (history_id, bid_id, bidder_email, bid_amount, bid_time) VALUES (NULL, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    
    // FIXED: Now using bidder_email from session
    $bidder_email = $_SESSION['email']; // Assuming email is stored in session
    
    // FIXED: Using "sid" for the bind_param types (string, integer, double)
    $stmt->bind_param("isd", $bid_id, $bidder_email, $bid_amount);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Bid placed successfully!";
    } else {
        $_SESSION['error'] = "Error placing bid: " . $conn->error;
    }
    $stmt->close();
}

header("Location: bidding.php");
exit();
?>