<?php
session_start();
require_once 'dbconfig.php';

// Check if bid_id and action are set
if (isset($_GET['bid_id']) && isset($_GET['action'])) {
    $bid_id = $_GET['bid_id'];
    $action = $_GET['action'];
    
    // Update the bid action
    $update_query = "UPDATE bid SET action = ? WHERE bid_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $action, $bid_id);
    $result = $stmt->execute();
    
    if ($result) {
        // If the action is 'accepted', create a notification for the seller
        if ($action == 'accepted') {
            // Get seller_email and product details from the bid table
            $bid_query = "SELECT seller_email, product_name FROM bid WHERE bid_id = ?";
            $bid_stmt = $conn->prepare($bid_query);
            $bid_stmt->bind_param("i", $bid_id);
            $bid_stmt->execute();
            $bid_result = $bid_stmt->get_result();
            
            if ($bid_result->num_rows > 0) {
                $bid_row = $bid_result->fetch_assoc();
                $seller_email = $bid_row['seller_email'];
                $product_name = $bid_row['product_name'];
                
                // Look up the seller_id from seller_registration table
                $seller_query = "SELECT id FROM seller_registration WHERE email = ?";
                $seller_stmt = $conn->prepare($seller_query);
                $seller_stmt->bind_param("s", $seller_email);
                $seller_stmt->execute();
                $seller_result = $seller_stmt->get_result();
                
                if ($seller_result->num_rows > 0) {
                    $seller_row = $seller_result->fetch_assoc();
                    $seller_id = $seller_row['id'];
                    
                    // Create a notification entry in the notifications table
                    $notification_message = "Your bid for product \"$product_name\" has been accepted!";
                    
                    $notification_query = "INSERT INTO seller_notifications (seller_id, seller_email, message, bid_id, is_read, created_at) 
                                          VALUES (?, ?, ?, ?, 0, NOW())";
                    
                    $notification_stmt = $conn->prepare($notification_query);
                    $notification_stmt->bind_param("issi", $seller_id, $seller_email, $notification_message, $bid_id);
                    $notification_result = $notification_stmt->execute();
                    
                    if ($notification_result) {
                        $_SESSION['message'] = "Bid accepted and notification created for seller.";
                    } else {
                        $_SESSION['message'] = "Bid accepted but failed to create notification: " . $conn->error;
                    }
                    
                    $notification_stmt->close();
                } else {
                    $_SESSION['message'] = "Bid accepted but couldn't find seller ID for email: " . $seller_email;
                }
                
                $seller_stmt->close();
            } else {
                $_SESSION['message'] = "Bid accepted but couldn't find seller information.";
            }
            
            $bid_stmt->close();
        } else {
            $_SESSION['message'] = "Bid status updated to: " . $action;
        }
    } else {
        $_SESSION['message'] = "Error updating bid status: " . $conn->error;
    }
    
    $stmt->close();
    
    // Redirect back to the bid management page
    header("Location: bid_manage.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request";
    header("Location: bid_manage.php");
    exit();
}

$conn->close();
?>