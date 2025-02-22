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
    <title>Seller Dashboard - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            background: rgb(177, 144, 53);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 30px;
        }

        .sidebar .nav-link {
            color: white;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
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
            background:rgb(123, 90, 23);
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
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }

        .store-item {
            background: #ffffff;
            box-shadow: 0 0 45px rgba(0, 0, 0, .08);
            transition: .5s;
            margin-bottom: 30px;
        }

        .store-item:hover {
            box-shadow: 0 0 45px rgba(0, 0, 0, .15);
        }

        .store-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, .3);
            opacity: 0;
            transition: .5s;
        }

        .store-item:hover .store-overlay {
            opacity: 1;
        }

        .btn-dark:hover {
            background-color: #B37A41;
            border-color: #B37A41;
        }

        .far.fa-heart {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Seller Dashboard Sidebar -->
        <div class="sidebar">
            <h2>Seller Dashboard</h2>
            <nav>
                <a href="sellerdashboard.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="sellerproduct.php" class="nav-link active">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="bidding.html" class="nav-link">
                    <i class="fas fa-gavel"></i> Bids
                </a>
                <a href="sellerorders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="sellercat.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i> categories
                </a>
                <a href="sellersettings.php" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content Area -->
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
            <div class="row g-4">
                <?php
                // Fetch products from database
                $query = "SELECT p.*, c.category_name 
                         FROM products p 
                         JOIN categories c ON p.category_id = c.category_id";
                $result = mysqli_query($conn, $query);
                
                while($product = mysqli_fetch_assoc($result)) { ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="store-item position-relative text-center">
                            <img class="img-fluid" src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['product_name']; ?>">
                            <div class="p-4">
                                <h4 class="mb-3"><?php echo $product['product_name']; ?></h4>
                                <p><?php echo $product['description']; ?></p>
                                <h4 class="text-primary">$<?php echo number_format($product['price'], 2); ?></h4>
                                <div class="store-overlay">
                                    <a href="#" class="btn btn-primary rounded-pill py-2 px-4 m-2">More Detail</a>
                                    <button onclick="addToWishlist(<?php echo $product['product_id']; ?>)" 
                                            class="btn btn-dark rounded-pill py-2 px-4 m-2">
                                        <i class="far fa-heart"></i> Add to Wishlist
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
    function addToWishlist(productId) {
        // Check if wishlist array exists in session, if not create it
        if(!sessionStorage.getItem('wishlist')) {
            sessionStorage.setItem('wishlist', JSON.stringify([]));
        }
        
        let wishlist = JSON.parse(sessionStorage.getItem('wishlist'));
        
        // Add product ID to wishlist if not already present
        if(!wishlist.includes(productId)) {
            wishlist.push(productId);
            sessionStorage.setItem('wishlist', JSON.stringify(wishlist));
            alert('Product added to wishlist!');
            
            // Redirect to wishlist page
            window.location.href = 'wishlist.php';
        } else {
            alert('Product is already in your wishlist!');
        }
    }
    </script>
</body>
</html>