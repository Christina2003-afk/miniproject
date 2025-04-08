<?php
// Include your database connection file
include('dbconfig.php'); // Replace with the actual path to your connection file

// Query to fetch bid history - removed items table join
$sql = "SELECT * FROM bid_history ORDER BY bid_time DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bid History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
        }
        .bid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bid-table th, .bid-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .bid-table th {
            background-color: #f2f2f2;
        }
        .bid-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .bid-table tr:hover {
            background-color: #eaeaea;
        }
        .no-bids {
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Bid History</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="bid-table">
            <thead>
                <tr>
                    <th>History ID</th>
                    <th>Bid ID</th>
                    <th>Bidder Email</th>
                    <th>Bid Amount</th>
                    <th>Bid Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['history_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['bid_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['bidder_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['bid_amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['bid_time']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-bids">No bids found.</p>
    <?php endif; ?>
    
    <p><a href="index.php">Back to Home</a></p>
</body>
</html>