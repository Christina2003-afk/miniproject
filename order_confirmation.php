<?php require_once 'dbconfig.php'; // Ensure this file contains your database connection setup

// Check if order_id is set in the URL
if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit;
}

$order_id = $_GET['order_id'];

// Fetch order details from the database
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Order not found.";
    exit;
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(159, 108, 45);
            --primary-light: rgba(159, 108, 45, 0.1);
            --primary-dark: rgb(130, 85, 30);
            --text-dark: #333333;
            --text-light: #777777;
            --bg-light: #f9f9f9;
            --bg-white: #ffffff;
            --border-color: #eeeeee;
            --success-color: #4CAF50;
            --accent-color: #EAA636;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 25px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .confirmation-container:hover {
            transform: translateY(-5px);
        }
        
        .success-banner {
            background: linear-gradient(to right, var(--success-color), #66BB6A);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .success-banner i {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .confirmation-body {
            padding: 40px;
        }
        
        .confirmation-message {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .confirmation-message h2 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .confirmation-message p {
            color: var(--text-light);
            font-size: 18px;
        }
        
        .order-details {
            background-color: var(--bg-light);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 35px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        
        .order-details h4 {
            color: var(--primary-color);
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }
        
        .order-details p {
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
        }
        
        .order-details p strong {
            color: var(--text-dark);
            display: flex;
            align-items: center;
            font-weight: 600;
        }
        
        .order-details p strong i {
            margin-right: 10px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .order-details .value {
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 5px 12px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .order-status {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .footer-message {
            text-align: center;
            margin: 30px 0;
            color: var(--text-light);
            font-size: 16px;
            background-color: rgba(76, 175, 80, 0.1);
            padding: 15px;
            border-radius: 8px;
        }
        
        .footer-message i {
            color: var(--success-color);
            margin-right: 8px;
        }
        
        .order-actions {
            display: flex;
            gap: 20px;
            margin-top: 35px;
            justify-content: center;
        }
        
        .order-btn {
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .order-btn i {
            margin-right: 8px;
        }
        
        .order-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        .secondary-btn {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .secondary-btn:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }
        
        .footer {
            background-color: var(--bg-light);
            text-align: center;
            padding: 25px;
            color: var(--text-light);
            font-size: 14px;
            border-top: 1px solid var(--border-color);
            margin-top: 40px;
        }
        
        /* Receipt Styles - Enhanced */
        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            padding: 40px;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px dashed var(--border-color);
        }
        
        .receipt-header h2 {
            color: var(--primary-color);
            font-size: 28px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .receipt-header p {
            color: var(--text-light);
            font-size: 16px;
            margin-top: 5px;
        }
        
        .receipt-logo {
            max-width: 180px;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 30px;
        }
        
        .receipt-info-block {
            flex: 1;
            background-color: var(--bg-light);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .receipt-info-block h4 {
            color: var(--primary-color);
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .receipt-info-block p {
            margin: 8px 0;
            font-size: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .receipt-info-block p strong {
            color: var(--text-dark);
        }
        
        .receipt-product {
            margin-bottom: 30px;
            padding: 20px;
            background-color: var(--bg-light);
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 4px solid var(--accent-color);
        }
        
        .receipt-product-details {
            display: flex;
            align-items: center;
        }
        
        .receipt-product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .receipt-product-info h4 {
            color: var(--primary-color);
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .receipt-product-info p {
            margin: 5px 0;
            font-size: 15px;
            color: var(--text-light);
        }
        
        .receipt-total {
            margin-top: 30px;
            padding: 20px;
            border-top: 2px dashed var(--border-color);
            background-color: var(--bg-light);
            border-radius: 8px;
        }
        
        .receipt-total p {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 16px;
        }
        
        .receipt-total .total-amount {
            font-size: 22px;
            font-weight: 600;
            color: var(--primary-color);
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px solid var(--border-color);
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px dashed var(--border-color);
            font-size: 15px;
            color: var(--text-light);
        }
        
        .print-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 25px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .print-button i {
            margin-right: 8px;
        }
        
        .print-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .receipt-container, .receipt-container * {
                visibility: visible;
            }
            .receipt-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
            }
            .print-button {
                display: none;
            }
            .header, .footer, .confirmation-container {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .confirmation-container, .receipt-container {
                margin: 20px 15px;
                border-radius: 10px;
            }
            
            .confirmation-body, .receipt-container {
                padding: 25px 20px;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .receipt-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .receipt-product-details {
                flex-direction: column;
                text-align: center;
            }
            
            .receipt-product-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-store"></i> Pure Art Gallery</h1>
    </div>

    <div class="confirmation-container">
        <div class="success-banner">
            <i class="fas fa-check-circle"></i> Order Confirmed
        </div>
        
        <div class="confirmation-body">
            <div class="confirmation-message">
                <h2>Thank You for Your Purchase!</h2>
                <p>Your order has been successfully placed and is being processed.</p>
            </div>
            
            <div class="order-details">
                <h4><i class="fas fa-file-invoice"></i> Order Details</h4>
                <p>
                    <strong><i class="fas fa-hashtag"></i> Order ID:</strong>
                    <span class="value"><?php echo htmlspecialchars($order['id']); ?></span>
                </p>
                <p>
                    <strong><i class="fas fa-box"></i> Product ID:</strong>
                    <span class="value"><?php echo htmlspecialchars($order['product_id']); ?></span>
                </p>
                <p>
                    <strong><i class="fas fa-rupee-sign"></i> Amount Paid:</strong>
                    <span class="value">₹<?php echo htmlspecialchars($order['amount']); ?></span>
                </p>
                <p>
                    <strong><i class="fas fa-info-circle"></i> Status:</strong>
                    <span class="order-status"><?php echo htmlspecialchars($order['status']); ?></span>
                </p>
                <p>
                    <strong><i class="far fa-calendar-alt"></i> Order Date:</strong>
                    <span class="value"><?php echo htmlspecialchars($order['created_at']); ?></span>
                </p>
            </div>
            
            <div class="footer-message">
                <p><i class="far fa-envelope"></i> We have sent a confirmation email to your registered email address.</p>
            </div>
            
            <div class="order-actions">
                <a href="index.php" class="order-btn"><i class="fas fa-home"></i> Continue Shopping</a>
                <a href="product_review.php?product_id=<?php echo htmlspecialchars($order['product_id']); ?>&order_id=<?php echo htmlspecialchars($order['id']); ?>" class="order-btn secondary-btn"><i class="fas fa-star"></i> Write a Review</a>
            </div>
        </div>
    </div>
    
    <div class="receipt-container" id="receipt">
        <div class="receipt-header">
            <img src="images/logo.png" alt="Pure Art Gallery Logo" class="receipt-logo" onerror="this.style.display='none'">
            <h2>Pure Art Gallery</h2>
            <p>Tax Invoice / Receipt</p>
        </div>
        
        <div class="receipt-info">
            <div class="receipt-info-block">
                <h4>Order Information</h4>
                <p><strong>Order ID:</strong> <span><?php echo htmlspecialchars($order['id']); ?></span></p>
                <p><strong>Date:</strong> <span><?php echo htmlspecialchars($order['created_at']); ?></span></p>
                <p><strong>Payment Status:</strong> <span><?php echo htmlspecialchars($order['status']); ?></span></p>
            </div>
            
            <div class="receipt-info-block">
                <h4>Customer Information</h4>
                <p><strong>Email:</strong> <span><?php echo htmlspecialchars($order['user_email'] ?? 'Not available'); ?></span></p>
                <p><strong>Transaction ID:</strong> <span><?php echo htmlspecialchars($order['transaction_id'] ?? 'Not available'); ?></span></p>
            </div>
        </div>
        
        <div class="receipt-product">
            <div class="receipt-product-details">
                <?php if (!empty($order['product_image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($order['product_image']); ?>" 
                     alt="<?php echo htmlspecialchars($order['product_name'] ?? 'Product'); ?>" 
                     class="receipt-product-image"
                     onerror="this.src='placeholder.jpg'; this.alt='Image not available';">
                <?php endif; ?>
                
                <div class="receipt-product-info">
                    <h4><?php echo htmlspecialchars($order['product_name'] ?? 'Art Product'); ?></h4>
                    <p><?php echo htmlspecialchars($order['product_description'] ?? 'No description available'); ?></p>
                    <p><strong>Product ID:</strong> <?php echo htmlspecialchars($order['product_id']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="receipt-total">
            <p>
                <span>Subtotal:</span>
                <span>₹<?php echo htmlspecialchars($order['amount']); ?></span>
            </p>
            <p>
                <span>Tax (GST 18%):</span>
                <span>₹<?php echo number_format($order['amount'] * 0.18, 2); ?></span>
            </p>
            <p class="total-amount">
                <span>Total Amount:</span>
                <span>₹<?php echo number_format($order['amount'] * 1.18, 2); ?></span>
            </p>
        </div>
        
        <div class="receipt-footer">
            <p>Thank you for your purchase!</p>
            <p>For any queries, please contact us at support@pureartgallery.com</p>
            <p>&copy; <?php echo date('Y'); ?> Pure Art Gallery. All rights reserved.</p>
            <button onclick="window.print()" class="print-button"><i class="fas fa-print"></i> Print Receipt</button>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Auction System. All rights reserved.</p>
    </div>
</body>
</html>