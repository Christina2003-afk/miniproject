<?php
session_start();
require_once 'dbconfig.php';

// Fetch all orders
$query = "SELECT * FROM orders";
$result = $conn->query($query);

if (!$result) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(159, 108, 45);
            --secondary-color: rgb(135, 90, 30);
            --accent-color: rgb(184, 133, 70);
            --success-color: #4CAF50;
            --warning-color: #FFC107;
            --danger-color: #F44336;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-color: #e0e0e0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        /* Top Navigation */
        .top-nav {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(159, 108, 45, 0.3);
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .nav-actions {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        /* Main Content */
        .main-content {
            padding: 30px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 1.8rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title i {
            color: var(--accent-color);
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: rgba(159, 108, 45, 0.9);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        
        .icon-orders {
            background-color: rgba(159, 108, 45, 0.1);
            color: var(--accent-color);
        }
        
        .icon-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .icon-completed {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .icon-cancelled {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
        }
        
        .stat-info h3 {
            font-size: 1.5rem;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Filters and Search */
        .filters-bar {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: white;
            min-width: 150px;
        }
        
        .search-box {
            position: relative;
            flex-grow: 1;
            max-width: 300px;
        }
        
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 10px 10px 35px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.95rem;
        }
        
        /* Orders Grid */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        
        .order-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-id {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--warning-color);
        }
        
        .status-completed {
            background-color: rgba(76, 175, 80, 0.15);
            color: var(--success-color);
        }
        
        .status-cancelled {
            background-color: rgba(244, 67, 54, 0.15);
            color: var(--danger-color);
        }
        
        .order-body {
            padding: 15px;
        }
        
        .order-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .detail-label {
            color: #666;
            font-weight: 500;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .order-amount {
            font-weight: 700;
            color: var(--accent-color);
            font-size: 1.1rem;
        }
        
        .order-footer {
            padding: 12px 15px;
            background-color: #f9f9f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid var(--border-color);
        }
        
        .order-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .action-view {
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        .action-edit {
            background-color: var(--accent-color);
            color: white;
        }
        
        .action-delete {
            background-color: var(--danger-color);
            color: white;
        }
        
        .order-action:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-options {
                flex-direction: column;
                width: 100%;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .top-nav {
                padding: 10px 15px;
                flex-direction: column;
                gap: 10px;
            }
            
            .nav-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar instead of sidebar -->
    <div class="top-nav">
        <div class="nav-brand">
            <i class="fas fa-gavel"></i>
            <span>Pure Art </span>
        </div>
        <div class="nav-actions">
            <a href="admindash.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-shopping-bag"></i>
                Orders Management
            </h1>
        
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-orders">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <h3>6</h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>6</h3>
                    <p>Completed Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-cancelled">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Cancelled Orders</p>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="filters-bar">
            <div class="filter-options">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                
                <select class="filter-select" id="dateFilter">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search orders...">
            </div>
        </div>
        
        <!-- Orders Grid -->
        <div class="orders-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $status = strtolower($row['status']);
                $statusClass = 'status-' . $status;
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo htmlspecialchars($row['id']); ?></div>
                        <span class="order-status <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>
                    <div class="order-body">
                        <div class="order-detail">
                            <span class="detail-label">Product ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['product_id']); ?></span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Payment ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['payment_id']); ?></span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($row['created_at']); ?></span>
                        </div>
                        <div class="order-detail">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value order-amount">$<?php echo htmlspecialchars($row['amount']); ?></span>
                        </div>
                    </div>
                    <div class="order-footer">
                        <button class="order-action action-view" onclick="viewOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="order-action action-edit" onclick="editOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="order-action action-delete" onclick="deleteOrder(<?php echo $row['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Auction System. All rights reserved.</p>
    </div>

    <script>
        function viewOrder(id) {
            // View order details (you can implement a modal or redirect to a details page)
            alert("View Order ID: " + id);
        }
        
        function editOrder(id) {
            // Edit order functionality
            alert("Edit Order ID: " + id);
        }
        
        function deleteOrder(id) {
            if (confirm("Are you sure you want to delete this order?")) {
                // Delete order logic - you would typically make an AJAX request or redirect
                alert("Delete Order ID: " + id);
            }
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                const orderText = card.textContent.toLowerCase();
                if (orderText.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                const status = card.querySelector('.order-status').textContent.trim().toLowerCase();
                
                if (filterValue === '' || status === filterValue) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 