<?php
session_start();
include("dbconfig.php");

// Check if user is logged in and is a seller
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Set seller_email in SESSION if not already set
if (!isset($_SESSION['seller_email'])) {
    $_SESSION['seller_email'] = $_SESSION['email'];
}

$email = $_SESSION['email'];
$seller_email = $_SESSION['seller_email'];

// Get seller information from database
$query = "SELECT * FROM table_reg WHERE email = '$email' AND role = 'seller'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: login.html");
    exit();
}

$seller = mysqli_fetch_assoc($result);
$sellerId = $seller['reg_id']; // Assuming 'reg_id' is the unique identifier for the seller

// Fetch products for the seller based on reg_id
$productQuery = "SELECT * FROM seller_products WHERE id = '$sellerId'"; // Ensure 'seller_id' corresponds to 'reg_id'
$productResult = mysqli_query($conn, $productQuery);

// Fetch notifications for the logged-in seller
$notif_query = "SELECT * FROM seller_notifications WHERE seller_email = ? ORDER BY created_at DESC";
$notif_stmt = $conn->prepare($notif_query);
$notif_stmt->bind_param("s", $seller_email);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

// Count unread notifications
$count_query = "SELECT COUNT(*) AS unread_count FROM seller_notifications WHERE seller_email = ? AND is_read = 0";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("s", $seller_email);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$unread_count = $count_row['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color:rgb(177, 144, 53);
            --secondary-color:rgb(159, 108, 45);
            --accent-color:rgb(228, 205, 77);
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text:rgb(241, 241, 238);
            --sidebar-width: 250px;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }

        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            transition: all 0.3s;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(114, 83, 20, 0.15);
            padding-top: 1rem;
        }

        #sidebar .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(194, 140, 13, 0.1);
        }

        #sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 0.3rem 1rem;
        }

        #sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        #sidebar .nav-link.active {
            background: var(--accent-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        #sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        #content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 2rem;
        }

        .navbar {
            margin-left: var(--sidebar-width);
            background: white !important;
            box-shadow: 0 2px 4px rgba(190, 125, 97, 0.1);
        }

        .navbar-brand, .nav-link {
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-3px);
        }
        
        .stats-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stats-card.products {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .stats-card.orders {
            background: linear-gradient(135deg, #2ecc71, #1abc9c);
        }
        
        .stats-card.revenue {
            background: linear-gradient(135deg, #f6d365, #fda085);
        }
        
        .stats-card .card-body {
            padding: 1.5rem;
        }
        
        .stats-card .card-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .stats-card .card-text {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stats-card:hover .stats-icon {
            transform: scale(1.1);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table thead th {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 1rem;
            border: none;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .btn-action {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-add {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.2);
        }
        
        .btn-add:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(183, 101, 24, 0.15);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            #content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-card .card-text {
                font-size: 1.5rem;
            }

            .table-responsive {
                border-radius: 15px;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Add Product Form Styles */
        .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #addProductForm {
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .custom-file-input:focus ~ .custom-file-label {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        #imagePreview {
            transition: all 0.3s ease;
        }

        #imagePreview img {
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-secondary {
            background-color: #95a5a6;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        /* Notification styles */
        .notifications-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(183, 101, 24, 0.15);
        }
        
        .notification {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 5px;
            text-align: left;
        }
        
        .notification.unread {
            background-color: #e6f7ff;
            border-left: 3px solid #1890ff;
        }
        
        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #f56565;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-item {
            position: relative;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                <i class="fas fa-store mr-2"></i>
                Seller Dashboard
            </h4>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="sellerdashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="productlisting.php">
                    <i class="fas fa-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="seller_bid_submission.php">
                    <i class="fas fa-gavel"></i>
                    Bid Art
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="sellerorders.php">
                    <i class="fas fa-shopping-cart"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="bid_details.php">
                    <i class="fas fa-users"></i>
                    Bid details
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="add_product.php">
                    <i class="fas fa-chart-bar"></i>
                    add product
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="total_earnings.php">
                    <i class="fas fa-money-bill-wave"></i>
                    Total Earnings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="notifications.php">
                    <i class="fas fa-bell"></i>
                    Notifications
                    <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <div id="wrapper">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-nav ml-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle mr-1"></i>
                            <?php echo htmlspecialchars($seller['name']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div id="content">
            <!-- Welcome Section -->
            <div class="welcome-section mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Welcome back, <?php echo htmlspecialchars($seller['name']); ?>!</h4>
                    <div class="date-time">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span id="currentDateTime"></span>
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="notifications-section mb-4">
                <h4 class="mb-3">Recent Notifications (<?php echo $unread_count; ?> Unread)</h4>
                
                <div class="notifications">
                    <?php
                    if ($notif_result->num_rows > 0) {
                        $counter = 0;
                        while ($notif = $notif_result->fetch_assoc()) {
                            $counter++;
                            if ($counter > 5) break; // Show only the 5 most recent notifications
                            
                            $class = $notif['is_read'] ? "notification" : "notification unread";
                            echo "<div class='$class'>";
                            echo "<p class='mb-1'>" . htmlspecialchars($notif['message']) . "</p>";
                            echo "<small class='text-muted'>Received on: " . $notif['created_at'] . "</small>";
                            echo "</div>";
                        }
                        
                        if ($unread_count > 0) {
                            echo "<div class='text-center mt-3'>";
                            echo "<form method='POST' action='mark_notifications.php'>";
                            echo "<button type='submit' class='btn btn-sm btn-primary'>Mark all as read</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                        
                        echo "<div class='text-center mt-2'>";
                        echo "<a href='notifications.php' class='text-primary'>View all notifications</a>";
                        echo "</div>";
                    } else {
                        echo "<p class='text-center'>No notifications yet.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="stats-card products">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Total Products</h5>
                                <p class="card-text text-white">
                                    <span id="totalProducts" class="counter">6</span>
                                </p>
                            </div>
                            <i class="fas fa-box stats-icon text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-card orders">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Pending Orders</h5>
                                <p class="card-text text-white">
                                    <span id="pendingOrders" class="counter">0</span>
                                </p>
                            </div>
                            <i class="fas fa-clock stats-icon text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-card revenue">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title text-white">Total Revenue</h5>
                                <p class="card-text text-white">
                                    $<span id="totalRevenue" class="counter">0</span>
                                </p>
                            </div>
                            <i class="fas fa-dollar-sign stats-icon text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add this after the Stats Cards Row and before the Products Table Section -->
            

            <!-- Products Table Section -->
           
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Animate numbers
            $('.counter').each(function() {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });

            // Update date and time
            function updateDateTime() {
                const now = new Date();
                const options = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                $('#currentDateTime').text(now.toLocaleDateString('en-US', options));
            }
            updateDateTime();
            setInterval(updateDateTime, 60000);

            // Add loading animation
            $(document).ajaxStart(function() {
                $('#loadingSpinner').show();
            }).ajaxStop(function() {
                $('#loadingSpinner').hide();
            });

            // Toggle Add Product Form
            $('#toggleAddProduct').click(function() {
                $('#addProductForm').slideToggle();
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Cancel Button
            $('#cancelAdd').click(function() {
                $('#addProductForm').slideUp();
                $('#toggleAddProduct i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('#productForm')[0].reset();
                $('#imagePreview').hide();
            });

            // Image Preview
            $('#productImage').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview img').attr('src', e.target.result);
                        $('#imagePreview').show();
                        $('.custom-file-label').text(file.name);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Form Submission
            $('#productForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: 'add_product.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            showToast('Product added successfully!', 'success');
                            $('#productForm')[0].reset();
                            $('#imagePreview').hide();
                            $('#addProductForm').slideUp();
                            location.reload(); // Reload to show new product
                        } else {
                            showToast(response.message || 'Error adding product', 'error');
                        }
                    },
                    error: function() {
                        hideLoading();
                        showToast('Error adding product', 'error');
                    }
                });
            });
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = `
                <div class="toast" role="alert" data-delay="3000">
                    <div class="toast-header bg-${type}">
                        <strong class="mr-auto text-white">Notification</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;
            
            $('.toast-container').append(toast);
            $('.toast').toast('show');
        }

        // Loading overlay functions
        function showLoading() {
            $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
        }

        function hideLoading() {
            $('.loading-overlay').remove();
        }
    </script>
</body>
</html>

<?php
$notif_stmt->close();
$count_stmt->close();
$conn->close();
?> 