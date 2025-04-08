<?php
require_once 'dbconfig.php'; // Ensure this file contains your database connection setup

// Check if product_id and order_id are set in the URL
if (!isset($_GET['product_id']) || !isset($_GET['order_id'])) {
    echo "Invalid product or order ID.";
    exit;
}

$product_id = $_GET['product_id'];
$order_id = $_GET['order_id'];

// Fetch product details from the database
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Product not found.";
    exit;
}

$product = $result->fetch_assoc();

// Check if order belongs to the user (you might want to add session-based user authentication)
$order_query = "SELECT * FROM orders WHERE id = ? AND product_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("ii", $order_id, $product_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows !== 1) {
    echo "Order not found or does not match the product.";
    exit;
}

$order = $order_result->fetch_assoc();

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];
    $name = $_POST['name'] ?? 'Anonymous';
    $user_email = $_POST['email'] ?? ''; // Get email from form field
    
    // Validate form data
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $error = "Please select a valid rating between 1 and 5.";
    } elseif (empty($review_text)) {
        $error = "Please enter your review.";
    } else {
        // Insert review into database
        $insert_query = "INSERT INTO reviews (product_id, order_id, user_name, user_email, rating, review_text, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iissds", $product_id, $order_id, $name, $user_email, $rating, $review_text);
        
        if ($insert_stmt->execute()) {
            $message = "Thank you! Your review has been submitted successfully.";
        } else {
            $error = "Error submitting your review. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Review - Pure Art Gallery</title>
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
        
        .review-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--bg-white);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            padding: 40px;
        }
        
        .review-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .review-header h2 {
            color: var(--primary-color);
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .review-header p {
            color: var(--text-light);
            font-size: 16px;
            margin-top: 5px;
        }
        
        .product-details {
            display: flex;
            margin-bottom: 30px;
            background-color: var(--bg-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        
        .product-image {
            width: 40%;
            max-width: 200px;
            padding: 15px;
        }
        
        .product-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .product-info {
            flex: 1;
            padding: 20px;
        }
        
        .product-info h3 {
            color: var(--primary-color);
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .product-info p {
            margin: 5px 0;
            color: var(--text-light);
        }
        
        .review-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 20px;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            cursor: pointer;
            width: 40px;
            height: 40px;
            background-color: #ddd;
            margin-right: 5px;
            border-radius: 5px;
            text-align: center;
            line-height: 40px;
            color: #888;
            font-size: 20px;
            transition: all 0.2s ease;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input:checked ~ label {
            background-color: var(--accent-color);
            color: white;
        }
        
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
            color: var(--text-dark);
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .submit-btn i {
            margin-right: 8px;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid rgba(244, 67, 54, 0.2);
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
        
        @media (max-width: 768px) {
            .review-container {
                margin: 20px 15px;
                padding: 25px 20px;
            }
            
            .product-details {
                flex-direction: column;
            }
            
            .product-image {
                width: 100%;
                max-width: 100%;
                padding: 15px 15px 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-store"></i> Pure Art Gallery</h1>
    </div>

    <div class="review-container">
        <div class="review-header">
            <h2>Write Your Review</h2>
            <p>Share your experience with this product</p>
        </div>
        
        <?php if(!empty($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="product-details">
            <div class="product-image">
                <?php if (!empty($product['image_url'])): ?>
                    <!-- Debug: Uncomment the line below to see the image path -->
                    <?php //echo "Image path: " . htmlspecialchars($product['image_url']); ?>
                    
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name'] ?? 'Product'); ?>"
                         onerror="this.src='images/placeholder.jpg'; this.alt='Image not available';">
                <?php else: ?>
                    <img src="images/placeholder.jpg" alt="Product image placeholder">
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h3><?php echo htmlspecialchars($product['product_name'] ?? 'Art Product'); ?></h3>
                <p><?php echo htmlspecialchars($product['product_description'] ?? 'No description available'); ?></p>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
                <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($order['created_at'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <?php if(empty($message)): ?>
        <div class="review-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Rate this product:</label>
                    <div class="rating-stars">
                        <input type="radio" name="rating" id="star5" value="5" required>
                        <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star4" value="4">
                        <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star3" value="3">
                        <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star2" value="2">
                        <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star1" value="1">
                        <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name">Your Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($order['user_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($order['user_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="review_text">Your Review:</label>
                    <textarea id="review_text" name="review_text" placeholder="Share your experience with this product" required></textarea>
                </div>
                
                <button type="submit" name="submit_review" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        </div>
        <?php else: ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="submit-btn">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Pure Art Gallery. All rights reserved.</p>
    </div>
    
    <script>
        // Optional: You can add JavaScript for enhanced form validation here
        document.addEventListener('DOMContentLoaded', function() {
            // For example, you might want to highlight the stars on hover
            const stars = document.querySelectorAll('.rating-stars label');
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    // Add animation or highlighting logic
                });
            });
        });
    </script>
</body>
</html> 