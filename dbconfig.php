<?php
// First, check if PostgreSQL functions are available
if (function_exists('pg_connect')) {
    // PostgreSQL connection details from Render
    $host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
    $port = "5432";
    $dbname = "dbart";
    $user = "dbart_user";
    $password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

    // Connect to PostgreSQL
    $connString = "host=$host port=$port dbname=$dbname user=$user password=$password";
    $conn = pg_connect($connString);
    
    // Check connection
    if (!$conn) {
        die("PostgreSQL Connection failed: " . pg_last_error());
    }
    
    // Define a function to run queries (to make transitioning between DB types easier)
    function db_query($query) {
        global $conn;
        return pg_query($conn, $query);
    }
    
    function db_fetch_assoc($result) {
        return pg_fetch_assoc($result);
    }
    
    function db_num_rows($result) {
        return pg_num_rows($result);
    }
    
    function db_escape_string($string) {
        global $conn;
        return pg_escape_string($conn, $string);
    }
} 
// If PostgreSQL isn't available, try MySQL
else if (function_exists('mysqli_connect')) {
    // MySQL connection details (use your local details here)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "artgallery";
    
    // Connect to MySQL
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    // Check connection
    if (!$conn) {
        die("MySQL Connection failed: " . mysqli_connect_error());
    }
    
    // Define compatible functions
    function db_query($query) {
        global $conn;
        return mysqli_query($conn, $query);
    }
    
    function db_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }
    
    function db_num_rows($result) {
        return mysqli_num_rows($result);
    }
    
    function db_escape_string($string) {
        global $conn;
        return mysqli_real_escape_string($conn, $string);
    }
} 
// No database extensions are available
else {
    die("Error: No database extensions available. Please install either php-pgsql or php-mysql extension.");
}

// You can add a comment to indicate successful connection
// echo "Connected to the database successfully!";
?>
