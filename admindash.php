<?php
include("dbconfig.php");
session_start();

// Fetch all users
$query = "SELECT reg_id, name, email, status FROM table_reg";
$result = mysqli_query($conn, $query);

// Check for query errors
if (!$result) {
    echo "Error: " . mysqli_error($conn);
}

// Add this after the first query at the top of the file
$seller_query = "SELECT reg_id, name, email, status FROM table_reg WHERE role='seller'";
$seller_result = mysqli_query($conn, $seller_query);

if (!$seller_result) {
    echo "Error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4a90e2;
            --sidebar-width: 350px;
            --header-height: 60px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            background-color: #f5f6fa;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            background-color: rgb(159, 108, 45);
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .logo {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .logo h2 {
            margin-left: 10px;
            font-size: 1.2rem;
            color: #fcfafa;
        }

        .sidebar.collapsed .logo h2 {
            display: none;
        }

        .nav-links {
            padding: 20px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fcfafa;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: #f5f6fa;
            color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .nav-link span {
            margin-left: 15px;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            min-width: 0;
        }

        .header {
            height: var(--header-height);
            background: white;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: #f5f6fa;
            border-radius: 8px;
            padding: 8px 15px;
            width: 300px;
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            margin-right: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 15px;
            height: 15px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 50px;
        }

        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .summary-box-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .summary-box i {
            font-size: 24px;
            padding: 10px;
            border-radius: 8px;
            background: #f5f6fa;
        }

        .summary-box h3 {
            font-size: 24px;
            margin: 10px 0;
        }

        .summary-box p {
            color: #666;
            font-size: 14px;
        }

        .trend {
            font-size: 12px;
        }

        .trend.up {
            color: #2ecc71;
        }

        .trend.down {
            color: #e74c3c;
        }

        /* User Table */
        .user-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            color: #666;
            font-weight: 600;
        }

        .approve-btn {
            background: #e8f5e9;
            color: #2ecc71;
            border: none;
            padding: 5px 12px;
            border-radius: 15px;
            cursor: pointer;
            margin-right: 5px;
        }

        .reject-btn {
            background: #ffebee;
            color: #e74c3c;
            border: none;
            padding: 5px 12px;
            border-radius: 15px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar .logo h2,
            .sidebar .nav-link span {
                display: none;
            }

            .search-bar {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="logo">
                
                <img src="img/logoart.png" alt="Logo">
                <h2>Admin Dashboard</h2>
            </div>
            <div class="nav-links">
                <a href="#" class="nav-link" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><h2>Dashboard</h2></span>
                </a>
                <a href="#" class="nav-link" data-section="users">
                    <i class="fas fa-users"></i>
                    <span><h2>Manage Users</h2></span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-store"></i>
                    <span><h2>Seller Management</h2></span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-gavel"></i>
                    <span><h2>Bid Management</h2></span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-plus-circle"></i>
                    <span><h2>Product</h2></span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-tags"></i>
                    <span><h2>Categories</h2></span>
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span><h2>Reports</h2></span>
                </a>
            </div>
        </div>
        <div class="main-content">
            <div class="header">
                <div class="left-section">
                    <button id="toggle-sidebar" style="border: none; background: none; cursor: pointer; margin-right: 15px;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-bar">
                        <input type="text" placeholder="Search...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="user-profile">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <img src="/api/placeholder/35/35" alt="Profile" class="user-avatar">
                    <a href="logout.php" class="logout-btn" style="text-decoration: none; color: #e74c3c; padding: 8px 15px; border-radius: 5px; background: #ffebee; margin-left: 15px;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <h2>Welcome Admin </h2>
                <div class="summary-boxes">
                    <div class="summary-box">
                        <div class="summary-box-header">
                            <i class="fas fa-user-plus" style="color: #4a90e2;"></i>
                        </div>
                        <h3>10</h3>
                        <p>New Users</p>
                        <span class="trend up">+12.5% from last month</span>
                    </div>
                    <div class="summary-box">
                        <div class="summary-box-header">
                            <i class="fas fa-shopping-cart" style="color: #2ecc71;"></i>
                        </div>
                        <h3>0</h3>
                        <p>Total Orders</p>
                        <span class="trend up">+8.2% from last month</span>
                    </div>
                    <div class="summary-box">
                        <div class="summary-box-header">
                            <i class="fas fa-box" style="color: #9b59b6;"></i>
                        </div>
                        <h3>25</h3>
                        <p>Available Products</p>
                        <span class="trend down">-3.1% from last month</span>
                    </div>
                </div>
            </div>

            <!-- Users Table Content (Initially Hidden) -->
            <div id="usersContent" style="display: none; padding: 20px;">
                <div class="user-table">
                    <div class="table-header">
                        <h2>Recent Users</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                               
                                <th>User</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { 
                            ?>
                                <tr>
                                    
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'Active'): ?>
                                            <button class="reject-btn" onclick="updateStatus(<?php echo $row['reg_id']; ?>, 'Inactive'); $(this).closest('tr').find('.status-badge').removeClass('active').addClass('inactive').text('Inactive');">
                                                <i class="fas fa-user-times"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button class="approve-btn" onclick="updateStatus(<?php echo $row['reg_id']; ?>, 'Active'); $(this).closest('tr').find('.status-badge').removeClass('inactive').addClass('active').text('Active');">
                                                <i class="fas fa-user-check"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Seller Management Content (Initially Hidden) -->
            <div id="sellerContent" style="display: none; padding: 20px;">
                <div class="user-table">
                    <div class="table-header">
                        <h2>Seller Management</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                               
                                <th>Seller Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($seller_result) > 0) {
                                while ($row = mysqli_fetch_assoc($seller_result)) { 
                            ?>
                                <tr>
                                    
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                    <td>
                                        <button class="approve-btn" onclick="updateStatus(<?php echo $row['reg_id']; ?>, 'Active')">Approve</button>
                                        
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5'>No sellers found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.getElementById('toggle-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Get all sidebar nav links
        const navLinks = document.querySelectorAll('.nav-link');
        
        // Get content sections
        const dashboardContent = document.querySelector('.dashboard-content');
        const usersContent = document.getElementById('usersContent');
        const sellerContent = document.getElementById('sellerContent');
        
        // Add click event listeners to each nav link
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const linkText = this.querySelector('span').textContent.trim();
                
                if (linkText === 'Dashboard') {
                    dashboardContent.style.display = 'block';
                    usersContent.style.display = 'none';
                    sellerContent.style.display = 'none';
                } 
                else if (linkText === 'Manage Users') {
                    dashboardContent.style.display = 'none';
                    usersContent.style.display = 'block';
                    sellerContent.style.display = 'none';
                }
                else if (linkText === 'Seller Management') {
                    dashboardContent.style.display = 'none';
                    usersContent.style.display = 'none';
                    sellerContent.style.display = 'block';
                }
                else if (linkText === 'Categories') {
                    window.location.href = 'admincat.php';
                }
                else if (linkText === 'Product') {
                    window.location.href = 'productmang.php';
                }
            });
        });

        function updateStatus(userId, status) {
            if (confirm("Are you sure you want to change the status?")) {
                window.location.href = "update_status.php?reg_id=" + userId + "&status=" + status;
            }
        }
    </script>
</body>
</html>