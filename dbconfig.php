<?php
// PostgreSQL connection details from Render
$host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "dbart";
$user = "dbart_user";
$password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

// Using pg_connect (native PostgreSQL functions)
$connString = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($connString);

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
} else {
    // echo "Connected to the database successfully!<br>";
}
?>
