<?php
include 'dbconfig.php';

if (isset($_POST['seller_id']) && isset($_POST['action'])) {
    $seller_id = intval($_POST['seller_id']);
    $action = $_POST['status'];

    if ($action == "Active") {
        $new_status = "approved";
    } elseif ($action == "reject") {
        $new_status = "rejected";
    } else {
        die("Invalid action");
    }

    // Update seller status
    $update_query = "UPDATE seller_registration SET status = 'Approved' WHERE id = $seller_id";
    if (mysqli_query($conn, $update_query)) {

        // If approved, insert seller into users table for login
        if ($action == "Active") {
            // Fetch seller details
            $seller_query = "SELECT email FROM seller_registration WHERE id = $seller_id";
            $result = mysqli_query($conn, $seller_query);
            $seller = mysqli_fetch_assoc($result);
            $email = $seller['email'];
            $default_password = password_hash("seller123", PASSWORD_DEFAULT); // Set a default password

            // Insert into users table (if you have one)
            $insert_user = "INSERT INTO table_reg (email, password, role) VALUES ('$email', '$default_password', 'seller')";
            if (mysqli_query($conn, $insert_user)) {
                echo "success";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }

        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
