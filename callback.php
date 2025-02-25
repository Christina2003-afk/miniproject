<?php
require 'vendor/autoload.php';
require_once "dbconfig.php";

session_start();

$client = new Google_Client();
$client->setClientId("888745675554-b2cig93tnsm47c8f5ku6sspm8qeeuqgj.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri("http://localhost/baker/callback.php");
$client->addScope("email");
$client->addScope("profile");

if (isset($_POST['credential'])) {
    // Verify the ID token
    try {
        $payload = $client->verifyIdToken($_POST['credential']);
        
        if ($payload) {
            $email = $payload['email'];
            $name = $payload['name'];
            $picture = $payload['picture'];
            $_SESSION['email'] = $email;
            
            // Check if user already exists
            $query = $conn->prepare("SELECT * FROM table_reg WHERE email = ?");
            $query->bind_param("s", $email);
            $query->execute();
            $result = $query->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($user['status'] == 'Inactive') {
                    echo '<script>alert("Your account is inactive. Please contact the administrator."); window.location.href = "login.html";</script>';
                    exit();
                }
                if ($user['role'] == "admin") {
                    header("Location: admindash.php");
                    exit();
                } else if ($user['role'] == "seller") {
                    header("Location: sellerdashboard.php"); 
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            }

            if ($result->num_rows == 0) {            
                // Insert new user
                $insert = $conn->prepare("INSERT INTO table_reg (name, email) VALUES (?, ?)");
                $insert->bind_param("ss", $name, $email);
                $insert->execute();
            }

            header("Location: index.php");
            exit();
        }
    } catch (Exception $e) {
        die("Error verifying token: " . $e->getMessage());
    }
}

// If we get here, something went wrong
header("Location: login.php?error=authentication_failed");
exit();
