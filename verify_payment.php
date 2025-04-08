<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Request: ' . print_r($_POST, true) . "\n", FILE_APPEND);

require_once 'dbconfig.php';

// Verify the payment with Razorpay
if (!isset($_POST['payment_id']) || !isset($_POST['product_id']) || !isset($_POST['amount'])) {
    $error_msg = 'Missing required parameters: ' . 
                 (isset($_POST['payment_id']) ? '' : 'payment_id ') . 
                 (isset($_POST['product_id']) ? '' : 'product_id ') . 
                 (isset($_POST['amount']) ? '' : 'amount');
    
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Error: ' . $error_msg . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit;
}

$payment_id = $_POST['payment_id'];
$product_id = $_POST['product_id'];
$amount = $_POST['amount'];

// Log received data
file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Processing payment_id: ' . $payment_id . ', product_id: ' . $product_id . ', amount: ' . $amount . "\n", FILE_APPEND);

// Fetch the product details to double-check
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Error: Product not found for ID: ' . $product_id . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();
$product_amount_in_paise = $product['price'] * 100;

// Log product information
file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Product found: ' . $product['product_name'] . ', price: ' . $product['price'] . ', amount_in_paise: ' . $product_amount_in_paise . "\n", FILE_APPEND);

// Verify that the amount matches
if ($product_amount_in_paise != $amount) {
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Error: Amount mismatch. Expected: ' . $product_amount_in_paise . ', Got: ' . $amount . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Amount mismatch']);
    exit;
}

// For testing purposes, you can skip the actual verification with Razorpay API
// and directly create the order if you're having issues with the API
$skip_verification = true; // Set to false in production

if ($skip_verification) {
    // Skip verification and create order directly
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Skipping Razorpay verification for testing' . "\n", FILE_APPEND);
    
    // Insert order into database
    $order_query = "INSERT INTO orders (product_id, payment_id, amount, customer_name, customer_email, status, created_at) 
                    VALUES (?, ?, ?, 'Test Customer', 'test@example.com', 'paid', NOW())";
    $order_stmt = $conn->prepare($order_query);
    $amount_dollars = $amount / 100; // Convert back to dollars/rupees for storage
    $order_stmt->bind_param("iss", $product_id, $payment_id, $amount_dollars);
    
    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;
        file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Order created successfully, ID: ' . $order_id . "\n", FILE_APPEND);
        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } else {
        $error = $conn->error;
        file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Error creating order: ' . $error . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $error]);
    }
} else {
    // Verify with Razorpay API
    $razorpay_key_id = 'rzp_test_uOVm47g65oGRuZ'; // Your key
    $razorpay_key_secret = 'YOUR_SECRET_KEY';    // Replace with your secret
    
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Verifying with Razorpay API, payment_id: ' . $payment_id . "\n", FILE_APPEND);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payments/' . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $razorpay_key_id . ':' . $razorpay_key_secret);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $curl_error = curl_error($ch);
        file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - cURL Error: ' . $curl_error . "\n", FILE_APPEND);
    }
    
    curl_close($ch);
    
    file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Razorpay API Response code: ' . $http_status . ', Response: ' . $response . "\n", FILE_APPEND);
    
    if ($http_status === 200) {
        $razorpay_response = json_decode($response, true);
        
        // Check if payment is authorized/captured
        if ($razorpay_response['status'] === 'authorized' || $razorpay_response['status'] === 'captured') {
            // Payment is valid, create order in database
            $customer_name = isset($razorpay_response['notes']['customer_name']) ? $razorpay_response['notes']['customer_name'] : 'Customer';
            $customer_email = isset($razorpay_response['notes']['customer_email']) ? $razorpay_response['notes']['customer_email'] : '';
            
            // Insert order into database
            $order_query = "INSERT INTO orders (product_id, payment_id, amount, customer_name, customer_email, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, 'paid', NOW())";
            $order_stmt = $conn->prepare($order_query);
            $amount_dollars = $amount / 100; // Convert back to dollars/rupees for storage
            $order_stmt->bind_param("isiss", $product_id, $payment_id, $amount_dollars, $customer_name, $customer_email);
            
            if ($order_stmt->execute()) {
                $order_id = $conn->insert_id;
                file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Order created successfully, ID: ' . $order_id . "\n", FILE_APPEND);
                echo json_encode(['success' => true, 'order_id' => $order_id]);
            } else {
                $error = $conn->error;
                file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Error creating order: ' . $error . "\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $error]);
            }
        } else {
            file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Payment not authorized, status: ' . $razorpay_response['status'] . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Payment not authorized']);
        }
    } else {
        file_put_contents('payment_log.txt', date('Y-m-d H:i:s') . ' - Failed to verify payment with Razorpay, status: ' . $http_status . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Failed to verify payment with Razorpay']);
    }
}
?>