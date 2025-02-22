<?php
include 'dbconfig.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_GET['subcategory_id'])) {
    $subcategory_id = intval($_GET['subcategory_id']);
    
    try {
        // Debug: Print connection status
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Debug: Print query
        $query = "SELECT * FROM products WHERE subcategory_id = ?";
        error_log("Query: " . $query . " with subcategory_id: " . $subcategory_id);

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $subcategory_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $products = array();
        while($row = $result->fetch_assoc()) {
            $products[] = array(
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'product_description' => $row['product_description'],
                'price' => floatval($row['price']),
                'stock_quantity' => intval($row['stock_quantity']),
                'image_url' => $row['image_url']
            );
        }
        
        // Debug: Log the number of products found
        error_log("Found " . count($products) . " products for subcategory_id: " . $subcategory_id);
        
        echo json_encode($products);
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in get_products.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(array('error' => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'No subcategory_id provided'));
}

$conn->close();
?>
