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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #6c757d;
            --accent-color: #48cae4;
            --danger-color: #e63946;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --text-color: #212529;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --header-gradient: linear-gradient(135deg, #4361ee, #3f37c9);
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
        }

        .page-header {
            background: rgb(159, 108, 45);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: var(--card-shadow);
        }

        .header-title {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            transition: transform 0.2s;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-control, .form-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: border 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .btn-primary {
            background-color: rgb(159, 108, 45);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }
        
        .category-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .subcategory-item {
            padding: 12px;
            margin: 6px 0;
            background: white;
            border-radius: 6px;
            border-left: 3px solid var(--accent-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 6px rgba(0,0,0,0.05);
            padding: 15px 20px;
        }

        .action-button {
            margin-top: 20px;
            display: inline-block;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="header-title mb-0"><i class="fas fa-folder-tree me-2"></i>Category Management</h2>
                <a href="admindash.php" class="btn btn-light"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            </div>
        </div>
    </div>

    <div class="container pb-5">
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

        <div class="row g-4">
            <!-- Add Category Form -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Add New Category</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="category_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="categoryDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="categoryDescription" name="category_description" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Subcategory Form -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Add New Subcategory</h5>
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
                                <textarea class="form-control" id="subcategoryDescription" name="subcategory_description" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_subcategory" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Subcategory
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="viewcategory.php" class="btn btn-primary action-button">
                <i class="fas fa-list me-2"></i>View All Categories
            </a>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>