<?php
session_start();
require_once 'dbconfig.php';

// Get current user's email if logged in
$current_user = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Improved error handling for database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Add the new bid update functions
function updateBid($bid_id, $bidder_email, $bid_amount) {
    global $conn;

    // First, check if this is the highest bid for this item
    $query = "SELECT MAX(bid_amount) as current_highest FROM bid_history WHERE bid_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_highest = $row['current_highest'];

    // If no bids yet or this bid is higher
    if ($current_highest === NULL || $bid_amount > $current_highest) {
        // Insert the new bid into bid_history
        $insert_query = "INSERT INTO bid_history (bid_id, bidder_email, bid_amount, bid_time) 
                         VALUES (?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isd", $item_id, $bidder_email, $bid_amount);
        
        if ($insert_stmt->execute()) {
            // Now update the items table to reflect new highest bidder
            $update_query = "UPDATE items 
                           SET highest_bidder = ?, current_bid = ? 
                           WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sdi", $bidder_email, $bid_amount);
            $update_stmt->execute();
            
            return array(
                'success' => true,
                'message' => 'Your bid of ₹' . number_format($bid_amount, 2) . ' has been placed successfully!'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Database error: ' . $conn->error
            );
        }
    } else {
        return array(
            'success' => false,
            'message' => 'Your bid must be higher than the current highest bid of ₹' . number_format($current_highest, 2)
        );
    }
}

function isHighestBidder($item_id, $current_user_email) {
    global $conn;
    
    // Get the current highest bidder from the items table
    $query = "SELECT highest_bidder FROM items WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return ($current_user_email == $row['highest_bidder'] && $row['highest_bidder'] != '');
}

// Display success/error messages from session
$message = '';
if (isset($_SESSION['success'])) {
    $message = '<div class="success-message">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $message = '<div class="error-message">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

try {
    // Fetch accepted bids with start and end times along with highest bid info
    // Using prepared statement to prevent SQL injection
    $bid_query = "SELECT b.bid_id, b.product_name, b.product_description, b.product_image, 
                     b.starting_amount, b.start_datetime, b.end_datetime, b.seller_email,
                     COALESCE(MAX(h.bid_amount), b.starting_amount) as current_highest_bid,
                     COALESCE(h.bidder_email, '') as highest_bidder
              FROM bid b
              LEFT JOIN bid_history h ON b.bid_id = h.bid_id
              WHERE b.action = 'accepted'
              GROUP BY b.bid_id
              ORDER BY b.start_datetime DESC";

    $result = $conn->query($bid_query);
    
    // Check for query execution errors
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }

    // Current time for comparison
    $current_time = date("Y-m-d H:i:s");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Continue execution with empty result set
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Auction | Bid on Exclusive Artworks</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a6ea5;
            --secondary-color: #ff6b6b;
            --background-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --border-color: #e0e0e0;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #EAA636;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .countdown-banner {
            background: #EAA636;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .auction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .auction-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .auction-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }
        
        .auction-image {
            height: 200px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        
        .auction-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .auction-card:hover .auction-image img {
            transform: scale(1.05);
        }
        
        .auction-details {
            padding: 1.5rem;
        }
        

        .auction-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #EAA636;
        }
        
        .auction-description {
            color: var(--text-color);
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .auction-meta {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-top: 1px solid var(--border-color);
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .auction-price {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin: 0.5rem 0;
        }
        
        .auction-current-bid {
            font-weight: 600;
            color: var(--success-color);
            font-size: 1.2rem;
            margin: 0.5rem 0;
        }
        
        .auction-seller {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        
        .auction-time {
            display: flex;
            justify-content: space-between;
            background-color: rgba(58, 110, 165, 0.1);
            padding: 0.5rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }
        
        .auction-time div {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .auction-time span {
            font-weight: 500;
        }
        
        .bid-form {
            margin-top: 1rem;
        }
        
        .bid-input {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .bid-input input {
            flex: 1;
            padding: 0.6rem;
            border: 1px solid var(--border-color);
            border-radius: 4px 0 0 4px;
            font-family: 'Poppins', sans-serif;
        }
        
        .bid-input input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .bid-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            padding: 0 1rem;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .bid-button:hover {
            background-color: #2d5a8c;
        }
        
        .closed-label {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            display: inline-block;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .pending-label {
            background-color: var(--warning-color);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            display: inline-block;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        
        .highest-bidder {
            color: var(--success-color);
            font-weight: 500;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .countdown {
            font-weight: 600;
            color: #EAA636;
            font-size: 1rem;
            margin: 0.5rem 0;
            text-align: center;
        }
        
        .winning-bid {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid var(--success-color);
            border-radius: 4px;
            padding: 0.5rem;
            margin-top: 1rem;
            color: var(--success-color);
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .empty-state p {
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .success-message {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .auction-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Art Auction Gallery</h1>
        <p class="subtitle">Bid on exclusive artworks from talented artists</p>
    </header>
    
    <div class="countdown-banner">
        <h3>Join our next special auction event starting in</h3>
        <div id="event-countdown">Loading next event...</div>
    </div>

    <?php 
    // Display session messages
    echo $message;
    
    // Display URL error messages
    if (isset($_GET['error'])): ?>
        <div class="error-message">
            <p><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="auction-grid">
            <?php while ($row = $result->fetch_assoc()): 
                // Convert times to a readable format
                $start_time = $row['start_datetime'];
                $end_time = $row['end_datetime'];
                
                // Get the correct image path
                $image_path = $row['product_image'];
                if (!empty($image_path) && strpos($image_path, 'uploads/') === false) {
                    $image_path = 'uploads/' . $image_path;
                }
                
                // Determine auction status
                $status = '';
                if ($current_time < $start_time) {
                    $status = 'pending';
                } elseif ($current_time >= $start_time && $current_time <= $end_time) {
                    $status = 'active';
                } else {
                    $status = 'closed';
                }
                
                // Calculate next bid amount (with validation)
                $current_highest_bid = floatval($row['current_highest_bid']);
                $minimum_bid = $current_highest_bid + 50;
                
                // Check if current user is the highest bidder
                $is_highest_bidder = ($current_user == $row['highest_bidder'] && $row['highest_bidder'] != '');
            ?>
                <div class="auction-card" data-bid-id="<?php echo htmlspecialchars($row['bid_id']); ?>" data-status="<?php echo $status; ?>">
                    <div class="auction-image">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                             alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                             onerror="this.src='placeholder.jpg'; this.alt='Image not available';">
                    </div>
                    <div class="auction-details">
                        <h3 class="auction-title"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p class="auction-seller">By <?php echo htmlspecialchars($row['seller_email']); ?></p>
                        <p class="auction-description"><?php echo htmlspecialchars($row['product_description']); ?></p>
                        
                        <p class="auction-price">Starting Bid: ₹<?php echo htmlspecialchars($row['starting_amount']); ?></p>
                        
                        <?php if ($current_highest_bid > floatval($row['starting_amount'])): ?>
                            <p class="auction-current-bid">Current Bid: ₹<?php echo htmlspecialchars($row['current_highest_bid']); ?></p>
                            
                            <?php if ($row['highest_bidder']): ?>
                                <p class="highest-bidder">
                                    Highest Bidder: <?php echo (strpos($row['highest_bidder'], '@') !== false) ? 
                                        htmlspecialchars(substr($row['highest_bidder'], 0, strpos($row['highest_bidder'], '@'))) : 
                                        htmlspecialchars($row['highest_bidder']); ?>
                                    <?php echo ($is_highest_bidder) ? ' (You)' : ''; ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="auction-time">
                            <div>
                                <small>Starts</small>
                                <span><?php echo date("M d, h:i A", strtotime($start_time)); ?></span>
                            </div>
                            <div>
                                <small>Ends</small>
                                <span><?php echo date("M d, h:i A", strtotime($end_time)); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($status == 'active'): ?>
                            <div class="countdown" data-end="<?php echo $end_time; ?>">
                                Calculating time remaining...
                            </div>
                            
                            <?php if ($current_user && $current_user != $row['seller_email']): ?>
                                <!-- FIXED: Form action now just "place_bid.php" -->
                                <form action="place_bid.php" method="POST" class="bid-form">
                                    <input type="hidden" name="bid_id" value="<?php echo htmlspecialchars($row['bid_id']); ?>">
                                    <input type="hidden" name="current_bid" value="<?php echo htmlspecialchars($row['current_highest_bid']); ?>">
                                    <input type="hidden" name="seller_email" value="<?php echo htmlspecialchars($row['seller_email']); ?>">
                                    <input type="hidden" name="auction_status" value="active">
                                    <div class="bid-input">
                                        <input type="number" name="bid_amount" min="<?php echo $minimum_bid; ?>" value="<?php echo $minimum_bid; ?>" step="50" placeholder="Enter bid amount" required>
                                        <button type="submit" class="bid-button">Place Bid</button>
                                    </div>
                                    <small>Minimum bid: ₹<?php echo $minimum_bid; ?> (in increments of ₹50)</small>
                                </form>
                            <?php elseif ($current_user == $row['seller_email']): ?>
                                <p><small>You cannot bid on your own auction</small></p>
                            <?php else: ?>
                                <p><small>Please <a href="login.php">login</a> to place a bid</small></p>
                            <?php endif; ?>
                            
                        <?php elseif ($status == 'pending'): ?>
                            <div class="countdown" data-start="<?php echo $start_time; ?>">
                                Bidding starts in: Calculating...
                            </div>
                            <div class="pending-label" id="pending-label-<?php echo htmlspecialchars($row['bid_id']); ?>">Bidding Not Started</div>
                            
                            <?php if ($current_user && $current_user != $row['seller_email']): ?>
                                <!-- Initially hidden bid form for pending auctions -->
                                <form action="place_bid.php" method="POST" class="bid-form pending-bid-form" id="bid-form-<?php echo htmlspecialchars($row['bid_id']); ?>" style="display: none;">
                                    <input type="hidden" name="bid_id" value="<?php echo htmlspecialchars($row['bid_id']); ?>">
                                    <input type="hidden" name="current_bid" value="<?php echo htmlspecialchars($row['current_highest_bid']); ?>">
                                    <input type="hidden" name="seller_email" value="<?php echo htmlspecialchars($row['seller_email']); ?>">
                                    <input type="hidden" name="auction_status" value="active">
                                    <div class="bid-input">
                                        <input type="number" name="bid_amount" min="<?php echo $minimum_bid; ?>" value="<?php echo $minimum_bid; ?>" step="50" placeholder="Enter bid amount" required>
                                        <button type="submit" class="bid-button">Place Bid</button>
                                    </div>
                                    <small>Minimum bid: ₹<?php echo $minimum_bid; ?> (in increments of ₹50)</small>
                                </form>
                            <?php elseif ($current_user == $row['seller_email']): ?>
                                <p><small>You cannot bid on your own auction</small></p>
                            <?php else: ?>
                                <p><small>Please <a href="login.php">login</a> to place a bid</small></p>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="closed-label">Bidding Closed</div>
                            
                            <?php if ($is_highest_bidder): ?>
                                <div class="winning-bid">
                                    <i class="fas fa-trophy"></i> You won this auction!
                                </div>
                            <?php elseif ($row['highest_bidder']): ?>
                                <div class="highest-bidder">
                                    Won by: <?php echo (strpos($row['highest_bidder'], '@') !== false) ? 
                                        htmlspecialchars(substr($row['highest_bidder'], 0, strpos($row['highest_bidder'], '@'))) : 
                                        htmlspecialchars($row['highest_bidder']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-gavel"></i>
            <h3>No Auctions Available</h3>
            <p>There are no active art pieces up for auction at the moment. Please check back later.</p>
            <?php if (isset($e)): ?>
                <p class="text-danger">System message: <?php echo htmlspecialchars($e->getMessage()); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Update countdowns for each auction
    function updateCountdowns() {
        try {
            document.querySelectorAll('.countdown').forEach(counter => {
                const now = new Date().getTime();
                let targetTime;
                let message;
                
                if (counter.dataset.end) {
                    targetTime = new Date(counter.dataset.end).getTime();
                    message = "Time remaining: ";
                } else if (counter.dataset.start) {
                    targetTime = new Date(counter.dataset.start).getTime();
                    message = "Bidding starts in: ";
                    
                    // Check if bidding should be active
                    const diff = targetTime - now;
                    if (diff <= 0) {
                        // Auction has started
                        counter.textContent = "Bidding is now open!";
                        
                        // Find parent auction card and update its status
                        const card = counter.closest('.auction-card');
                        if (card) {
                            // Update card status attribute
                            card.dataset.status = 'active';
                            
                            const bidId = card.dataset.bidId;
                            const pendingLabel = document.getElementById(`pending-label-${bidId}`);
                            const bidForm = document.getElementById(`bid-form-${bidId}`);
                            
                            if (pendingLabel) {
                                pendingLabel.style.display = 'none';
                            }
                            if (bidForm) {
                                bidForm.style.display = 'block';
                                // Update the hidden status field
                                const statusInput = bidForm.querySelector('input[name="auction_status"]');
                                if (statusInput) {
                                    statusInput.value = 'active';
                                }
                            }
                        }
                        return;
                    }
                } else {
                    counter.textContent = "Time information unavailable";
                    return;
                }
                
                const diff = targetTime - now;
                
                if (diff <= 0) {
                    // Time's up
                    if (counter.dataset.end) {
                        counter.textContent = "Auction has ended";
                        // Disable the bid form if auction has ended
                        const card = counter.closest('.auction-card');
                        if (card) {
                            card.dataset.status = 'closed';
                            const form = card.querySelector('.bid-form');
                            if (form) {
                                const button = form.querySelector('.bid-button');
                                if (button) {
                                    button.disabled = true;
                                    button.textContent = "Bidding Closed";
                                }
                            }
                        }
                        // Refresh the page to update auction status after a brief delay
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    } else {
                        counter.textContent = "Bidding is now open!";
                        // Update auction status and show bid form
                        const card = counter.closest('.auction-card');
                        if (card) {
                            card.dataset.status = 'active';
                            const bidId = card.dataset.bidId;
                            const pendingLabel = document.getElementById(`pending-label-${bidId}`);
                            const bidForm = document.getElementById(`bid-form-${bidId}`);
                            
                            if (pendingLabel) {
                                pendingLabel.style.display = 'none';
                            }
                            if (bidForm) {
                                bidForm.style.display = 'block';
                            }
                        }
                    }
                    return;
                }
                
                // Calculate time components
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                // Display the countdown
                if (days > 0) {
                    counter.textContent = `${message}${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    counter.textContent = `${message}${hours}h ${minutes}m ${seconds}s`;
                }
            });
        } catch (error) {
            console.error("Error updating countdowns:", error);
        }
    }
    
    // Function to check if auctions have started and update UI
    function checkAuctionStatus() {
        try {
            const now = new Date().getTime();
            
            document.querySelectorAll('.auction-card').forEach(card => {
                // Only check pending auctions
                if (card.dataset.status === 'pending') {
                    const countdown = card.querySelector('.countdown[data-start]');
                    if (countdown) {
                        const startTime = new Date(countdown.dataset.start).getTime();
                        
                        if (now >= startTime) {
                            // Auction has started - update status
                            card.dataset.status = 'active';
                            
                            // Update UI elements
                            const bidId = card.dataset.bidId;
                            const pendingLabel = document.getElementById(`pending-label-${bidId}`);
                            const bidForm = document.getElementById(`bid-form-${bidId}`);
                            
                            if (pendingLabel) {
                                pendingLabel.style.display = 'none';
                            }
                            
                            if (bidForm) {
                                bidForm.style.display = 'block';
                                
                                // Update form action to ensure correct processing
                                bidForm.action = "place_bid.php";
                                
                                // Update the auction status field
                                const statusInput = bidForm.querySelector('input[name="auction_status"]');
                                if (statusInput) {
                                    statusInput.value = 'active';
                                } else {
                                    // Create status input if it doesn't exist
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'auction_status';
                                    input.value = 'active';
                                    bidForm.appendChild(input);
                                }
                            }
                            
                            countdown.textContent = "Bidding is now open!";
                        }
                    }
                }
            });
        } catch (error) {
            console.error("Error checking auction status:", error);
        }
    }
    
    // Event countdown for the banner
    function updateEventCountdown() {
        try {
            const now = new Date();
            const target = new Date(now);
            target.setDate(target.getDate() + 5);
            target.setHours(target.getHours() + 12);
            target.setMinutes(target.getMinutes() + 36);
            
            const diff = target - now;
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const countdownElement = document.getElementById('event-countdown');
            if (countdownElement) {
                countdownElement.textContent = 
                    `${days} days, ${hours} hours, ${minutes} minutes, ${seconds} seconds`;
            }
        } catch (error) {
            console.error("Error updating event countdown:", error);
            const countdownElement = document.getElementById('event-countdown');
            if (countdownElement) {
                countdownElement.textContent = "Coming soon";
            }
        }
    }
    
    // Handle form submission to prevent the "bidding not started" error
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a bid button
        if (e.target && e.target.classList.contains('bid-button')) {
            const form = e.target.closest('form');
            if (form) {
                // Get the auction card
                const card = form.closest('.auction-card');
                if (card) {
                    // Check if the auction has actually started
                    const status = card.dataset.status;
                    if (status !== 'active') {
                        // Prevent form submission if auction is not active
                        e.preventDefault();
                        alert('Bidding has not started yet. Please wait for the auction to begin.');
                    }
                }
            }
        }
    });
    
    // Initialize and start countdown timers with error handling
    document.addEventListener('DOMContentLoaded', function() {
        try {
            updateEventCountdown();
            updateCountdowns();
            checkAuctionStatus(); // Initial check
            // Set up interval to update countdown timers
            setInterval(function() {
                try {
                    updateEventCountdown();
                    updateCountdowns();
                    checkAuctionStatus();
                } catch (error) {
                    console.error("Error in timer update:", error);
                }
            }, 1000);
        } catch (startupError) {
            console.error("Error during initialization:", startupError);
        }
    });
    
    // Form validation for bid amounts
    document.querySelectorAll('.bid-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            try {
                const amountInput = this.querySelector('input[name="bid_amount"]');
                const currentBidInput = this.querySelector('input[name="current_bid"]');
                
                if (amountInput && currentBidInput) {
                    const bidAmount = parseFloat(amountInput.value);
                    const currentBid = parseFloat(currentBidInput.value);
                    const minimumBid = currentBid + 50;
                    
                    if (bidAmount < minimumBid) {
                        e.preventDefault();
                        alert(`Your bid must be at least ₹${minimumBid}`);
                    } else if (bidAmount % 50 !== 0) {
                        e.preventDefault();
                        alert('Bid amount must be in increments of ₹50');
                    }
                }
            } catch (error) {
                console.error("Error in bid validation:", error);
                e.preventDefault();
                alert('There was an error processing your bid. Please try again.');
            }
        });
    });
    
    // Add to watchlist functionality
    const setupWatchlistButtons = () => {
        document.querySelectorAll('.watchlist-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const bidId = this.dataset.bidId;
                if (!bidId) return;
                
                // Send AJAX request to add/remove from watchlist
                fetch('watchlist_toggle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `bid_id=${bidId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle button state
                        this.innerHTML = data.watched ? 
                            '<i class="fas fa-heart"></i> Remove from Watchlist' : 
                            '<i class="far fa-heart"></i> Add to Watchlist';
                            
                        // Show feedback
                        const message = data.watched ? 
                            'Added to your watchlist!' : 
                            'Removed from your watchlist';
                            
                        const feedback = document.createElement('div');
                        feedback.className = 'success-message watchlist-feedback';
                        feedback.textContent = message;
                        feedback.style.position = 'fixed';
                        feedback.style.bottom = '20px';
                        feedback.style.right = '20px';
                        feedback.style.padding = '10px 20px';
                        feedback.style.zIndex = '1000';
                        document.body.appendChild(feedback);
                        
                        // Remove feedback after 3 seconds
                        setTimeout(() => {
                            feedback.style.opacity = '0';
                            feedback.style.transition = 'opacity 0.5s ease';
                            setTimeout(() => feedback.remove(), 500);
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error toggling watchlist:', error);
                });
            });
        });
    };
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    if (src) {
                        img.src = src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imgObserver.observe(img);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.src = img.getAttribute('data-src');
            img.removeAttribute('data-src');
        });
    }
    
    // Handle notifications permission
    function requestNotificationPermission() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
                localStorage.setItem('notificationPermission', permission);
            });
        }
    }
    
    // Check if we already asked for permission
    if (localStorage.getItem('notificationAsked') !== 'true') {
        // Wait a few seconds before asking
        setTimeout(() => {
            requestNotificationPermission();
            localStorage.setItem('notificationAsked', 'true');
        }, 5000);
    }
    
    // Function to send bid ending notifications
    function sendBidEndingNotification(auction) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const title = `Auction Ending Soon: ${auction.title}`;
            const options = {
                body: `"${auction.title}" auction will end in 15 minutes. Current highest bid: ₹${auction.currentBid}`,
                icon: '/favicon.ico'
            };
            
            new Notification(title, options);
        }
    }
    
    // Check for auctions ending soon
    function checkAuctionsEndingSoon() {
        const now = new Date().getTime();
        const fifteenMinutes = 15 * 60 * 1000;
        
        document.querySelectorAll('.auction-card[data-status="active"]').forEach(card => {
            const countdown = card.querySelector('.countdown[data-end]');
            if (countdown) {
                const endTime = new Date(countdown.dataset.end).getTime();
                const timeLeft = endTime - now;
                
                // Check if auction is ending in the next 15 minutes and we haven't notified yet
                if (timeLeft > 0 && timeLeft <= fifteenMinutes && !card.dataset.notified) {
                    card.dataset.notified = 'true';
                    
                    // Get auction details
                    const title = card.querySelector('.auction-title').textContent;
                    const currentBid = card.querySelector('.auction-current-bid') ? 
                        card.querySelector('.auction-current-bid').textContent.replace('Current Bid: ₹', '') : 
                        card.querySelector('.auction-price').textContent.replace('Starting Bid: ₹', '');
                    
                    // Send notification
                    sendBidEndingNotification({
                        title: title,
                        currentBid: currentBid
                    });
                }
            }
        });
    }
    
    // Check for ending auctions every minute
    setInterval(checkAuctionsEndingSoon, 60000);
</script>

</body>
</html>