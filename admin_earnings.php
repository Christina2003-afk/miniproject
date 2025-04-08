<?php
include("dbconfig.php");
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['email'];
$role_query = "SELECT role FROM table_reg WHERE email = ?";
$stmt = mysqli_prepare($conn, $role_query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$role_result = mysqli_stmt_get_result($stmt);

if ($role_result && $row = mysqli_fetch_assoc($role_result)) {
    if ($row['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Earnings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #E67E22;
            --secondary-color: #d35400;
            --text-color: #2C3E50;
            --background-color: #f8f9fa;
            --card-shadow: 0 8px 24px rgba(230, 126, 34, 0.1);
        }

        body {
            background-color: var(--background-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
        }

        .dashboard-header {
            background: rgb(159, 108, 45);
            padding: 2rem 0;
            margin-bottom: 2rem;
            color: white;
            box-shadow: var(--card-shadow);
        }

        .earnings-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
            border-top: 4px solid #E67E22;
        }

        .earnings-card:hover {
            transform: translateY(-5px);
        }

        .total-amount {
            font-size: 3rem;
            font-weight: 700;
            color: #E67E22;
            margin-bottom: 1rem;
        }

        .seller-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .seller-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(230, 126, 34, 0.15);
        }

        .seller-name {
            color: var(--text-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .seller-earnings {
            color: #E67E22;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .admin-share {
            color: #d35400;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .back-button {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.8);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-button:hover {
            background: white;
            color: #E67E22;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem 0;
            }
            .total-amount {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <a href="admindash.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <h1 class="mt-4 mb-0">Admin Revenue Share</h1>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="earnings-card">
                    <h2>Total Admin Share (25%)</h2>
                    <?php
                    $total_query = "SELECT SUM(amount) as total FROM orders WHERE status = 'paid'";
                    $total_result = mysqli_query($conn, $total_query);
                    $total = mysqli_fetch_assoc($total_result);
                    $admin_total = ($total['total'] ?? 0) * 0.25;
                    ?>
                    <div class="total-amount">₹<?php echo number_format($admin_total, 2); ?></div>
                   
                </div>
            </div>
        </div>

        
                    
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 