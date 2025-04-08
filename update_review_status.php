<?php
require_once 'dbconfig.php';

// Start session
session_start();



// Get user's role

$role_query = "SELECT role FROM table_reg WHERE reg_id = ?";
$stmt = mysqli_prepare($conn, $role_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$role_result = mysqli_stmt_get_result($stmt);



// Process review status update
if(isset($_GET['id']) && isset($_GET['action'])) {
    $review_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Prepare the appropriate query based on the action
    if($action == 'approve') {
        $query = "UPDATE reviews SET approved = 1 WHERE id = ?";
    } elseif($action == 'reject') {
        $query = "UPDATE reviews SET approved = 0 WHERE id = ?";
    } elseif($action == 'delete') {
        $query = "DELETE FROM reviews WHERE id = ?";
    } else {
        // Invalid action, continue to display the reviews
    }
    
    // Execute the query if one was set
    if(isset($query)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $review_id);
        $result = mysqli_stmt_execute($stmt);
        
        if(!$result) {
            $error_message = "Error: " . mysqli_error($conn);
        } else {
            $success_message = "Review successfully updated!";
        }
    }
}

// Fetch all reviews for display
$reviews_query = "SELECT r.*, p.product_name 
                FROM reviews r
                LEFT JOIN products p ON r.product_id = p.product_id
                ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_query);

if (!$reviews_result) {
    $error_message = "Error fetching reviews: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: rgb(159, 108, 45);
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .active {
            background-color: #e8f5e9;
            color: #2ecc71;
        }

        .inactive {
            background-color: #ffebee;
            color: #e74c3c;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .approve-btn, .reject-btn, .delete-btn {
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .approve-btn {
            background-color: #e8f5e9;
            color: #2ecc71;
        }

        .reject-btn {
            background-color: #ffebee;
            color: #e74c3c;
        }

        .delete-btn {
            background-color: #e53935;
            color: white;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: rgb(159, 108, 45);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .star-rating {
            color: #FFD700;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Review Management</h1>
        
        <?php if(isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($reviews_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($reviews_result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['product_name'] ?? 'Unknown Product'); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name'] ?? $row['user_email'] ?? 'Anonymous'); ?></td>
                        <td>
                            <div class="star-rating">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $row['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars(substr($row['review_text'], 0, 100))); ?>
                            <?php echo (strlen($row['review_text']) > 100) ? '...' : ''; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                           
                        </td>
                        
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No reviews found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="admindash.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html> 