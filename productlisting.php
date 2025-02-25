<!-- productlistings.php -->
<?php
session_start();
include("dbconfig.php");

// Check if the seller is logged in
if (!isset($_SESSION['seller_id'])) {
    die("You must be logged in to view your products.");
}

$seller_id = $_SESSION['seller_id'];

// Fetch products for the logged-in seller
$query = "SELECT * FROM seller_products WHERE seller_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }

        #product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .product img {
            max-width: 100%;
            border-radius: 8px;
            height: 200px; /* Fixed height for uniformity */
            object-fit: cover; /* Ensures the image covers the area */
        }

        .product h2 {
            font-size: 1.5em;
            color: #343a40;
            margin: 10px 0;
        }

        .product p {
            color: #6c757d;
            margin: 5px 0;
        }

        .product .price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
        }

        .product .category {
            font-style: italic;
            color: #007bff;
        }

        .product .subcategory {
            color: #6c757d;
        }

        @media (max-width: 768px) {
            #product-list {
                grid-template-columns: 1fr; /* Stack items on smaller screens */
            }
        }
    </style>
</head>
<body>
    <h1>Your Products</h1>
    <table>
        <thead>
            <tr>
                <th>Seller ID</th>
                <th>Product Title</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['seller_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['subcategory']) . "</td>";
                    echo "<td><img src='" . htmlspecialchars($row['image']) . "' alt='Product Image' width='100'></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No products found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>