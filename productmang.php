<?php
include("dbconfig.php");
session_start();

// Process Product Addition
if (isset($_POST['add_product'])) {
    $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    
    // Handle image upload
    $image_url = '';
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "uploads/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . time() . '_' . basename($_FILES["product_image"]["name"]);
        if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }
    
    $query = "INSERT INTO products (subcategory_id, product_name, product_description, price, stock_quantity, image_url) 
              VALUES ('$subcategory_id', '$product_name', '$product_description', '$price', '$stock_quantity', '$image_url')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Product added successfully";
    } else {
        $_SESSION['error'] = "Error adding product: " . mysqli_error($conn);
    }
    header("Location: productmang.php");
    exit();
}

// Process Product Deletion
if (isset($_POST['delete_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    // Get image URL before deleting
    $image_query = "SELECT image_url FROM products WHERE product_id = '$product_id'";
    $image_result = mysqli_query($conn, $image_query);
    $product = mysqli_fetch_assoc($image_result);
    
    $query = "DELETE FROM products WHERE product_id = '$product_id'";
    
    if (mysqli_query($conn, $query)) {
        // Delete image file if exists
        if(!empty($product['image_url']) && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        $_SESSION['success'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting product: " . mysqli_error($conn);
    }
    header("Location: productmang.php");
    exit();
}

// Fetch categories and subcategories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch all products with category and subcategory information
$products_query = "SELECT p.*, s.subcategory_name, c.category_name 
                  FROM products p 
                  JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
                  JOIN categories c ON s.category_id = c.category_id 
                  ORDER BY c.category_name, s.subcategory_name, p.product_name";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            background:rgb(159, 108, 45);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
        }

        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: #A0522D;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background:rgb(111, 91, 62);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .product-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .product-image {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
        }

        .price-tag {
            color: #8B4513;
            font-weight: bold;
            font-size: 1.2em;
        }

        .stock-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="categories.php">Categories</a>
                <a href="products.php" class="active">Products</a>
                <a href="orders.php">Orders</a>
                <a href="settings.php">Settings</a>
            </nav>
        </div>

        <div class="main-content flex-grow-1">
            <h2 class="mb-4">Product Management</h2>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <!-- Add Product Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Product</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subcategory" class="form-label">Category & Subcategory</label>
                                    <select class="form-select" id="subcategory" name="subcategory_id" required>
                                        <option value="">Select Subcategory</option>
                                        <?php 
                                        mysqli_data_seek($categories_result, 0);
                                        while($category = mysqli_fetch_assoc($categories_result)) {
                                            $subcategories_query = "SELECT * FROM subcategories WHERE category_id = '{$category['category_id']}' ORDER BY subcategory_name";
                                            $subcategories_result = mysqli_query($conn, $subcategories_query);
                                            
                                            echo "<optgroup label='" . htmlspecialchars($category['category_name']) . "'>";
                                            while($subcategory = mysqli_fetch_assoc($subcategories_result)) {
                                                echo "<option value='" . $subcategory['subcategory_id'] . "'>" . 
                                                     htmlspecialchars($subcategory['subcategory_name']) . "</option>";
                                            }
                                            echo "</optgroup>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="productName" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="productName" name="product_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="productDescription" name="product_description" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="stockQuantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productImage" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="productImage" name="product_image" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </form>
                </div>
            </div>

            <!-- Add this button after your "Add Product" form and before the products list -->
        

            <!-- Products List -->
            <!-- Add a button to view products -->
            <div class="text-center mb-4">
                <a href="viewproduct.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Products
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>