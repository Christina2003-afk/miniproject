<?php
session_start();
include("dbconfig.php");

// Fetch only the first 6 orders
$query = "SELECT id, product_id, payment_id, amount, status, created_at 
          FROM orders 
          ORDER BY created_at DESC 
          LIMIT 6";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Orders</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
            padding: 20px;
        }

        .dashboard-title {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            padding: 10px;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .order-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-body {
            padding: 20px;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.95em;
        }

        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }

        .order-amount {
            color: #2ecc71;
            font-weight: 600;
        }

        .order-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
            border-radius: 0 0 12px 12px;
            display: flex;
            gap: 10px;
        }

        .order-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.2s ease;
        }

        .action-view {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .action-edit {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .action-delete {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .action-view:hover { background-color: #bbdefb; }
        .action-edit:hover { background-color: #ffe0b2; }
        .action-delete:hover { background-color: #ffcdd2; }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #adb5bd;
        }

        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .order-card {
                margin: 0 auto;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>
    <h1 class="dashboard-title">
        <i class="fas fa-shopping-bag"></i>
        Recent Orders
    </h1>

    <div class="orders-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $status = strtolower($row['status']);
                $statusClass = 'status-' . $status;
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo htmlspecialchars($row['id']); ?></div>
                        <span class="order-status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>
                    <div class="order-body">
                        <div class="order-detail">
                            <span class="detail-label">Product ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['product_id']); ?></span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Payment ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['payment_id']); ?></span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value">
                                <?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                            </span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value order-amount">₹<?php echo htmlspecialchars($row['amount']); ?></span>
                        </div>
                    </div>
                    <div class="order-footer">
                        <button class="order-action action-view" onclick="viewOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="order-action action-edit" onclick="editOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="order-action action-delete" onclick="deleteOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h2>No Orders Yet</h2>
                <p>When you receive orders, they will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewOrder(id) {
            // Add your view order logic here
            console.log('Viewing order:', id);
        }

        function editOrder(id) {
            // Add your edit order logic here
            console.log('Editing order:', id);
        }

        function deleteOrder(id) {
            if (confirm('Are you sure you want to delete this order?')) {
                // Add your delete order logic here
                console.log('Deleting order:', id);
            }
        }
    </script>
</body>
</html>
