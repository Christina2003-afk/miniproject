<?php
// Include database connection
include("dbconfig.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["email"])) {
    header("Location: login.html");
    exit();
}

// Get user email from session
$email = $_SESSION["email"];

// Fetch user data
$query = "SELECT * FROM table_reg WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $role = $user['role'];
    $phone = $user['phone'] ?? '';
    $city = $user['city'] ?? '';
    $address = $user['address'] ?? '';
    $country = $user['country'] ?? '';
} else {
    header("Location: login.html");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_phone = $_POST['phone'];
    $new_city = $_POST['city'];
    $new_address = $_POST['address'];
    $new_country = $_POST['country'];

    $update_query = "UPDATE table_reg SET phone = ?, city = ?, address = ?, country = ? WHERE email = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssss", $new_phone, $new_city, $new_address, $new_country, $email);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile. Please try again.');</script>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Art Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f8f9fa; color: #333; line-height: 1.6; }
        
        /* Navigation */
        .nav-bar {
            background: #1a1a1a;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand {
            color: #fff;
            font-size: 1.5rem;
            text-decoration: none;
            font-weight: 600;
        }
        .nav-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 2rem;
            transition: color 0.3s;
        }
        .nav-links a:hover {
            color: #ffd700;
        }

        .container { 
            max-width: 1000px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }
        .profile-card { 
            background-color: #fff; 
            border-radius: 20px; 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden; 
        }
        .profile-header { 
            background: #edb354;
            color: white; 
            padding: 50px 40px; 
            text-align: center; 
        }
        .profile-img { 
            width: 150px; 
            height: 150px; 
            border-radius: 50%; 
            border: 8px solid rgba(255, 255, 255, 0.2);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background-color: #fff;
            margin: 0 auto; 
            transition: transform 0.3s;
        }
        .profile-img:hover {
            transform: scale(1.05);
        }
        .profile-img i { 
            font-size: 60px; 
            color: #2c3e50; 
        }
        .profile-name { 
            font-size: 28px; 
            font-weight: 600; 
            margin-top: 20px; 
        }
        .profile-role { 
            font-size: 16px; 
            background-color: rgba(255, 255, 255, 0.15); 
            padding: 8px 20px; 
            border-radius: 25px; 
            display: inline-block; 
            margin-top: 10px; 
            backdrop-filter: blur(5px);
        }
        .profile-details { 
            padding: 40px; 
            background: #fff;
        }
        .detail-item { 
            margin-bottom: 30px; 
            padding: 20px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .detail-label { 
            font-weight: 600; 
            font-size: 14px; 
            color: #2c3e50; 
            margin-bottom: 8px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value { 
            font-size: 16px; 
            color: #333; 
        }
        .form-group { 
            margin-bottom: 25px; 
        }
        .form-group label { 
            font-weight: 600; 
            display: block; 
            margin-bottom: 8px; 
            color: #2c3e50;
        }
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e9ecef; 
            border-radius: 10px; 
            font-size: 15px; 
            transition: all 0.3s ease; 
        }
        .form-group input:focus, .form-group textarea:focus { 
            border-color: #3498db; 
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none; 
        }
        .btn { 
            background: #EAA636;
            color: white; 
            padding: 14px 25px; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            display: block; 
            width: 100%; 
            text-align: center; 
            transition: all 0.3s ease; 
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .profile-actions { 
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 20px 40px 40px;
        }
        .action-btn { 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 10px; 
            transition: all 0.3s ease; 
            font-weight: 600;
            min-width: 150px;
        }
        .dashboard-btn {
            background-color: #2ecc71;
            color: white;
        }
        .dashboard-btn:hover {
            background-color: #27ae60;
        }
        .logout-btn { 
            background-color: #e74c3c; 
            color: white; 
        }
        .logout-btn:hover { 
            background-color: #c0392b; 
        }

        @media (max-width: 768px) {
            .container { padding: 15px; }
            .profile-header { padding: 30px 20px; }
            .profile-img { width: 120px; height: 120px; }
            .profile-name { font-size: 24px; }
            .profile-details { padding: 20px; }
            .nav-container { flex-direction: column; gap: 1rem; }
            .nav-links { display: flex; flex-wrap: wrap; justify-content: center; }
            .nav-links a { margin: 0.5rem; }
        }
    </style>
</head>
<body>
    <nav class="nav-bar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">Art Gallery</a>
            <div class="nav-links">
                
                <a href="index.php"><i class="fas fa-user"></i> Home</a>
                <a href="Collection.php"><i class="fas fa-images"></i> collection</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-img">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($name); ?></h1>
                <div class="profile-role"><?php echo htmlspecialchars($role); ?></div>
            </div>
            <div class="profile-details">
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-envelope"></i> Email Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($email); ?></div>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea name="address" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe"></i> Country</label>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($country); ?>" required>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
            <div class="profile-actions">
            <div class="text-center mt-3">
            <a href="winning_bids.php" class="btn btn-success">View Your Winning Bids</a>
        </div>
        
                <a href="logout.php" class="action-btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Add this new button -->
       
    </div>
</body>
</html>