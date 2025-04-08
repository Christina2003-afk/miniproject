<?php
session_start();
require_once 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    $_SESSION['error'] = "Please login to place a bid";
    header("Location: login.php?redirect=bidding.php");
    exit();
}

// Check if form was submitted with required fields
if (isset($_POST['bid_id']) && isset($_POST['bid_amount'])) {
    $bid_id = intval($_POST['bid_id']);
    $bid_amount = floatval($_POST['bid_amount']);
    $bidder_email = $_SESSION['email'];
    $bid_time = date("Y-m-d H:i:s");
    
    // Get auction details
    $auction_query = "SELECT start_datetime, end_datetime FROM bid WHERE bid_id = ?";
    $stmt = $conn->prepare($auction_query);
    $stmt->bind_param("i", $bid_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $auction = $result->fetch_assoc();
        $current_time = date("Y-m-d H:i:s");
        
        // Check if auction has started - use a small buffer time to account for page refresh delays
        $start_time = strtotime($auction['start_datetime']);
        $end_time = strtotime($auction['end_datetime']);
        $current = strtotime($current_time);
        
        if ($current >= $start_time) {
            if ($current <= $end_time) {
                // Get the current highest bid
                $highest_bid_query = "SELECT MAX(bid_amount) as highest_bid FROM bid_history WHERE bid_id = ?";
                $stmt = $conn->prepare($highest_bid_query);
                $stmt->bind_param("i", $bid_id);
                $stmt->execute();
                $highest_result = $stmt->get_result();
                $highest_row = $highest_result->fetch_assoc();
                
                // If there's no existing bid, use the starting amount
                $highest_bid = ($highest_row && $highest_row['highest_bid']) ? $highest_row['highest_bid'] : 0;
                
                // Check if bid is high enough (at least 50 more than current highest)
                if ($bid_amount >= ($highest_bid + 50)) {
                    // Insert the new bid
                    $insert_query = "INSERT INTO bid_history (bid_id, bidder_email, bid_amount, bid_time) 
                                   VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("isds", $bid_id, $bidder_email, $bid_amount, $bid_time);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Your bid of ₹$bid_amount has been placed successfully!";
                        header("Location: bidding.php");
                        exit();
                    } else {
                        $_SESSION['error'] = "Error placing bid: " . $conn->error;
                    }
                } else {
                    $_SESSION['error'] = "Your bid must be at least ₹" . ($highest_bid + 50);
                }
            } else {
                $_SESSION['error'] = "Bidding has ended";
            }
        } else {
            // Auction hasn't started yet
            $_SESSION['error'] = "Bidding has not started yet. Please wait until " . date("M d, h:i A", $start_time);
        }
    } else {
        $_SESSION['error'] = "Invalid auction";
    }
} else {
    $_SESSION['error'] = "Invalid bid data";
}

// Redirect back to bidding page with error message if we reach here
header("Location: bidding.php");
exit();
?>