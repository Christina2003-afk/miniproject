<?php
session_start();
require_once 'dbconfig.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION['email'];

// Fetch user details
$user_query = "SELECT * FROM seller_registration WHERE email = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Process settings update
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $bio = $_POST['bio'];
        
        $update_query = "UPDATE seller_registration SET full_name = ?, phone = ?, bio = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssss", $full_name, $phone, $bio, $user_email);
        
        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $messageType = "success";
            
            // Refresh user data
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user = $user_result->fetch_assoc();
        } else {
            $message = "Error updating profile. Please try again.";
            $messageType = "error";
        }
    } else if (isset($_POST['change_password'])) {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $password_query = "UPDATE seller_registration SET password = ? WHERE email = ?";
                $password_stmt = $conn->prepare($password_query);
                $password_stmt->bind_param("ss", $hashed_password, $user_email);
                
                if ($password_stmt->execute()) {
                    $message = "Password changed successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error changing password. Please try again.";
                    $messageType = "error";
                }
            } else {
                $message = "New passwords do not match.";
                $messageType = "error";
            }
        } else {
            $message = "Current password is incorrect.";
            $messageType = "error";
        }
    } else if (isset($_POST['notification_preferences'])) {
        // Handle notification preferences
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $bid_notifications = isset($_POST['bid_notifications']) ? 1 : 0;
        $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;
        $auction_notifications = isset($_POST['auction_notifications']) ? 1 : 0;
        
        // Check if preferences exist
        $pref_check = "SELECT * FROM user_preferences WHERE email = ?";
        $pref_check_stmt = $conn->prepare($pref_check);
        $pref_check_stmt->bind_param("s", $user_email);
        $pref_check_stmt->execute();
        $pref_result = $pref_check_stmt->get_result();
        
        if ($pref_result->num_rows > 0) {
            // Update existing preferences
            $pref_query = "UPDATE user_preferences SET 
                        email_notifications = ?, 
                        bid_notifications = ?, 
                        message_notifications = ?, 
                        auction_notifications = ? 
                        WHERE email = ?";
        } else {
            // Insert new preferences
            $pref_query = "INSERT INTO user_preferences 
                        (email, email_notifications, bid_notifications, message_notifications, auction_notifications) 
                        VALUES (?, ?, ?, ?, ?)";
        }
        
        $pref_stmt = $conn->prepare($pref_query);
        $pref_stmt->bind_param("iiiss", $email_notifications, $bid_notifications, $message_notifications, $auction_notifications, $user_email);
        
        if ($pref_stmt->execute()) {
            $message = "Notification preferences updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating notification preferences. Please try again.";
            $messageType = "error";
        }
    }
}

// Fetch user preferences if they exist
$preferences = array(
    'email_notifications' => 1,
    'bid_notifications' => 1,
    'message_notifications' => 1,
    'auction_notifications' => 1,
    'dark_mode' => 0
);

$pref_query = "SELECT * FROM user_preferences WHERE email = ?";
$pref_stmt = $conn->prepare($pref_query);
$pref_stmt->bind_param("s", $user_email);
$pref_stmt->execute();
$pref_result = $pref_stmt->get_result();

