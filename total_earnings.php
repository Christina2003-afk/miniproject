<?php
session_start();
include("dbconfig.php");

// Check if user is logged in and is a seller
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['email'];

// Get seller information
$query = "SELECT * FROM table_reg WHERE email = '$email' AND role = 'seller'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: login.html");
    exit();
}

$seller = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Earnings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(177, 144, 53);
            --secondary-color: rgb(159, 108, 45);
            --accent-color: rgb(228, 205, 77);
            --dark-text: rgb(241, 241, 238);
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .earnings-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-title {
            color: var(--secondary-color);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
        }

        .earnings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 25px;
            padding: 25px;
        }

        .earnings-header {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .back-button {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
        }

        .earnings-details {
            margin-top: 30px;
        }

        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .table tbody tr:hover {
            background-color: rgba(228, 205, 77, 0.1);
        }

        .earnings-breakdown {
            background: linear-gradient(135deg, #fff, #fff8f3);
        }

        .breakdown-container {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(230, 126, 34, 0.05);
            border-radius: 10px;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(230, 126, 34, 0.1);
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            color: var(--text-color);
            font-weight: 500;
        }

        .breakdown-value {
            font-weight: 600;
            color: #E67E22;
        }

        .admin-share {
            color: #d35400;
        }

        .seller-share {
            color: #27AE60;
        }

        .earnings-card .total-amount {
            position: relative;
            padding-bottom: 0.5rem;
        }

        .earnings-card .total-amount::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #E67E22;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .earnings-card {
                margin-bottom: 1rem;
            }
            
            .breakdown-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="earnings-container">
        <a href="sellerdashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <h1 class="page-title">Total Earnings</h1>

        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="earnings-card">
                        <h2 class="earnings-header">Total Revenue</h2>
                        <?php
                        $earnings_query = "SELECT SUM(amount) as total FROM orders WHERE status = 'paid'";
                        $earnings_result = mysqli_query($conn, $earnings_query);
                        $earnings = mysqli_fetch_assoc($earnings_result);
                        $total_earnings = $earnings['total'] ?? 0;
                        $admin_share = $total_earnings * 0.25; // Calculate 25% admin share
                        $seller_share = $total_earnings * 0.75; // Calculate 75% seller share
                        ?>
                        <div class="total-amount">₹<?php echo number_format($seller_share, 2); ?></div>
                        <div class="trend-indicator">
                            <i class="fas fa-info-circle"></i>
                            <span>Your share (75% of total revenue)</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="earnings-card">
                        <h2 class="earnings-header">Admin Share (25%)</h2>
                        <div class="total-amount admin-share">₹<?php echo number_format($admin_share, 2); ?></div>
                        <div class="trend-indicator">
                            <i class="fas fa-info-circle"></i>
                            <span>Platform fee (25% of total revenue)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add a new card for total earnings breakdown -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="earnings-card earnings-breakdown">
                        <h2 class="earnings-header">Revenue Breakdown</h2>
                        <div class="breakdown-container">
                            <div class="breakdown-item">
                                <span class="breakdown-label">Total Revenue:</span>
                                <span class="breakdown-value">₹<?php echo number_format($total_earnings, 2); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Your Share (75%):</span>
                                <span class="breakdown-value seller-share">₹<?php echo number_format($seller_share, 2); ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Admin Share (25%):</span>
                                <span class="breakdown-value admin-share">₹<?php echo number_format($admin_share, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="earnings-details">
                <h3 class="mb-4">Recent Transactions</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch recent orders
                            $orders_query = "SELECT * FROM orders WHERE status = 'paid' ORDER BY created_at DESC LIMIT 10";
                            $orders_result = mysqli_query($conn, $orders_query);

                            while ($order = mysqli_fetch_assoc($orders_result)) {
                                echo "<tr>";
                                echo "<td>" . $order['id'] . "</td>";
                                echo "<td>" . date('M d, Y', strtotime($order['created_at'])) . "</td>";
                                
                                echo "<td>₹" . number_format($order['amount'], 2) . "</td>";
                                echo "<td><span class='badge badge-success'>Paid</span></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?> 