<?php
require_once 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data with the correct field names
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];
    $product_size = $_POST['product_size'];
    $starting_amount = $_POST['starting_amount'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $seller_email = $_POST['seller_email']; // Get the seller's email
    
    // Handle file upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a valid image
    $check = getimagesize($_FILES["product_image"]["tmp_name"]);
    if($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    
    // Check if file already exists
    if (file_exists($target_file)) {
        $target_file = $target_dir . time() . "_" . basename($_FILES["product_image"]["name"]);
    }
    
    // Check file size
    if ($_FILES["product_image"]["size"] > 5000000) { // 5MB
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            // File uploaded successfully, now save data to database
            $action = "pending"; // Default action is pending
            
            $stmt = $conn->prepare("INSERT INTO bid (product_name, product_description, product_image, product_size, start_datetime, end_datetime, starting_amount, action, seller_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssdss", $product_name, $product_description, $target_file, $product_size, $start_datetime, $end_datetime, $starting_amount, $action, $seller_email);
            
            if ($stmt->execute()) {
                echo "Bid details submitted successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    
    $conn->close();
}
?>