if ($pref_result->num_rows > 0) {
    $pref_data = $pref_result->fetch_assoc();
    $preferences['email_notifications'] = $pref_data['email_notifications'];
    $preferences['bid_notifications'] = $pref_data['bid_notifications'];
    $preferences['message_notifications'] = $pref_data['message_notifications'];
    $preferences['auction_notifications'] = $pref_data['auction_notifications'];
    $preferences['dark_mode'] = $pref_data['dark_mode'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Art Auction Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B4513; /* SaddleBrown */
            --secondary-color: #CD853F; /* Peru - a lighter brown */
            --accent-color: #D2691E; /* Chocolate - for accents */
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --text-color: #333333;
            --text-light: #777777;
            --border-color: #e0e0e0;
            --bg-light: #f8f9fa;
            --bg-dark: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
            text-align: center;
            background: rgb(177, 144, 53);
            color: white;
            border-radius: var(--border-radius);
            padding: 25px 30px;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: white;
        }
        
        .settings-container {
            display: flex;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .settings-sidebar {
            width: 250px;
            background: rgb(177, 144, 53);
            padding: 2rem 0;
        }
        
        .user-profile {
            text-align: center;
            padding: 0 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #fff;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }
        
        .user-email {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        .settings-menu {
            list-style: none;
        }
        
        .settings-menu-item {
            padding: 0.75rem 2rem;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .settings-menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .settings-menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .settings-menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-left: 4px solid var(--secondary-color);
        }
        
        .settings-content {
            flex: 1;
            padding: 2rem;
        }
        
        .settings-panel {
            display: none;
        }
        
        .settings-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-section-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            color: var(--text-color);
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #bd2130;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }
        
        .btn-outline:hover {
            background-color: #f0f0f0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .switch-container {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .switch-label {
            flex: 1;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        input:focus + .slider {
            box-shadow: 0 0 1px var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .two-factor-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            background-color: #f0f4f8;
            border-radius: var(--border-radius);
            margin-top: 1rem;
        }
        
        .qr-placeholder {
            width: 180px;
            height: 180px;
            background-color: #ddd;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #777;
            text-align: center;
        }
        
        .verification-code {
            display: flex;
            margin-top: 1rem;
        }
        
        .verification-input {
            width: 40px;
            height: 45px;
            text-align: center;
            margin: 0 5px;
            font-size: 1.2rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .delete-account-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: rgba(220, 53, 69, 0.05);
            border-radius: var(--border-radius);
            border: 1px solid rgba(220, 53, 69, 0.1);
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }
        
        .payment-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 40px;
            text-align: center;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-type {
            font-weight: 500;
        }
        
        .payment-info {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .payment-actions {
            display: flex;
            gap: 10px;
        }
        
        .payment-btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Design */
        @media (max-width: 991px) {
            .settings-container {
                flex-direction: column;
            }
            
            .settings-sidebar {
                width: 100%;
                padding: 1rem 0;
            }
            
            .user-profile {
                padding: 0 1rem 1rem;
                margin-bottom: 0.5rem;
            }
            
            .profile-image {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .settings-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }
            
            .settings-menu-item {
                padding: 0.5rem 1rem;
            }
            
            .verification-code {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Account Settings</h1>
            <p>Manage your account preferences and information</p>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="settings-container">
            <div class="settings-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                
                <ul class="settings-menu">
                    <li class="settings-menu-item active" data-panel="profile">
                        <i class="fas fa-user-circle"></i> Profile
                    </li>
                    <li class="settings-menu-item" data-panel="security">
                        <i class="fas fa-shield-alt"></i> Security
                    </li>
                    <li class="settings-menu-item" data-panel="notifications">
                        <i class="fas fa-bell"></i> Notifications
                    </li>
                    
                    <li class="settings-menu-item" data-panel="privacy">
                        <i class="fas fa-lock"></i> Privacy
                    </li>
                    <li class="settings-menu-item" data-panel="account">
                        <i class="fas fa-user-cog"></i> Account
                    </li>
                </ul>
            </div>
            
            <div class="settings-content">
                <!-- Profile Settings Panel -->
                <div class="settings-panel active" id="profile-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Profile Information</h2>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" disabled>
                                <small>Your email address cannot be changed</small>
                            </div>
                            
                           
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" class="form-control" placeholder="Tell collectors about yourself"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
                
                <!-- Security Settings Panel -->
                <div class="settings-panel" id="security-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Password</h2>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                    
                    <div class="settings-section">
                        <h2 class="settings-section-title">Two-Factor Authentication</h2>
                        <p>Enhance your account security by enabling two-factor authentication</p>
                        
                        <div class="switch-container">
                            <span class="switch-label">Enable Two-Factor Authentication</span>
                            <label class="switch">
                                <input type="checkbox" id="two_factor">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="two-factor-container" style="display: none;">
                            <div class="qr-placeholder">QR Code will appear here</div>
                            <p>Scan this QR code with your authenticator app</p>
                            <p>Enter the 6-digit verification code below:</p>
                            
                            <div class="verification-code">
                                <input type="text" class="verification-input" maxlength="1">
                                <input type="text" class="verification-input" maxlength="1">
                                <input type="text" class="verification-input" maxlength="1">
                                <input type="text" class="verification-input" maxlength="1">
                                <input type="text" class="verification-input" maxlength="1">
                                <input type="text" class="verification-input" maxlength="1">
                            </div>
                            
                            <button type="button" class="btn btn-primary" style="margin-top: 1rem;">Verify</button>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h2 class="settings-section-title">Login Sessions</h2>
                        <p>These are devices that have logged into your account</p>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-type">Chrome on Windows</div>
                                <div class="payment-info">Active now • Your current session</div>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-type">Safari on iPhone</div>
                                <div class="payment-info">Last active: 2 days ago</div>
                            </div>
                            <div class="payment-actions">
                                <button class="btn btn-outline payment-btn">Log Out</button>
                            </div>
                        </div>
                        
                        <button class="btn btn-danger">Log Out All Other Sessions</button>
                    </div>
                </div>
                
                <!-- Notifications Settings Panel -->
                <div class="settings-panel" id="notifications-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Notification Preferences</h2>
                        <form action="" method="POST">
                            <div class="switch-container">
                                <span class="switch-label">Email Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="switch-container">
                                <span class="switch-label">New Bid Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="bid_notifications" <?php echo $preferences['bid_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="switch-container">
                                <span class="switch-label">Message Notifications</span>
                                <label class="switch">
                                    <input type="checkbox" name="message_notifications" <?php echo $preferences['message_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="switch-container">
                                <span class="switch-label">Auction Status Updates</span>
                                <label class="switch">
                                    <input type="checkbox" name="auction_notifications" <?php echo $preferences['auction_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <button type="submit" name="notification_preferences" class="btn btn-primary">Save Preferences</button>
                        </form>
                    </div>
                </div>
                
                <!-- Payment Methods Panel -->
                <div class="settings-panel" id="payment-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Payment Methods</h2>
                        <p>Manage your payment methods for receiving payments from auctions</p>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fab fa-cc-visa"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-type">Visa ending in 4242</div>
                                <div class="payment-info">Expires 12/25</div>
                            </div>
                            <div class="payment-actions">
                                <button class="btn btn-outline payment-btn">Edit</button>
                                <button class="btn btn-danger payment-btn">Remove</button>
                            </div>
                        </div>
                        
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fab fa-paypal"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-type">PayPal</div>
                                <div class="payment-info">Connected to <?php echo htmlspecialchars($user_email); ?></div>
                            </div>
                            <div class="payment-actions">
                                <button class="btn btn-danger payment-btn">Disconnect</button>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary" style="margin-top: 1rem;">Add Payment Method</button>
                    </div>
                    
                    <div class="settings-section">
                        <h2 class="settings-section-title">Payout Information</h2>
                        <p>Your earnings from auctions will be sent to your preferred payout method</p>
                        
                        <form action="">
                            <div class="form-group">
                                <label for="payout_method" class="form-label">Preferred Payout Method</label>
                                <select id="payout_method" class="form-control">
                                    <option value="bank">Bank Transfer</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                            
                            <div id="bank-details">
                                <div class="form-group">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" id="account_number" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="routing_number" class="form-label">Routing Number</label>
                                    <input type="text" id="routing_number" class="form-control">
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary">Save Payout Information</button>
                        </form>
                    </div>
                </div>
                
                <!-- Appearance Settings Panel -->
                <div class="settings-panel" id="appearance-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Appearance</h2>
                        
                        <div class="switch-container">
                            <span class="switch-label">Dark Mode</span>
                            <label class="switch">
                                <input type="checkbox" id="dark_mode" <?php echo $preferences['dark_mode'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label for="font_size" class="form-label">Font Size</label>
                            <select id="font_size" class="form-control">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary">Save Appearance Settings</button>
                    </div>
                </div>
                
                <!-- Privacy Settings Panel -->
                <div class="settings-panel" id="privacy-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Privacy Settings</h2>
                        
                        <div class="switch-container">
                            <span class="switch-label">Show my profile to other users</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="switch-container">
                            <span class="switch-label">Show my contact information</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="switch-container">
                            <span class="switch-label">Allow other users to send me messages</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <button class="btn btn-primary">Save Privacy Settings</button>
                    </div>
                    
                    <div class="settings-section">
                        <h2 class="settings-section-title">Data & Privacy</h2>
                        
                        <p>We value your privacy and give you control over your data.</p>
                        
                        <button class="btn btn-outline" style="margin-right: 10px;">Download My Data</button>
                        <button class="btn btn-outline">Privacy Policy</button>
                    </div>
                </div>
                
                <!-- Account Settings Panel -->
                <div class="settings-panel" id="account-panel">
                    <div class="settings-section">
                        <h2 class="settings-section-title">Account Status</h2>
                        
                        <div class="form-group">
                            <label class="form-label">Account Type</label>
                            <div><strong>Seller Account</strong></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Member Since</label>
                            <div>January 15, 2023</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Account Status</label>
                            <div><span style="color: var(--success-color);"><i class="fas fa-check-circle"></i> Active</span></div>
                        </div>
                    </div>
                    
                    <div class="delete-account-container">
                        <h3 style="color: var(--danger-color); margin-bottom: 1rem;">Delete Account</h3>
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="btn btn-danger" style="margin-top: 1rem;">Delete My Account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle tab switching
            $('.settings-menu-item').click(function() {
                // Update active menu item
                $('.settings-menu-item').removeClass('active');
                $(this).addClass('active');
                
                // Show corresponding panel
                const panelId = $(this).data('panel') + '-panel';
                $('.settings-panel').removeClass('active');
                $('#' + panelId).addClass('active');
            });
            
            // Two-factor authentication toggle
            $('#two_factor').change(function() {
                if($(this).is(':checked')) {
                    $('.two-factor-container').slideDown();
                } else {
                    $('.two-factor-container').slideUp();
                }
            });
            
            // Auto-tab for verification code
            $('.verification-input').keyup(function() {
                if ($(this).val().length === $(this).attr('maxlength')) {
                    $(this).next('.verification-input').focus();
                }
            });
            
            // Password visibility toggle
            $('.password-toggle').click(function() {
                const passwordField = $(this).prev('input');
                const iconElement = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    iconElement.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    iconElement.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Handle form submission animations
            $('form').submit(function() {
                const submitButton = $(this).find('button[type="submit"]');
                const originalText = submitButton.html();
                
                // Show loading state
                submitButton.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                submitButton.prop('disabled', true);
                
                // Reset button after 2 seconds (for demo)
                // In production, this would be handled by the form submission response
                setTimeout(function() {
                    submitButton.html(originalText);
                    submitButton.prop('disabled', false);
                }, 2000);
            });
            
            // Success message fade out
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 5000);
            
            // Profile image upload preview
            $('#profile_image').change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        $('.profile-image img').attr('src', e.target.result);
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Dark mode toggle
            $('#dark_mode').change(function() {
                if($(this).is(':checked')) {
                    $('body').addClass('dark-mode');
                    // In a real implementation, you would save this preference to the database
                } else {
                    $('body').removeClass('dark-mode');
                }
            });
            
            // Password strength meter
            $('#new_password').keyup(function() {
                const password = $(this).val();
                let strength = 0;
                
                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]+/)) strength += 1;
                if (password.match(/[A-Z]+/)) strength += 1;
                if (password.match(/[0-9]+/)) strength += 1;
                if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
                
                const strengthBar = $('.password-strength');
                
                // Update the strength bar
                if (password.length === 0) {
                    strengthBar.width('0%').css('background-color', '#e0e0e0');
                    $('.password-feedback').text('');
                } else {
                    // Calculate the percentage width
                    const percent = (strength / 5) * 100;
                    strengthBar.width(percent + '%');
                    
                    // Set the color based on strength
                    if (strength < 2) {
                        strengthBar.css('background-color', '#dc3545'); // Weak
                        $('.password-feedback').text('Weak').css('color', '#dc3545');
                    } else if (strength < 4) {
                        strengthBar.css('background-color', '#ffc107'); // Medium
                        $('.password-feedback').text('Medium').css('color', '#ffc107');
                    } else {
                        strengthBar.css('background-color', '#28a745'); // Strong
                        $('.password-feedback').text('Strong').css('color', '#28a745');
                    }
                }
            });
            
            // Show confirmation dialog when attempting to delete account
            $('.btn-danger').click(function(e) {
                if ($(this).text() === 'Delete My Account') {
                    e.preventDefault();
                    if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                        return false;
                    }
                }
            });
            
            // Initialize to show the profile panel by default
            $('.settings-menu-item[data-panel="profile"]').click();
        });
    </script>
</body>
</html>

<?php
// Close all statements
if (isset($user_stmt)) $user_stmt->close();
if (isset($update_stmt)) $update_stmt->close();
if (isset($password_stmt)) $password_stmt->close();
if (isset($pref_stmt)) $pref_stmt->close();
if (isset($pref_check_stmt)) $pref_check_stmt->close();

// Close connection
$conn->close();
?>