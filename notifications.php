<?php
session_start();
require_once 'dbconfig.php';

// Check if the seller is logged in
if (!isset($_SESSION['seller_email'])) {
    header("Location: seller_login.php");
    exit();
}

$seller_email = $_SESSION['seller_email'];

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
    <title>Seller Notifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Canvas Confetti library -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --primary: #3a6ea5;
            --secondary: #ff6b6b;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --unread-bg: #e3f2fd;
            --unread-border: #90caf9;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            padding-top: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .notification {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .notification.unread {
            background-color: #e6f7ff;
            border-left: 4px solid #1890ff;
        }
        .back-link {
            margin-bottom: 20px;
        }
        
        /* Surprise Popup Styles */
        .surprise-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.1);
            z-index: 1050;
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 90%;
            width: 400px;
            overflow: hidden;
        }
        
        .surprise-popup.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            visibility: visible;
        }
        
        .surprise-popup .close-surprise {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: all 0.3s;
        }
        
        .surprise-popup .close-surprise:hover {
            color: #333;
            transform: rotate(90deg);
        }
        
        .surprise-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: var(--secondary);
            animation: pulse 1.5s infinite;
        }
        
        .surprise-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .surprise-message {
            font-size: 16px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .surprise-action {
            display: inline-block;
            padding: 10px 25px;
            background: linear-gradient(135deg, var(--primary), #274c77);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .surprise-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Birthday Popper */
        .birthday-popper {
            position: fixed;
            bottom: -300px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1060;
            width: 350px;
            height: 300px;
            background-image: url('https://i.pinimg.com/originals/2a/c9/67/2ac967e9c0ac34583cc63d3567479407.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            animation: popUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            display: none;
        }
        
        .birthday-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -80%);
            width: 80%;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-shadow: 0 0 5px white;
        }
        
        /* Achievement Notification */
        .achievement {
            position: fixed;
            top: 20px;
            right: -350px;
            background: white;
            border-radius: 8px;
            padding: 15px;
            width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            z-index: 1050;
            transition: right 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }
        
        .achievement.show {
            right: 20px;
        }
        
        .achievement-icon {
            width: 50px;
            height: 50px;
            background: #ffe9c8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .achievement-icon i {
            font-size: 24px;
            color: #f7941d;
        }
        
        .achievement-content h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .achievement-content p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #777;
        }
        
        /* Floating Elements */
        .floating-element {
            position: fixed;
            z-index: 1040;
            pointer-events: none;
            font-size: 48px;
            animation: float 8s linear forwards;
            opacity: 0;
        }
        
        /* Animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        @keyframes popUp {
            0% { bottom: -300px; }
            80% { bottom: 20px; }
            90% { bottom: 15px; }
            100% { bottom: 20px; }
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }
        
        /* Confetti canvas */
        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="sellerdashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Notifications</h3>
                <?php if ($unread_count > 0): ?>
                <form method="POST" action="mark_notifications.php">
                    <button type="submit" class="btn btn-sm btn-primary">Mark all as read</button>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="card-body">
                <?php if ($notif_result->num_rows > 0): ?>
                    <?php while ($notif = $notif_result->fetch_assoc()): ?>
                        <?php $class = $notif['is_read'] ? "notification" : "notification unread"; ?>
                        <div class="<?php echo $class; ?>">
                            <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <small class="text-muted">Received on: <?php echo $notif['created_at']; ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center py-3">No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Surprise Popup -->
    <div id="surprisePopup" class="surprise-popup">
        <div class="close-surprise">&times;</div>
        <div class="surprise-icon">
            <i class="fas fa-gift"></i>
        </div>
        <div class="surprise-title">Surprise!</div>
        <div class="surprise-message">
            Congratulations! Your latest artwork bids accepted! 
            Keep up the amazing work and continue inspiring art lovers.
        </div>
        <button class="surprise-action">Claim Reward</button>
    </div>

    <!-- Birthday/Celebration Popper -->
    <div id="birthdayPopper" class="birthday-popper">
        <div class="birthday-message">
            Your artwork happiness for our customers
        </div>
    </div>

    <!-- Achievement Notification -->
    <div id="achievement" class="achievement">
        <div class="achievement-icon">
            <i class="fas fa-medal"></i>
        </div>
        <div class="achievement-content">
            <h3>Achievement Unlocked!</h3>
            <p>You've become a Top Seller this month! Keep it up!</p>
        </div>
    </div>

    <!-- Floating elements container -->
    <div id="floatingContainer"></div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Random trigger functions
            function getRandomTime(min, max) {
                return Math.floor(Math.random() * (max - min + 1) + min);
            }
            
            // Show surprise popup with confetti
            function showSurprisePopup() {
                // Trigger confetti
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
                
                // Show the popup
                $('#surprisePopup').addClass('active');
                
                // Close after interaction
                $('.close-surprise, .surprise-action').on('click', function() {
                    $('#surprisePopup').removeClass('active');
                });
            }
            
            // Show birthday popper
            function showBirthdayPopper() {
                $('#birthdayPopper').css('display', 'block');
                
                // Celebrate with confetti
                const end = Date.now() + (5 * 1000);
                
                // Create a confetti cannon
                const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'];
                
                (function frame() {
                    confetti({
                        particleCount: 2,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 },
                        colors: colors
                    });
                    
                    confetti({
                        particleCount: 2,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 },
                        colors: colors
                    });
                    
                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
                
                // Hide after 5 seconds
                setTimeout(() => {
                    $('#birthdayPopper').css({
                        'animation': 'none',
                        'bottom': '-300px'
                    });
                    setTimeout(() => {
                        $('#birthdayPopper').css('display', 'none');
                    }, 500);
                }, 7000);
            }
            
            // Show achievement notification
            function showAchievement() {
                $('#achievement').addClass('show');
                
                setTimeout(() => {
                    $('#achievement').removeClass('show');
                }, 5000);
            }
            
            // Create floating elements
            function createFloatingElement() {
                const emojis = ['🎨', '🖼️', '🏆', '💰', '🎉', '⭐', '🎊'];
                const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
                
                const element = $('<div class="floating-element">' + randomEmoji + '</div>');
                
                // Random position
                const startPositionX = Math.random() * window.innerWidth;
                element.css({
                    left: startPositionX + 'px',
                    bottom: '-50px'
                });
                
                $('#floatingContainer').append(element);
                
                // Remove element after animation completes
                setTimeout(() => {
                    element.remove();
                }, 8000);
            }
            
            // Schedule random popups
            setTimeout(showSurprisePopup, getRandomTime(5000, 15000));
            setTimeout(showBirthdayPopper, getRandomTime(20000, 30000));
            setTimeout(showAchievement, getRandomTime(8000, 12000));
            
            // Create floating elements periodically
            setInterval(createFloatingElement, getRandomTime(3000, 6000));
            
            // Trigger confetti on certain actions
            $('.mark-all-btn').click(function() {
                confetti({
                    particleCount: 50,
                    spread: 70,
                    origin: { y: 0.7 }
                });
            });
            
            // Special trigger when user receives a high-value bid
            function triggerSpecialEvent() {
                // Fireworks effect
                const duration = 5 * 1000;
                const animationEnd = Date.now() + duration;
                const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
                
                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }
                
                const interval = setInterval(function() {
                    const timeLeft = animationEnd - Date.now();
                    
                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }
                    
                    const particleCount = 50 * (timeLeft / duration);
                    
                    // Create bursts of confetti from random positions
                    confetti({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
                    });
                    confetti({
                        ...defaults,
                        particleCount,
                        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
                    });
                }, 250);
            }
            
            // Demo trigger for special event (high-value bid received)
            setTimeout(triggerSpecialEvent, getRandomTime(35000, 45000));
        });
    </script>
</body>
</html>

<?php
$notif_stmt->close();
$count_stmt->close();
$conn->close();
?> 