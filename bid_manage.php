<?php

session_start();
require_once 'dbconfig.php';

// Fetch all bids
$query = "SELECT * FROM bid";
$result = $conn->query($query);

if (!$result) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bid Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(159, 108, 45);
            --secondary-color: rgb(135, 90, 30);
            --accent-color: rgb(184, 133, 70);
            --danger-color: #c23616;
            --warning-color: #e1b382;
            --dark-color: rgb(100, 65, 23);
            --light-color: #f5f0e6;
            --border-color: #d7c0aa;
            --background-color: #f9f5f0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: #3E2723;
            line-height: 1.6;
        }
        
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 12px;
            border-radius: 4px;
        }
        
        .nav-links a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .page-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
            color: var(--primary-color);
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(159, 108, 45, 0.15);
            padding: 25px;
            margin-bottom: 30px;
            overflow: hidden;
            border-top: 4px solid var(--primary-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        tr:nth-child(even) {
            background-color: rgba(159, 108, 45, 0.05);
        }
        
        tr:hover {
            background-color: rgba(159, 108, 45, 0.1);
        }
        
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid var(--border-color);
        }
        
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-weight: 500;
            margin-right: 5px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .accepted { 
            background-color: #6B8E23; 
        }
        
        .deleted { 
            background-color: var(--danger-color); 
        }
        
        .pending { 
            background-color: var(--warning-color);
            color: var(--dark-color); 
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-accepted {
            background-color: rgba(107, 142, 35, 0.15);
            color: #6B8E23;
        }
        
        .status-pending {
            background-color: rgba(225, 179, 130, 0.2);
            color: rgb(159, 108, 45);
        }
        
        .status-deleted {
            background-color: rgba(194, 54, 22, 0.15);
            color: var(--danger-color);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        @media (max-width: 992px) {
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            .action-btn {
                padding: 6px 10px;
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <h1><i class="fas fa-gavel"></i> Auction System</h1>
            </div>
            <nav class="nav-links">
                <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2 class="page-title"><i class="fas fa-gavel"></i> Bid Management</h2>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Description</th>
                            <th>Size</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Starting Amount</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['product_description']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_size']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_datetime']); ?></td>
                                <td><?php echo htmlspecialchars($row['end_datetime']); ?></td>
                                <td><strong>$<?php echo htmlspecialchars($row['starting_amount']); ?></strong></td>
                                <td><img class="product-image" src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image"></td>
                                <td>
                                    <?php 
                                    $status = strtolower($row['action']);
                                    $statusClass = 'status-' . $status;
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($row['action']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn accepted" onclick="updateAction(<?php echo $row['bid_id']; ?>, 'accepted')">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                    <button class="action-btn deleted" onclick="updateAction(<?php echo $row['bid_id']; ?>, 'deleted')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Auction System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        function updateAction(bidId, action) {
            if (confirm("Are you sure you want to change the action?")) {
                window.location.href = "update_bid_action.php?bid_id=" + bidId + "&action=" + action;
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>