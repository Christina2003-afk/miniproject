<?php
session_start();
include("dbconfig.php");

// Fetch winning bids by joining bid and bid_history tables
$query = "SELECT 
    b.bid_id,
    b.product_name,
    b.product_description,
    b.product_image,
    b.product_size,
    b.starting_amount,
    b.seller_email,
    bh.bidder_email,
    bh.bid_amount,
    bh.bid_time
    FROM bid b
    JOIN (
        SELECT bid_id, bidder_email, bid_amount, bid_time
        FROM bid_history
        WHERE (bid_id, bid_amount) IN (
            SELECT bid_id, MAX(bid_amount)
            FROM bid_history
            GROUP BY bid_id
        )
    ) bh ON b.bid_id = bh.bid_id
    WHERE b.action = 'accepted'
    ORDER BY bh.bid_time DESC
    LIMIT 6";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Winners</title>
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"></noscript>
    
    <style>
        /* Critical CSS loaded first */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            padding: 20px;
            min-height: 100vh;
        }

        .winners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            padding: 10px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .winner-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .product-image-container {
            width: 100%;
            max-height: 400px; /* Increased height for larger images */
            overflow: hidden;
            position: relative;
            background-color: #f5f5f5;
            flex: 0 0 auto;
        }

        .product-image {
            width: 100%;
            height: auto; /* Changed to auto to maintain aspect ratio */
            max-height: 100%;
            object-fit: contain; /* Changed from cover to contain to show full image */
            display: block;
            transition: transform 0.3s ease;
        }
        
        /* Image hover effect */
        .product-image:hover {
            transform: scale(1.02);
        }

        /* Lightbox feature */
        .product-image-container {
            cursor: pointer;
        }

        .image-error {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 200px;
            background-color: #f5f5f5;
            color: #777;
            font-size: 14px;
        }

        .winner-content {
            padding: 20px;
            flex: 1 0 auto;
        }

        /* Non-critical CSS */
        .dashboard-title {
            color: #1a1a1a;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .product-description {
            color: #4a5568;
            font-size: 0.9em;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .winner-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eef0f2;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #1a1a1a;
            font-weight: 600;
        }

        .winning-bid {
            color: #10b981;
            font-size: 1.1em;
        }

        .starting-amount {
            color: #ef4444;
            text-decoration: line-through;
            opacity: 0.8;
        }

        .winner-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #ffd700;
            color: #000000;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #64748b;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ffd700;
        }

        /* Modal/Lightbox for full-size image */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .winners-grid {
                grid-template-columns: 1fr;
            }
            
            .winner-card {
                margin: 0 auto;
                max-width: 400px;
            }
        }
        
        /* Back button and header styles */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #f0f2f5;
            color: #1a1a1a;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .back-button:hover {
            background-color: #e2e8f0;
            transform: translateY(-2px);
        }
        
        @media (max-width: 576px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h1 class="dashboard-title">
            <i class="fas fa-trophy"></i>
            Auction Winners
        </h1>
        <a href="admindash.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="winners-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="winner-card">
                    <div class="product-image-container" onclick="openImageModal('<?php echo htmlspecialchars($row['product_image']); ?>')">
                        <?php 
                        // Use the image path directly from the database
                        $imagePath = htmlspecialchars($row['product_image']);
                        
                        // Make sure the imagePath is not empty
                        if (!empty($imagePath)):
                        ?>
                            <img src="<?php echo $imagePath; ?>" 
                                alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                class="product-image"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="image-error" style="display: none;">
                                <i class="fas fa-image" style="margin-right: 10px;"></i> Image not available
                            </div>
                        <?php else: ?>
                            <div class="image-error">
                                <i class="fas fa-image" style="margin-right: 10px;"></i> Image not available
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="winner-content">
                        <h2 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h2>
                        <p class="product-description">
                            <?php 
                            $description = $row['product_description'];
                            echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description); 
                            ?>
                        </p>
                        <div class="winner-info">
                            <div class="info-row">
                                <span class="info-label">Size</span>
                                <span class="info-value"><?php echo htmlspecialchars($row['product_size']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Winner</span>
                                <span class="info-value">
                                    <?php 
                                    $winner_email = $row['bidder_email'];
                                    echo htmlspecialchars(strpos($winner_email, '@') !== false ? 
                                        substr($winner_email, 0, strpos($winner_email, '@')) : 
                                        $winner_email);
                                    ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Starting Amount</span>
                                <span class="info-value starting-amount">
                                    ₹<?php echo htmlspecialchars(number_format($row['starting_amount'], 2)); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Winning Bid</span>
                                <span class="info-value winning-bid">
                                    ₹<?php echo htmlspecialchars(number_format($row['bid_amount'], 2)); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Won On</span>
                                <span class="info-value">
                                    <?php echo date('M d, Y h:i A', strtotime($row['bid_time'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="winner-badge">
                            <i class="fas fa-trophy"></i>
                            Auction Winner
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-trophy"></i>
                <h2>No Winners Yet</h2>
                <p>When auctions are completed, winners will be displayed here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal/Lightbox -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Function to open the image modal
        function openImageModal(imagePath) {
            var modal = document.getElementById("imageModal");
            var modalImg = document.getElementById("modalImage");
            modal.style.display = "flex";
            modalImg.src = imagePath;
        }

        // Function to close the image modal
        function closeImageModal() {
            document.getElementById("imageModal").style.display = "none";
        }
    </script>
</body>
</html>