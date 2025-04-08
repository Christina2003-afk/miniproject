<?php
include 'dbconfig.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

// Fetch shipping details from database
$shipping_query = "SELECT * FROM shipping_details ORDER BY created_at DESC";
$shipping_result = mysqli_query($conn, $shipping_query);

if (!$shipping_result) {
    echo "Error: " . mysqli_error($conn);
}

// Handle status update if requested
if (isset($_GET['update_id']) && isset($_GET['status'])) {
    $id = $_GET['update_id'];
    $status = $_GET['status'];
    
    $update_query = "UPDATE shipping_details SET status = '$status', updated_at = CURRENT_TIMESTAMP() WHERE id = $id";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        $success_message = "Shipping status updated successfully to " . $status . "!";
        // Refresh the shipping data
        $shipping_result = mysqli_query($conn, $shipping_query);
    } else {
        $error_message = "Error updating status: " . mysqli_error($conn);
    }
}

// Count shipping by status
$status_counts = [
    'total' => 0,
    'pending' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

// Get recent activity
$recent_activity = [];
$activity_query = "SELECT id, order_id, full_name, status, updated_at FROM shipping_details WHERE updated_at IS NOT NULL ORDER BY updated_at DESC LIMIT 5";
$activity_result = mysqli_query($conn, $activity_query);

if ($activity_result && mysqli_num_rows($activity_result) > 0) {
    while ($row = mysqli_fetch_assoc($activity_result)) {
        $recent_activity[] = $row;
    }
}

if ($shipping_result && mysqli_num_rows($shipping_result) > 0) {
    $status_counts['total'] = mysqli_num_rows($shipping_result);
    
    // Reset the result pointer
    mysqli_data_seek($shipping_result, 0);
    
    while ($row = mysqli_fetch_assoc($shipping_result)) {
        $status = isset($row['status']) ? strtolower($row['status']) : 'pending';
        if (isset($status_counts[$status])) {
            $status_counts[$status]++;
        }
    }
    
    // Reset the result pointer again for the main display
    mysqli_data_seek($shipping_result, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Overview - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        :root {
            --primary-color: rgb(234, 166, 54);
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --white-color: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: var(--white-color);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .header h1 {
            color: rgb(234, 166, 54);
            font-size: 24px;
            font-weight: 600;
        }
        
        .back-btn {
            background-color: var(--primary-color);
            color: var(--white-color);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #3a5fc9;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--white-color);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .icon {
            font-size: 24px;
            margin-bottom: 15px;
            display: inline-block;
            padding: 12px;
            border-radius: 50%;
            color: var(--white-color);
        }
        
        .stat-card.total .icon {
            background-color: var(--primary-color);
        }
        
        .stat-card.pending .icon {
            background-color: var(--warning-color);
        }
        
        .stat-card.shipped .icon {
            background-color: var(--info-color);
        }
        
        .stat-card.delivered .icon {
            background-color: var(--success-color);
        }
        
        .stat-card.cancelled .icon {
            background-color: var(--danger-color);
        }
        
        .stat-card h3 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: var(--secondary-color);
            font-size: 14px;
        }
        
        .dashboard-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .recent-activity {
            background-color: var(--white-color);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .recent-activity h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white-color);
            flex-shrink: 0;
        }
        
        .activity-icon.shipped {
            background-color: var(--info-color);
        }
        
        .activity-icon.delivered {
            background-color: var(--success-color);
        }
        
        .activity-icon.cancelled {
            background-color: var(--danger-color);
        }
        
        .activity-icon.pending {
            background-color: var(--warning-color);
        }
        
        .activity-content {
            flex-grow: 1;
        }
        
        .activity-content h4 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .activity-content p {
            font-size: 12px;
            color: var(--secondary-color);
        }
        
        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            background-color: var(--white-color);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            align-items: center;
        }
        
        .filter-section select, 
        .filter-section input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            flex-grow: 1;
            min-width: 150px;
            font-size: 14px;
        }
        
        .filter-section button {
            background-color: var(--primary-color);
            color: var(--white-color);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-section button:hover {
            background-color: #3a5fc9;
        }
        
        .shipping-table {
            background-color: var(--white-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .table-header {
            background-color: var(--primary-color);
            color: var(--white-color);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .export-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--white-color);
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .export-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 14px;
            text-transform: uppercase;
        }
        
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .shipped {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .deliver-btn {
            background-color: var(--success-color);
            color: var(--white-color);
        }
        
        .deliver-btn:hover {
            background-color: #218838;
        }
        
        .ship-btn {
            background-color: var(--info-color);
            color: var(--white-color);
        }
        
        .ship-btn:hover {
            background-color: #138496;
        }
        
        .cancel-btn {
            background-color: var(--danger-color);
            color: var(--white-color);
        }
        
        .cancel-btn:hover {
            background-color: #c82333;
        }
        
        .view-btn {
            background-color: var(--secondary-color);
            color: var(--white-color);
        }
        
        .view-btn:hover {
            background-color: #5a6268;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .pagination a {
            padding: 8px 15px;
            background-color: var(--white-color);
            color: var(--primary-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .pagination a.active,
        .pagination a:hover {
            background-color: var(--primary-color);
            color: var(--white-color);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: var(--white-color);
            margin: 10% auto;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 80%;
            max-width: 600px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h2 {
            font-size: 20px;
            color: var(--dark-color);
        }
        
        .close {
            color: var(--secondary-color);
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--dark-color);
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .shipment-detail {
            margin-bottom: 15px;
        }
        
        .shipment-detail h3 {
            font-size: 16px;
            margin-bottom: 5px;
            color: var(--secondary-color);
        }
        
        .shipment-detail p {
            font-size: 16px;
            color: var(--dark-color);
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .progress-tracker {
            margin: 30px 0;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 30px;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #eee;
            z-index: 1;
        }
        
        .step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        
        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .step.active .step-icon {
            background-color: var(--primary-color);
            color: var(--white-color);
        }
        
        .step.completed .step-icon {
            background-color: var(--success-color);
            color: var(--white-color);
        }
        
        .step-label {
            font-size: 12px;
            color: var(--secondary-color);
        }
        
        .step.active .step-label,
        .step.completed .step-label {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-row {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .shipping-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shipping-fast"></i> Shipment Overview</h1>
            <a href="admindash.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-stats">
            <div class="stat-card total">
                <div class="icon"><i class="fas fa-box"></i></div>
                <h3><?php echo $status_counts['total']; ?></h3>
                <p>Total Shipments</p>
            </div>
            <div class="stat-card pending">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <h3><?php echo $status_counts['pending']; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card shipped">
                <div class="icon"><i class="fas fa-truck"></i></div>
                <h3><?php echo $status_counts['shipped']; ?></h3>
                <p>Shipped</p>
            </div>
            <div class="stat-card delivered">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <h3><?php echo $status_counts['delivered']; ?></h3>
                <p>Delivered</p>
            </div>
            <div class="stat-card cancelled">
                <div class="icon"><i class="fas fa-times-circle"></i></div>
                <h3><?php echo $status_counts['cancelled']; ?></h3>
                <p>Cancelled</p>
            </div>
        </div>
        
        <div class="dashboard-row">
            <div class="filter-section">
                <select id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <input type="text" id="orderSearch" placeholder="Search by Order ID or Customer Name">
                <button id="filterBtn"><i class="fas fa-filter"></i> Apply Filter</button>
                <button id="resetBtn"><i class="fas fa-sync-alt"></i> Reset</button>
            </div>
            
            <div class="recent-activity">
                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                <ul class="activity-list">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon <?php echo strtolower($activity['status']); ?>">
                                    <?php if ($activity['status'] == 'Shipped'): ?>
                                        <i class="fas fa-truck"></i>
                                    <?php elseif ($activity['status'] == 'Delivered'): ?>
                                        <i class="fas fa-check"></i>
                                    <?php elseif ($activity['status'] == 'Cancelled'): ?>
                                        <i class="fas fa-times"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content">
                                    <h4>Order #<?php echo $activity['order_id']; ?> - <?php echo $activity['status']; ?></h4>
                                    <p><?php echo $activity['full_name']; ?> • <?php echo date('M d, g:i A', strtotime($activity['updated_at'])); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <h4>No recent activity</h4>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="shipping-table">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Shipping Details</h2>
                <button class="export-btn" onclick="exportToCSV()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Shipping Method</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($shipping_result) > 0) {
                            while ($row = mysqli_fetch_assoc($shipping_result)) { 
                        ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['order_id']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td>
                                    <strong>Email:</strong> <?php echo $row['email']; ?><br>
                                    <strong>Phone:</strong> <?php echo $row['phone']; ?>
                                </td>
                                <td>
                                    <?php echo $row['address']; ?><br>
                                    <?php echo $row['city']; ?>, <?php echo $row['pincode']; ?>
                                </td>
                                <td><?php echo $row['shipping_method']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo isset($row['status']) ? strtolower($row['status']) : 'pending'; ?>">
                                        <?php echo isset($row['status']) ? $row['status'] : 'Pending'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button class="action-btn view-btn" onclick="viewShipment(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <?php if (!isset($row['status']) || $row['status'] !== 'Shipped'): ?>
                                        <button class="action-btn ship-btn" onclick="updateStatus(<?php echo $row['id']; ?>, 'Shipped')">
                                            <i class="fas fa-truck"></i> Ship
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (!isset($row['status']) || $row['status'] !== 'Delivered'): ?>
                                        <button class="action-btn deliver-btn" onclick="updateStatus(<?php echo $row['id']; ?>, 'Delivered')">
                                            <i class="fas fa-check"></i> Deliver
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (!isset($row['status']) || ($row['status'] !== 'Cancelled' && $row['status'] !== 'Delivered')): ?>
                                        <button class="action-btn cancel-btn" onclick="updateStatus(<?php echo $row['id']; ?>, 'Cancelled')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='9' style='text-align: center; padding: 30px;'>No shipping details found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
            <a href="#">&laquo;</a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">&raquo;</a>
        </div>
    </div>
    
    <!-- Shipment Details Modal -->
    <div id="shipmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-box"></i> Shipment Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="progress-tracker">
                    <div class="progress-steps">
                        <div class="step completed">
                            <div class="step-icon"><i class="fas fa-box"></i></div>
                            <div class="step-label">Order Placed</div>
                        </div>
                        <div class="step completed">
                            <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                            <div class="step-label">Processing</div>
                        </div>
                        <div class="step" id="step-shipped">
                            <div class="step-icon"><i class="fas fa-truck"></i></div>
                            <div class="step-label">Shipped</div>
                        </div>
                        <div class="step" id="step-delivered">
                            <div class="step-icon"><i class="fas fa-home"></i></div>
                            <div class="step-label">Delivered</div>
                        </div>
                    </div>
                </div>
                
                <div class="shipment-details">
                    <div class="detail-row">
                        <div class="detail-col">
                            <h3>Order Information</h3>
                            <p><strong>Order ID:</strong> <span id="modal-order-id"></span></p>
                            <p><strong>Date Placed:</strong> <span id="modal-date"></span></p>
                            <p><strong>Status:</strong> <span id="modal-status"></span></p>
                            <p><strong>Shipping Method:</strong> <span id="modal-shipping-method"></span></p>
                        </div>
                        <div class="detail-col">
                            <h3>Customer Information</h3>
                            <p><strong>Name:</strong> <span id="modal-name"></span></p>
                            <p><strong>Email:</strong> <span id="modal-email"></span></p>
                            <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-col">
                            <h3>Shipping Address</h3>
                            <p id="modal-address"></p>
                            <p><span id="modal-city"></span>, <span id="modal-pincode"></span></p>
                            <p><span id="modal-country"></span></p>
                        </div>
                        <div class="detail-col">
                            <h3>Actions</h3>
                            <div class="modal-actions" id="modal-actions">
                                <!-- Actions will be added dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Modal functionality
        const modal = document.getElementById('shipmentModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        function viewShipment(id) {
            // In a real application, you would fetch the shipment details via AJAX
            // For this example, we'll use the data already in the table
            const row = document.querySelector(`tbody tr td:first-child:contains('${id}')`).parentNode;
            
            if (row) {
                const orderId = row.cells[1].textContent;
                const name = row.cells[2].textContent;
                const email = row.cells[3].textContent.split('Email:')[1].split('Phone:')[0].trim();
                const phone = row.cells[3].textContent.split('Phone:')[1].trim();
                const address = row.cells[4].textContent.split(',')[0].trim();
                const city = row.cells[4].textContent.split(',')[1].split(',')[0].trim();
                const pincode = row.cells[4].textContent.split(',')[1].split(',')[1].trim();
                const shippingMethod = row.cells[5].textContent;
                const status = row.cells[6].textContent.trim();
                const date = row.cells[7].textContent;
                
                // Update modal content
                document.getElementById('modal-order-id').textContent = orderId;
                document.getElementById('modal-date').textContent = date;
                document.getElementById('modal-status').textContent = status;
                document.getElementById('modal-shipping-method').textContent = shippingMethod;
                document.getElementById('modal-name').textContent = name;
                document.getElementById('modal-email').textContent = email;
                document.getElementById('modal-phone').textContent = phone;
                document.getElementById('modal-address').textContent = address;
                document.getElementById('modal-city').textContent = city;
                document.getElementById('modal-pincode').textContent = pincode;
                document.getElementById('modal-country').textContent = 'India';
                
                // Update progress tracker
                document.querySelectorAll('.progress-steps .step').forEach(step => {
                    step.classList.remove('completed', 'current');
                });
                
                document.querySelectorAll('.progress-steps .step')[0].classList.add('completed');
                document.querySelectorAll('.progress-steps .step')[1].classList.add('completed');
                
                if (status === 'Shipped' || status === 'Delivered') {
                    document.getElementById('step-shipped').classList.add('completed');
                }
                
                if (status === 'Delivered') {
                    document.getElementById('step-delivered').classList.add('completed');
                }
                
                // Add action buttons
                const actionsContainer = document.getElementById('modal-actions');
                actionsContainer.innerHTML = '';
                
                if (status !== 'Shipped') {
                    const shipBtn = document.createElement('button');
                    shipBtn.className = 'action-btn ship-btn';
                    shipBtn.innerHTML = '<i class="fas fa-truck"></i> Ship';
                    shipBtn.onclick = function() {
                        updateStatus(id, 'Shipped');
                    };
                    actionsContainer.appendChild(shipBtn);
                }
                
                if (status !== 'Delivered') {
                    const deliverBtn = document.createElement('button');
                    deliverBtn.className = 'action-btn deliver-btn';
                    deliverBtn.innerHTML = '<i class="fas fa-check"></i> Deliver';
                    deliverBtn.onclick = function() {
                        updateStatus(id, 'Delivered');
                    };
                    actionsContainer.appendChild(deliverBtn);
                }
                
                if (status !== 'Cancelled' && status !== 'Delivered') {
                    const cancelBtn = document.createElement('button');
                    cancelBtn.className = 'action-btn cancel-btn';
                    cancelBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
                    cancelBtn.onclick = function() {
                        updateStatus(id, 'Cancelled');
                    };
                    actionsContainer.appendChild(cancelBtn);
                }
                
                // Show modal
                modal.style.display = 'block';
            }
        }
        
        function updateStatus(id, status) {
            if (confirm("Are you sure you want to mark this shipment as " + status + "?")) {
                window.location.href = "shipping_overview.php?update_id=" + id + "&status=" + status;
            }
        }
        
        // Filter functionality
        document.getElementById('filterBtn').addEventListener('click', function() {
            applyFilters();
        });
        
        document.getElementById('resetBtn').addEventListener('click', function() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('orderSearch').value = '';
            applyFilters();
        });
        
        function applyFilters() {
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const searchFilter = document.getElementById('orderSearch').value.toLowerCase();
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const statusCell = row.querySelector('td:nth-child(7) .status-badge');
                const orderIdCell = row.querySelector('td:nth-child(2)');
                const nameCell = row.querySelector('td:nth-child(3)');
                
                const statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
                const orderId = orderIdCell ? orderIdCell.textContent.trim().toLowerCase() : '';
                const name = nameCell ? nameCell.textContent.trim().toLowerCase() : '';
                
                const statusMatch = statusFilter === '' || statusText === statusFilter;
                const searchMatch = searchFilter === '' || 
                                   orderId.includes(searchFilter) || 
                                   name.includes(searchFilter);
                
                if (statusMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Add event listeners for enter key in search field
        document.getElementById('orderSearch').addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                applyFilters();
            }
        });
        
        // Export to CSV functionality
        function exportToCSV() {
            const table = document.querySelector('table');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Get the text content and clean it up
                    let data = cols[j].textContent.replace(/(\r\n|\n|\r)/gm, '').trim();
                    
                    // Remove multiple spaces
                    data = data.replace(/\s+/g, ' ');
                    
                    // Escape quotes
                    data = data.replace(/"/g, '""');
                    
                    // Add quotes around the data
                    row.push('"' + data + '"');
                }
                csv.push(row.join(','));
            }
            
            // Create a CSV file and download it
            const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
            const downloadLink = document.createElement('a');
            
            downloadLink.download = 'shipping_details_' + new Date().toISOString().slice(0,10) + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
        
        // Helper function for querySelector
        Element.prototype.contains = function(text) {
            return this.textContent.trim() === text.trim();
        };
    </script>
</body>
</html>