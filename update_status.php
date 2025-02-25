<?php
include("dbconfig.php");

if (isset($_GET['reg_id']) && isset($_GET['status']))
    $seller_id = intval($_GET['reg_id']);
    $action = $_GET['status'];

    if ($action == "Active") {
        $new_status = "approved";
    } elseif ($action == "reject") {
        $new_status = "rejected";
    } else {
        die("Invalid action");
    }

    // Use a prepared statement for security
    $update_query = "UPDATE seller_registration SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $seller_id);

    if (mysqli_stmt_execute($stmt)) {
        if ($action == "Active") {
            // Fetch seller details
            $seller_query = "SELECT full_name, email FROM seller_registration WHERE id = ?";
            $stmt2 = mysqli_prepare($conn, $seller_query);
            mysqli_stmt_bind_param($stmt2, "i", $seller_id);
            mysqli_stmt_execute($stmt2);
            $result = mysqli_stmt_get_result($stmt2);

            if ($seller = mysqli_fetch_assoc($result)) {
                $name = $seller['full_name'];
                $email = $seller['email'];
                $default_password = password_hash("seller123", PASSWORD_DEFAULT);

                // Insert seller into users table
                $insert_user = "INSERT INTO table_reg (name, email, password, role) VALUES (?, ?, ?, 'seller')";
                $stmt3 = mysqli_prepare($conn, $insert_user);
                mysqli_stmt_bind_param($stmt3, "sss", $name, $email, $default_password);

                if (mysqli_stmt_execute($stmt3)) {
                    echo "success";
                } else {
                    echo "Error inserting user: " . mysqli_error($conn);
                }
            } else {
                echo "Error: Seller not found.";
            }
        } else {
            echo "success";
        }
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }

    // Close statements
    mysqli_stmt_close($stmt);
    if (isset($stmt2)) {
        mysqli_stmt_close($stmt2);
    }
    if (isset($stmt3)) {
        mysqli_stmt_close($stmt3);
    }

?>
