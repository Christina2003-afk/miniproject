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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #ffe9ec;
            font-family: 'Poppins', sans-serif;
            color: #495057;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            background: rgb(159, 108, 45);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .breadcrumb-item a {
            color: white;
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-header {
            background: rgb(159, 108, 45);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-bottom: none;
        }
        
        .btn-primary {
            background:rgb(234, 166, 54);
            border-color: #6f5b3e;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background: rgb(234, 166, 54);
            border-color: #8B4513;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #8B4513;
            box-shadow: 0 0 0 0.25rem rgba(139, 69, 19, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        
        .product-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
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
        
        .alert {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .admin-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .admin-actions .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .admin-actions .btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1"><i class="fas fa-box-open me-2"></i>Product Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Product Management</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="viewproduct.php" class="btn btn-light">
                    <i class="fas fa-list"></i> View All Products
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <!-- Add Product Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Product</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-4">
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
                            <div class="mb-4">
                                <label for="productName" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="productName" name="product_name" required>
                            </div>
                            <div class="mb-4">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="product_description" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="price" class="form-label">Price ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="stockQuantity" class="form-label">Stock Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                                    <input type="number" class="form-control" id="stockQuantity" name="stock_quantity" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="productImage" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="productImage" name="product_image" accept="image/*">
                                <div class="form-text">Recommended size: 800x600 pixels, Max: 2MB</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="reset" class="btn btn-light me-2">
                            <i class="fas fa-redo me-1"></i> Reset
                        </button>
                        <button type="submit" name="add_product" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="admin-actions">
            <a href="viewproduct.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Products
            </a>
            <a href="admindash.php" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>