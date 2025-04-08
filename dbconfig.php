<?php
// PostgreSQL connection details from Render
$host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "dbart";
$user = "dbart_user";
$password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

// Using PDO (recommended)
try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected to the database successfully!<br>";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
