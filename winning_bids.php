<?php
session_start();
require_once 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['email'];

// Get winning bids query with payment status
$query = "SELECT bh.*, 
          (SELECT MAX(bid_amount) FROM bid_history WHERE bid_id = bh.bid_id) as highest_bid,
          (SELECT COUNT(*) FROM payments WHERE history_id = bh.history_id) as payment_exists
          FROM bid_history bh
          WHERE bh.bidder_email = ? 
          AND bh.bid_amount = (
              SELECT MAX(bid_amount) 
              FROM bid_history 
              WHERE bid_id = bh.bid_id
          )
          GROUP BY bh.bid_id
          ORDER BY bh.bid_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Winning Bids | Art Gallery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .page-header {
            background: #EAA636;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .bid-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
            border: none;
        }
        .bid-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .bid-amount {
            font-size: 1.5rem;
            color: #28a745;
            font-weight: bold;
        }
        .bid-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .bid-status {
            background: #e8f5e9;
            color: #28a745;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .paid-status {
            background: #e3f2fd;
            color: #0d6efd;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .back-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background: white;
            color: #495057;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .auction-id {
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
            color: #495057;
        }
        .pay-now-btn {
            background: #28a745;
            color: white;
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
        }
        .pay-now-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .paid-badge {
            background: #0d6efd;
            color: white;
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-trophy mr-2"></i>Your Winning Bids</h1>
                <a href="profile.php" class="btn back-btn">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($result->num_rows > 0) { ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="col-md-6">
                        <div class="bid-card card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="auction-id">
                                        <i class="fas fa-gavel mr-1"></i>
                                        Auction #<?php echo htmlspecialchars($row['bid_id']); ?>
                                    </span>
                                    <span class="<?php echo $row['payment_exists'] > 0 ? 'paid-status' : 'bid-status'; ?>">
                                        <?php if ($row['payment_exists'] > 0): ?>
                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                        <?php else: ?>
                                            <i class="fas fa-crown mr-1"></i>Winning Bid
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="bid-amount mb-3">
                                    ₹<?php echo number_format($row['bid_amount'], 2); ?>
                                </div>
                                <div class="bid-info">
                                    <p class="mb-2">
                                        <i class="far fa-clock mr-2"></i>
                                        Bid placed on <?php echo date('d M Y, h:i A', strtotime($row['bid_time'])); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-hashtag mr-2"></i>
                                        Reference ID: <?php echo htmlspecialchars($row['history_id']); ?>
                                    </p>
                                </div>
                                <div class="mt-4 text-center">
                                    <?php if ($row['payment_exists'] > 0): ?>
                                        <div class="paid-badge">
                                            <i class="fas fa-check-circle mr-2"></i>Payment Completed
                                        </div>
                                    <?php else: ?>
                                        <button class="btn pay-now-btn" 
                                                onclick="makePayment('<?php echo htmlspecialchars($row['history_id']); ?>', 
                                                                    '<?php echo htmlspecialchars($row['bid_amount']); ?>', 
                                                                    '<?php echo htmlspecialchars($row['bid_id']); ?>')">
                                            <i class="fas fa-credit-card mr-2"></i>Pay Now
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <i class="fas fa-search mb-3"></i>
                <h3>No Winning Bids Yet</h3>
                <p class="text-muted mb-4">Keep participating in auctions to see your winning bids here!</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-gavel mr-2"></i>Browse Active Auctions
                </a>
            </div>
        <?php } ?>
    </div>

    <!-- Razorpay Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function makePayment(historyId, amount, auctionId) {
            // Convert amount to paise/cents as required by Razorpay
            var amountInPaise = Math.round(parseFloat(amount) * 100);
            
            var options = {
                "key": "rzp_test_uOVm47g65oGRuZ", // Your Razorpay Key ID
                "amount": amountInPaise,
                "currency": "INR",
                "name": "Art Gallery",
                "description": "Payment for Auction #" + auctionId,
                "image": "https://your-logo-url.png", // Replace with your logo URL
                "handler": function (response) {
                    // On successful payment
                    window.location.href = "payment_success.php?payment_id=" + response.razorpay_payment_id + 
                                         "&history_id=" + historyId + 
                                         "&auction_id=" + auctionId;
                },
                "prefill": {
                    "email": "<?php echo $_SESSION['email']; ?>",
                },
                "theme": {
                    "color": "#EAA636"
                }
            };
            
            var rzp = new Razorpay(options);
            rzp.open();
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>