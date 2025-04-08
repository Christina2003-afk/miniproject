<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
// Add error handling for user array elements
$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Unknown';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'No email';
$picture = isset($user['picture']) ? htmlspecialchars($user['picture']) : 'default-avatar.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0 auto;
            padding: 20px;
        }
        img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 10px;
        }
        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo $name; ?>!</h2>
    <img src="<?php echo $picture; ?>" alt="Profile Picture">
    <p>Email: <?php echo $email; ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>
