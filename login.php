<?php
include("dbconfig.php");
session_start();

// Get form data
$email = $_POST["email"];
$password = $_POST["password"];

// Store the email in session
$_SESSION["email"] = $email;

// Query to check user credentials and their role (admin check)
$userCheckQuery = "SELECT * FROM table_reg WHERE email = '$email'";

$result = mysqli_query($conn, $userCheckQuery);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Verify the password
    if (password_verify($password, $user['password'])){
        // Check user roles and redirect accordingly
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
    } else {
        // Email exists but password is wrong - display user data
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Error</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <style>
                .user-data {
                    margin: 20px;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Password is incorrect for this email.',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    window.location.href = 'login.html';
                });
            </script>
        </body>
        </html>
        <?php
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Error</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: 'Email or password is incorrect.',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                window.location.href = 'login.html';
            });
        </script>
    </body>
    </html>
<?php
}

// Close the database connection
mysqli_close($conn);
?>
