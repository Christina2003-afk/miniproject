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

// Convert price to paise (smallest currency unit in INR)
$amount_in_paise = $product['price'] * 100;

// Process form submission directly - without relying on Razorpay callback
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_shipping'])) {
    // Validate and sanitize inputs
    $full_name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = 'India'; // Default value
    $shipping_method = 'Standard'; // Default value
    
    // Create a temporary order if needed (you'd normally connect this to a real order)
    $order_query = "INSERT INTO orders (product_id, payment_id, amount, status) VALUES (?, 'pending', ?, 'pending')";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("id", $product_id, $product['price']);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    
    // Now insert the shipping details
    $shipping_query = "INSERT INTO shipping_details (order_id, full_name, email, phone, address, city, pincode, country, shipping_method) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $shipping_stmt = $conn->prepare($shipping_query);
    $shipping_stmt->bind_param("issssssss", 
        $order_id, 
        $full_name, 
        $email, 
        $phone, 
        $address, 
        $city, 
        $pincode, 
        $country, 
        $shipping_method
    );
    
    if ($shipping_stmt->execute()) {
        $shipping_success = "Shipping details saved successfully!";
    } else {
        $shipping_error = "Error saving shipping details: " . $shipping_stmt->error;
    }
}

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment for <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #528FF0;
            --primary-dark: #3670c7;
            --accent-color: #ffb74d;
            --bg-color: #f4f7fb;
            --card-color: #ffffff;
            --text-color: #333333;
            --text-muted: #6c757d;
            --border-color: #e0e6ed;
            --success-color: #4caf50;
            --error-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 20px;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .payment-header h1 {
            color:  rgb(234, 166, 54);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .payment-header p {
            color: var(--text-muted);
            margin: 0;
        }
        
        .payment-details {
            background: var(--card-color);
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .payment-details h2 {
            color: var(--text-color);
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .product-image {
            flex: 0 0 160px;
            height: 160px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-image:hover img {
            transform: scale(1.05);
        }
        
        .product-details {
            flex: 1;
        }
        
        .product-details h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 20px;
            color: var(--text-color);
        }
        
        .product-price {
            font-size: 22px;
            font-weight: 600;
            color: rgb(234, 166, 54);
            margin-bottom: 10px;
        }
        
        .product-description {
            color: var(--text-muted);
            margin-bottom: 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .shipping-form {
            background-color: var(--card-color);
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .shipping-form h2 {
            color: var(--text-color);
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            color: var(--text-color);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(82, 143, 240, 0.2);
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn-primary {
            background-color: rgb(234, 166, 54);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-pay {
            background-color: rgb(234, 166, 54);
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(82, 143, 240, 0.25);
        }
        
        .btn-pay:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(82, 143, 240, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: var(--error-color);
        }
        
        .debug-container {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .debug-container h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 500;
        }
        
        .debug-info {
            background-color: #f1f3f5;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            font-family: monospace;
            font-size: 13px;
            color: #495057;
        }
        
        .debug-info p {
            margin: 5px 0;
        }
        
        .payment-methods {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .payment-methods img {
            height: 24px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        
        .payment-methods img:hover {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .product-image {
                width: 100%;
                height: auto;
                aspect-ratio: 16/9;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1><i class="fas fa-shopping-cart"></i> Checkout</h1>
            <p>Complete your purchase securely</p>
        </div>
        
        <div class="payment-details">
            <h2>Product Details</h2>
            
            <div class="product-info">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                </div>
                
                <div class="product-details">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <div class="product-price">₹<?php echo htmlspecialchars($product['price']); ?></div>
                    <p class="product-description"><?php echo htmlspecialchars($product['product_description']); ?></p>
                </div>
            </div>
        </div>
        
        <?php if(isset($shipping_success)): ?>
            <div class="alert alert-success"><?php echo $shipping_success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($shipping_error)): ?>
            <div class="alert alert-danger"><?php echo $shipping_error; ?></div>
        <?php endif; ?>
        
        <div class="shipping-form">
            <h2>Shipping Information</h2>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city"><i class="fas fa-city"></i> City</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pincode"><i class="fas fa-map-pin"></i> Pincode</label>
                        <input type="text" id="pincode" name="pincode" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_shipping" class="btn-primary">
                        <i class="fas fa-save"></i> Save Shipping Details
                    </button>
                    
                    <button type="button" id="rzp-button" class="btn-pay">
                        <i class="fas fa-lock"></i> Pay Now with Razorpay
                    </button>
                </div>
                
               
            </form>
        </div>
        
        <div id="payment-message"></div>
        
        <div class="debug-container">
            <h4>Debug Information</h4>
            <div class="debug-info">
                <p>Product ID: <?php echo $product_id; ?></p>
                <p>Amount in paise: <?php echo $amount_in_paise; ?></p>
                
                <!-- Database connection test -->
                <?php
                if ($conn) {
                    echo "<p style='color:green'>Database connection: GOOD</p>";
                    
                    // Check shipping_details table existence
                    $table_check = $conn->query("SHOW TABLES LIKE 'shipping_details'");
                    if ($table_check->num_rows > 0) {
                        echo "<p style='color:green'>shipping_details table: EXISTS</p>";
                    } else {
                        echo "<p style='color:red'>shipping_details table: MISSING</p>";
                    }
                    
                    // Check orders table existence
                    $orders_check = $conn->query("SHOW TABLES LIKE 'orders'");
                    if ($orders_check->num_rows > 0) {
                        echo "<p style='color:green'>orders table: EXISTS</p>";
                    } else {
                        echo "<p style='color:red'>orders table: MISSING</p>";
                    }
                } else {
                    echo "<p style='color:red'>Database connection: FAILED</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('rzp-button').addEventListener('click', function(e) {
            // Get user details
            var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
            var phone = document.getElementById('phone').value;
            var address = document.getElementById('address').value;
            var city = document.getElementById('city').value;
            var pincode = document.getElementById('pincode').value;
            
            // Validate form
            if (!name || !email || !phone || !address || !city || !pincode) {
                alert('Please fill in all required fields');
                return;
            }
            
            var options = {
                "key": "rzp_test_uOVm47g65oGRuZ", // Using your actual key
                "amount": "<?php echo $amount_in_paise; ?>",
                "currency": "INR",
                "name": "Your Store",
                "description": "<?php echo htmlspecialchars($product['product_name']); ?>",
                "image": "", // Your company logo URL
                "prefill": {
                    "name": name,
                    "email": email,
                    "contact": phone
                },
                "theme": {
                    "color": "#528FF0"
                },
                "handler": function (response) {
                    // Show response in debug area
                    document.querySelector('.debug-info').innerHTML += '<p>Payment ID: ' + response.razorpay_payment_id + '</p>';
                    
                    document.getElementById('payment-message').innerHTML = '<div class="alert alert-success">Payment successful! Processing your order...</div>';
                    
                    // Send the payment and user details to your server
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'verify_payment.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            // Show the complete response for debugging
                            document.querySelector('.debug-info').innerHTML += '<p>Server Response: ' + xhr.responseText + '</p>';
                            
                            if (xhr.status === 200) {
                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        window.location.href = 'order_confirmation.php?order_id=' + response.order_id;
                                    } else {
                                        document.getElementById('payment-message').innerHTML = '<div class="alert alert-danger">Payment verification failed: ' + response.message + '</div>';
                                    }
                                } catch (e) {
                                    document.getElementById('payment-message').innerHTML = '<div class="alert alert-danger">Invalid response from server: ' + e.message + '</div>';
                                }
                            } else {
                                document.getElementById('payment-message').innerHTML = '<div class="alert alert-danger">Server error. Status: ' + xhr.status + '</div>';
                            }
                        }
                    };
                    xhr.send('payment_id=' + response.razorpay_payment_id + 
                             '&product_id=<?php echo $product_id; ?>' +
                             '&amount=<?php echo $amount_in_paise; ?>' +
                             '&name=' + encodeURIComponent(name) +
                             '&email=' + encodeURIComponent(email) +
                             '&phone=' + encodeURIComponent(phone) +
                             '&address=' + encodeURIComponent(address) +
                             '&city=' + encodeURIComponent(city) +
                             '&pincode=' + encodeURIComponent(pincode));
                },
                "modal": {
                    "ondismiss": function() {
                        document.querySelector('.debug-info').innerHTML += '<p>Payment modal dismissed</p>';
                    }
                }
            };
            
            // Log options to debug
            document.querySelector('.debug-info').innerHTML += '<p>Razorpay Options: ' + JSON.stringify(options) + '</p>';
            
            try {
                var rzp1 = new Razorpay(options);
                rzp1.open();
            } catch (error) {
                document.querySelector('.debug-info').innerHTML += '<p>Error: ' + error.message + '</p>';
            }
            
            e.preventDefault();
        });
    </script>
</body>
</html>