<?php
session_start();
require_once 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if payment parameters are set
if (!isset($_GET['payment_id']) || !isset($_GET['history_id']) || !isset($_GET['auction_id'])) {
    header("Location: index.php");
    exit();
}

$payment_id = $_GET['payment_id'];
$history_id = $_GET['history_id'];
$auction_id = $_GET['auction_id']; // This is actually bid_id in your database
$user_email = $_SESSION['email'];

// Get bid details from the bid table
$query = "SELECT bh.*, 
          b.product_name,
          b.product_description,
          b.product_image,
          b.product_size,
          b.seller_email
          FROM bid_history bh
          JOIN bid b ON bh.bid_id = b.bid_id
          WHERE bh.history_id = ? 
          AND bh.bidder_email = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $history_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$bid_details = $result->fetch_assoc();

// Create payments table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS payments (
    payment_id VARCHAR(255) PRIMARY KEY,
    history_id VARCHAR(255),
    bid_id INT(11),
    amount DECIMAL(10,2),
    payment_date DATETIME,
    payment_status VARCHAR(50)
)";

$conn->query($create_table_query);

// Check if bid_id column exists in payments table, add it if it doesn't
$check_column_query = "SHOW COLUMNS FROM payments LIKE 'bid_id'";
$column_result = $conn->query($check_column_query);
if ($column_result->num_rows == 0) {
    // bid_id column doesn't exist, add it
    $alter_table_query = "ALTER TABLE payments ADD COLUMN bid_id INT(11) AFTER history_id";
    $conn->query($alter_table_query);
}

// Update payment status in database
$update_query = "INSERT INTO payments (payment_id, history_id, bid_id, amount, payment_date, payment_status) 
                VALUES (?, ?, ?, ?, NOW(), 'completed')
                ON DUPLICATE KEY UPDATE payment_status = 'completed'";

$stmt = $conn->prepare($update_query);
$stmt->bind_param("ssid", $payment_id, $history_id, $auction_id, $bid_details['bid_amount']);
$stmt->execute();

// Generate invoice number
$invoice_number = 'INV-' . date('Ymd') . '-' . $history_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success | Art Gallery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .success-header {
            background: #EAA636;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .receipt-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .receipt-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: #28a745;
        }
        .receipt-header {
            border-bottom: 1px dashed #dee2e6;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
        }
        .receipt-amount {
            font-size: 2rem;
            color: #28a745;
            font-weight: bold;
        }
        .receipt-info {
            color: #6c757d;
        }
        .receipt-footer {
            border-top: 1px dashed #dee2e6;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
        }
        .product-img {
            max-height: 200px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            font-size: 3rem;
            color: #28a745;
        }
        .print-btn {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 20px;
        }
        .print-btn:hover {
            background: #5a6268;
            color: white;
        }
        .home-btn {
            background: #EAA636;
            color: white;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 20px;
        }
        .home-btn:hover {
            background: #d8952e;
            color: white;
        }
        .invoice-id {
            font-weight: 600;
            color: #495057;
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .product-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            body {
                background-color: white;
            }
        }
    </style>
</head>
<body>
    <div class="success-header no-print">
        <div class="container">
            <div class="text-center">
                <i class="fas fa-check-circle success-icon mb-3"></i>
                <h1>Payment Successful!</h1>
                <p class="lead">Your payment has been processed successfully.</p>
            </div>
        </div>
    </div>

    <div class="container" id="receipt">
        <div class="receipt-card">
            <div class="receipt-header d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1">Art Gallery</h2>
                    <p class="text-muted mb-0">Payment Receipt</p>
                </div>
                <div class="text-right">
                    <span class="invoice-id mb-2 d-inline-block">
                        <i class="fas fa-receipt mr-1"></i>
                        <?php echo $invoice_number; ?>
                    </span>
                    <p class="text-muted mb-0">Date: <?php echo date('d M Y, h:i A'); ?></p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center h-100">
                        <div>
                            <h5 class="mb-3">Product Details</h5>
                            <h4><?php echo htmlspecialchars($bid_details['product_name'] ?? 'Product Name'); ?></h4>
                            
                            <div class="product-details">
                                <p class="mb-2"><strong>Description:</strong><br>
                                <?php echo nl2br(htmlspecialchars($bid_details['product_description'] ?? 'Product Description')); ?>
                                </p>
                                
                                <?php if (!empty($bid_details['product_size'])): ?>
                                <p class="mb-2">
                                    <i class="fas fa-ruler-combined mr-2"></i>
                                    <strong>Size:</strong> <?php echo htmlspecialchars($bid_details['product_size']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <p class="mb-2">
                                    <i class="fas fa-gavel mr-2"></i>
                                    <strong>Auction #:</strong> <?php echo htmlspecialchars($auction_id); ?>
                                </p>
                                
                                <p class="mb-0">
                                    <i class="fas fa-hashtag mr-2"></i>
                                    <strong>Bid Reference:</strong> <?php echo htmlspecialchars($history_id); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <?php if (!empty($bid_details['product_image'])): ?>
                    <img src="<?php echo htmlspecialchars($bid_details['product_image']); ?>" 
                         alt="Product Image" class="product-img img-fluid">
                    <?php else: ?>
                    <div class="p-5 bg-light text-center rounded">
                        <i class="fas fa-image fa-4x text-muted"></i>
                        <p class="mt-3 text-muted">No image available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="bg-light p-4 rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Payment Information</h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-envelope mr-2"></i>
                                    <strong>Buyer:</strong> <?php echo htmlspecialchars($user_email); ?>
                                </p>
                                <?php if (!empty($bid_details['seller_email'])): ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-store mr-2"></i>
                                    <strong>Seller:</strong> <?php echo htmlspecialchars($bid_details['seller_email']); ?>
                                </p>
                                <?php endif; ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    <strong>Payment ID:</strong> <?php echo htmlspecialchars($payment_id); ?>
                                </p>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <strong>Bid Date:</strong> <?php echo date('d M Y, h:i A', strtotime($bid_details['bid_time'] ?? 'now')); ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Status:</strong> Completed
                                </p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p class="text-muted mb-1">Amount Paid:</p>
                                <div class="receipt-amount">
                                    ₹<?php echo number_format($bid_details['bid_amount'], 2); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="receipt-footer d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-0 text-muted">Thank you for your purchase!</p>
                </div>
                <div class="no-print">
                    <button class="btn print-btn mr-2" onclick="window.print()">
                        <i class="fas fa-print mr-2"></i>Print Receipt
                    </button>
                    <a href="index.php" class="btn home-btn">
                        <i class="fas fa-home mr-2"></i>Go to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>