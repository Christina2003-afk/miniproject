<?php
include 'dbconfig.php';

header('Content-Type: application/json');

if(isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    // Add error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    try {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT subcategory_id, subcategory_name 
                               FROM subcategories 
                               WHERE category_id = ?");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $category_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $subcategories = array();
        while($row = $result->fetch_assoc()) {
            $subcategories[] = array(
                'subcategory_id' => $row['subcategory_id'],
                'subcategory_name' => $row['subcategory_name']
            );
        }
        
        echo json_encode($subcategories);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
} else {
    echo json_encode(array('error' => 'No category_id provided'));
}

$conn->close();
?>
