<?php
session_start();
include 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_email = $_SESSION['email'];

// Get the raw POST data
$json = file_get_contents('php://input');
$product = json_decode($json, true);

// Validate the data
if (!$product || !isset($product['product_name']) || !isset($product['price'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    exit;
}

try {
    // Check if item already exists in wishlist for this user
    $check_sql = "SELECT id FROM wishlist WHERE user_email = ? AND product_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $user_email, $product['product_name']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Item already in wishlist']);
        exit;
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO wishlist (user_email, product_name, price, image_url, product_description, stock_quantity) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssi", 
        $user_email,
        $product['product_name'],
        $product['price'],
        $product['image_url'],
        $product['product_description'],
        $product['stock_quantity']
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
