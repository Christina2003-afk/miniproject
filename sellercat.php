<?php
include("dbconfig.php");
session_start();

// Process Category Addition
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_description = mysqli_real_escape_string($conn, $_POST['category_description']);
    
    $query = "INSERT INTO categories (category_name, category_description) 
              VALUES ('$category_name', '$category_description')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Category added successfully";
    } else {
        $_SESSION['error'] = "Error adding category: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}

// Process Subcategory Addition
if (isset($_POST['add_subcategory'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $subcategory_name = mysqli_real_escape_string($conn, $_POST['subcategory_name']);
    $subcategory_description = mysqli_real_escape_string($conn, $_POST['subcategory_description']);
    
    $query = "INSERT INTO subcategories (category_id, subcategory_name, subcategory_description) 
              VALUES ('$category_id', '$subcategory_name', '$subcategory_description')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Subcategory added successfully";
    } else {
        $_SESSION['error'] = "Error adding subcategory: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}

// Process Category Deletion
if (isset($_POST['delete_category'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $query = "DELETE FROM categories WHERE category_id = '$category_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Category deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting category: " . mysqli_error($conn);
    }
    header("Location: categories.php");
    exit();
}

// Process Subcategory Deletion
if (isset($_POST['delete_subcategory'])) {
    $subcategory_id = mysqli_real_escape_string($conn, $_POST['subcategory_id']);
    $query = "DELETE FROM subcategories WHERE subcategory_id = '$subcategory_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Subcategory deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting subcategory: " . mysqli_error($conn);
    }
    header("Location: admincat.php");
    exit();
}

// Fetch all categories
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch all subcategories with their parent category names
$subcategories_query = "SELECT s.*, c.category_name 
                       FROM subcategories s 
                       JOIN categories c ON s.category_id = c.category_id 
                       ORDER BY c.category_name, s.subcategory_name";
$subcategories_result = mysqli_query($conn, $subcategories_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-color: rgb(177, 144, 53);
            --secondary-color: rgb(123, 90, 23);
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --text-color: #212529;
            --sidebar-width: 250px;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            background:rgb(177, 144, 53);
            color: white;
            padding: 20px;
            height: 100vh;
            width: 250px;
            position: fixed;
        }

        .logo-section {
            padding: 20px 15px;
            margin-bottom: 30px;
        }

        .logo-section h2 {
            font-size: 1.5rem;
            margin: 0;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 5px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background:rgb(228, 205, 77);
            color: #000;
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .nav-link span {
            font-size: 1rem;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
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
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <div class="logo-section">
                <i class="fas fa-store"></i>
                <h2>Seller Dashboard</h2>
            </div>
            
            <nav class="nav-links">
                <a href="sellerdashboard.php" class="nav-link">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="sellerproduct.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="sellerbidart.php" class="nav-link">
                    <i class="fas fa-gavel"></i>
                    <span>Bid Art</span>
                </a>
                <a href="sellerorders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="sellercustomers.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="sellercat.php" class="nav-link active">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="sellersettings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <div class="main-content flex-grow-1 p-4">
            <h2 class="mb-4">Category Management</h2>

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

            <div class="row">
                <!-- Add Category Form -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Category</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="categoryName" name="category_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="categoryDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="categoryDescription" name="category_description" rows="2"></textarea>
                                </div>
                                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Add Subcategory Form -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Subcategory</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label for="parentCategory" class="form-label">Parent Category</label>
                                    <select class="form-select" id="parentCategory" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php 
                                        mysqli_data_seek($categories_result, 0);
                                        while($category = mysqli_fetch_assoc($categories_result)) { 
                                        ?>
                                            <option value="<?php echo $category['category_id']; ?>">
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="subcategoryName" class="form-label">Subcategory Name</label>
                                    <input type="text" class="form-control" id="subcategoryName" name="subcategory_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subcategoryDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="subcategoryDescription" name="subcategory_description" rows="2"></textarea>
                                </div>
                                <button type="submit" name="add_subcategory" class="btn btn-primary">Add Subcategory</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--<div class="text-center mb-4">
                <a href="viewcategory.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Categories
                </a>
            </div>

            <!-- Cvategories and Subcategories List -->
           
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>