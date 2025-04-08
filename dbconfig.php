<?php
// Check if we're on production (Render) or local environment
$isProduction = (getenv('RENDER') === 'true');

if ($isProduction) {
    // This code path will be executed once you have the PostgreSQL extension installed
    // For now, this won't work until php-pgsql is installed
    $host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
    $port = "5432";
    $dbname = "dbart";
    $user = "dbart_user";
    $password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";
    
    // We'll attempt this when the extension is available
    // $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
    
    // For now, just throw an error
    die("PostgreSQL extension not installed. Please contact server administrator to install php-pgsql extension.");
} else {
    // Use MySQL for local development (this should work with your current setup)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "artgallery";
    
    // Connect to MySQL server
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}
?>
