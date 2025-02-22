<?php
include("dbconfig.php"); // Include your database configuration file
session_start();

// Get form data
$name = $_POST["uname"];
$email = $_POST["email"];
$password = $_POST["password"];

// Hash the password before storing it
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists in the database
$check_query = "SELECT * FROM table_reg WHERE email = '$email'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    // If the email already exists, show an alert and redirect to the registration page
    echo '<script>alert("Email already exists."); window.location.href = "register.html";</script>';
    exit();
} else {
    // If the email does not exist, insert the new user into the database
    $insert_query = "INSERT INTO table_reg (name, email, password) VALUES ('$name', '$email', '$hashedPassword')";

    if (mysqli_query($conn, $insert_query)) {
        // Redirect to the login page after successful registration
        header("Location: http://localhost/baker/login.html");
        exit();
    } else {
        // If there's an error in the query, display the error
        echo "Error: " . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>
