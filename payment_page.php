<?php
require_once 'dbconfig.php';

// Check if product_id is set
if (!isset($_GET['product_id'])) {
    die("Product ID is missing.");
}

$product_id = $_GET['product_id'];

// Fetch product details from the database
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6200ea;
            --primary-light: #e4dbf5;
            --dark: #1a1a2e;
            --gray-dark: #343a40;
            --gray: #6c757d;
            --gray-light: #f8f9fa;
            --success: #28a745;
            --body-bg: #f5f5f5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: var(--body-bg);
            color: var(--gray-dark);
            line-height: 1.5;
        }
        
        .top-nav {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 15px;
            margin: 0 auto;
        }
        
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-weight: 700;
            font-size: 22px;
            color: rgb(234, 166, 54);
            text-decoration: none;
        }
        
        .back-link {
            color: var(--gray-dark);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .main-content {
            padding: 50px 0;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 450px;
        }
        
        .product-image {
            background-color: #f9f9f9;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
        }
        
        .product-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(98, 0, 234, 0.3);
        }
        
        .product-details {
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
        
        .product-category {
            color: var(--gray);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .product-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color:  rgb(234, 166, 54);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .product-description {
            color: var(--gray);
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .features-list {
            margin-bottom: 30px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .feature-icon {
            width: 22px;
            height: 22px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .action-buttons {
            margin-top: auto;
            display: flex;
            gap: 15px;
        }
        
        .btn-primary {
            background-color:  rgb(234, 166, 54);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1;
            box-shadow: 0 6px 15px rgba(98, 0, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(98, 0, 234, 0.4);
        }
        
        .payment-options {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        
        .payment-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .payment-method {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .payment-icon {
            width: 50px;
            height: 50px;
            padding: 12px;
            border-radius: 10px;
            background-color: var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .payment-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .payment-method:hover .payment-icon {
            background-color: var(--primary-light);
            transform: translateY(-3px);
        }
        
        .payment-label {
            font-size: 12px;
            color: var(--gray);
        }
        
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .trust-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            font-size: 13px;
        }
        
        .trust-badge i {
            color: var(--success);
        }
        
        footer {
            background-color: white;
            padding: 30px 0;
            margin-top: 50px;
            text-align: center;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                min-height: 300px;
            }
            
            .product-details {
                padding: 25px;
            }
            
            .product-title {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .payment-methods {
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="container">
            <div class="nav-content">
                <a href="#" class="logo">Pure Art   Gallery</a>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <div class="product-grid">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="product-badge">Featured</div>
                    </div>
                    <div class="product-details">
                        <div class="product-category"><?php echo htmlspecialchars($product['product_name']); ?></div>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                        <div class="product-price">
                            <i class="fas fa-rupee-sign"></i> <?php echo htmlspecialchars($product['price']); ?>
                        </div>
                        <p class="product-description"><?php echo htmlspecialchars($product['product_description']); ?></p>
                        <div class="features-list">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-check"></i></div>
                                <span>Authentic Product</span>
                            </div>
                            
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-check"></i></div>
                                <span>Premium Quality</span>
                            </div>
                        </div>
                        <form action="process_payment.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <input type="hidden" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
                            <div class="action-buttons">
                                <a href="payment.php?product_id=<?php echo $product_id; ?>" class="btn-primary">
                                    <i class="fas fa-bolt"></i> Buy Now
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AuctionPro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 