<?php
// PostgreSQL connection settings from Render
$host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "dbart";
$user = "dbart_user";
$password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

// Connection string
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Attempt to connect
$conn = pg_connect($conn_string);

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
} else {
    // echo "Connected to PostgreSQL database successfully!";
}
?>
