<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwxMTc3M3wwfDF8c2VhcmNofDF8fGJhY2tncm91bmR8ZW58MHx8fHwxNjI3MjY1NjY0&ixlib=rb-1.2.1&q=80&w=1080') no-repeat center center fixed; /* Background image */
            background-size: cover;
            margin: 0;
            padding: 20px;
            color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .add-product-form {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
        }

        .add-product-form h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }

        .add-product-form label {
            display: block;
            margin-bottom: 5px;
            color: #343a40;
        }

        .add-product-form input,
        .add-product-form select,
        .add-product-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.2s;
        }

        .add-product-form input:focus,
        .add-product-form select:focus,
        .add-product-form textarea:focus {
            border-color: #80bdff;
            outline: none;
        }

        .add-product-form input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .add-product-form input[type="submit"]:hover {
            background-color: #218838;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group .subcategory-label {
            display: block;
            margin-top: 10px;
            color: #343a40;
        }

        @media (max-width: 768px) {
            .add-product-form {
                padding: 20px;
            }
        }
    </style>
    <script>
        function updateSubcategories() {
            const category = document.getElementById("category").value;
            const subcategorySelect = document.getElementById("subcategory");
            subcategorySelect.innerHTML = ""; // Clear existing options

            let subcategories = [];

            // Define subcategories based on selected category
            if (category === "Painting") {
                subcategories = [
                    "Oil Painting",
                    "Watercolor Painting",
                    "Acrylic Painting",
                    "Digital Painting",
                    "Mixed Media"
                ];
            } else if (category === "Drawing & Illustration") {
                subcategories = [
                    "Pencil Sketching",
                    "Charcoal Drawing",
                    "Ink Illustration",
                    "Pastel Art"
                ];
            } else if (category === "Sculpture") {
                subcategories = [
                    "Marble Sculpture",
                    "Bronze Sculpture",
                    "Wood Carving",
                    "Ceramic Sculpture"
                ];
            } else if (category === "Photography") {
                subcategories = [
                    "Fine Art Photography",
                    "Black & White Photography",
                    "Abstract Photography",
                    "Portrait Photography"
                ];
            } else if (category === "Printmaking") {
                subcategories = [
                    "Lithography",
                    "Screen Printing",
                    "Etching",
                    "Woodcut Printing"
                ];
            } else if (category === "Textile & Fiber Art") {
                subcategories = [
                    "Tapestry",
                    "Quilting",
                    "Embroidery Art"
                ];
            } else if (category === "Installation Art") {
                subcategories = [
                    "Light Art Installations",
                    "Interactive Installations",
                    "Environmental Installations"
                ];
            } else if (category === "Contemporary & Conceptual Art") {
                subcategories = [
                    "Minimalist Art",
                    "Abstract Expressionism",
                    "Pop Art"
                ];
            }

            // Populate subcategory dropdown
            subcategories.forEach(function(subcategory) {
                const option = document.createElement("option");
                option.value = subcategory;
                option.textContent = subcategory;
                subcategorySelect.appendChild(option);
            });
        }
    </script>
</head>
<body>
    <h1>Add a New Product</h1>
    <div class="add-product-form">
        <form action="submit_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" onchange="updateSubcategories()" required>
                    <option value="">Select a category</option>
                    <option value="Painting">Painting</option>
                    <option value="Drawing & Illustration">Drawing & Illustration</option>
                    <option value="Sculpture">Sculpture</option>
                    <option value="Photography">Photography</option>
                    <option value="Printmaking">Printmaking</option>
                    <option value="Textile & Fiber Art">Textile & Fiber Art</option>
                    <option value="Installation Art">Installation Art</option>
                    <option value="Contemporary & Conceptual Art">Contemporary & Conceptual Art</option>
                </select>
            </div>

            <div class="form-group">
                <label class="subcategory-label" for="subcategory">Subcategory:</label>
                <select id="subcategory" name="subcategory" required>
                    <option value="">Select a subcategory</option>
                    <!-- Subcategories will be populated based on the selected category -->
                </select>
            </div>

            <input type="submit" value="Add Product">
        </form>
    </div>
</body>
</html> 