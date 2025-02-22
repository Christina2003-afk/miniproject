<?php
session_start();
include 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email'];

// Fetch wishlist items for the specific logged-in user
$sql = "SELECT * FROM wishlist WHERE user_email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .wishlist-item {
            transition: all 0.3s ease;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            background: #fff;
        }
        .wishlist-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .wishlist-img {
            height: 200px;
            object-fit: cover;
        }
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .remove-btn:hover {
            background: #dc3545;
            color: white;
        }
        .empty-wishlist {
            text-align: center;
            padding: 50px 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4">My Wishlist</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="wishlist-item position-relative rounded overflow-hidden">
                            <div class="remove-btn" onclick="removeItem(<?php echo $row['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </div>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                 class="wishlist-img w-100">
                            <div class="p-3">
                                <h4 class="mb-2"><?php echo htmlspecialchars($row['product_name']); ?></h4>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($row['product_description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-primary mb-0">$<?php echo number_format($row['price'], 2); ?></h5>
                                    <span class="badge bg-success">In Stock: <?php echo $row['stock_quantity']; ?></span>
                                </div>
                                <button class="btn btn-primary w-100 mt-3" 
                                        onclick="addToCart(<?php echo $row['id']; ?>)">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart text-muted mb-3" style="font-size: 48px;"></i>
                <h3>Your wishlist is empty</h3>
                <p class="text-muted">Add items to your wishlist to see them here!</p>
                <a href="collection.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeItem(itemId) {
            if (confirm('Are you sure you want to remove this item from your wishlist?')) {
                fetch('remove_from_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: itemId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error removing item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error removing item');
                });
            }
        }

        function addToCart(itemId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: itemId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item added to cart successfully!');
                } else {
                    alert('Error adding item to cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding item to cart');
            });
        }
    </script>
</body>
</html>
