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
            --secondary-color: #6c757d;
            --accent-color: #28a745;
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
            width: var(--sidebar-width);
            background: #B37A41;  /* Brown color as shown in image */
            color: white;
            padding: 20px;
            min-height: 100vh;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .logo {
            width: 40px;
            height: 40px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            padding: 10px;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .nav-link i {
            width: 20px;
        }

        .nav-link span {
            font-size: 1.1rem;
        }

        .content-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .category-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .subcategory-item {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <div class="logo-section">
                <img src="logo.png" alt="Logo" class="logo">
                <h2>Admin Dashboard</h2>
            </div>
            
            <nav class="nav-links">
                <a href="admindash.php" class="nav-link">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-store"></i>
                    <span>Seller Management</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-gavel"></i>
                    <span>Bid Management</span>
                </a>
                <a href="productmang.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Product</span>
                </a>
                <a href="admincat.php" class="nav-link active">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
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
            <div class="text-center mb-4">
                <a href="viewcategory.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Categories
                </a>
            </div>

            <!-- Cvategories and Subcategories List -->
           
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>