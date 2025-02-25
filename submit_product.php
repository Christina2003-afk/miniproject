<?php
include("dbconfig.php"); // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $subcategory = $_POST['subcategory'];
    $category = $_POST['category']; // Capture the category

    // Handle image upload
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/"; // Directory to store uploaded images
    $target_file = $target_dir . basename($image);

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Prepare SQL statement to insert product into the seller_products table
        $insert_query = "INSERT INTO seller_products (title, description, price, image, category, subcategory) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssdsss", $title, $description, $price, $target_file, $category, $subcategory);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                    alert('Product added successfully to Pure Art Gallery!');
                    setTimeout(function() {
                        window.location.href = 'productlisting.php';
                    }, 2000);
                  </script>";
        } else {
            echo "Error adding product: " . mysqli_error($conn);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error uploading image.";
    }
}

// Close the database connection
mysqli_close($conn);
?> 