<?php
// PostgreSQL connection details
$host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "dbart";
$user = "dbart_user";
$password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

// Attempt connection
try {
    $dbconn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
    
    if (!$dbconn) {
        echo "Failed to connect to PostgreSQL";
        exit;
    }
    
    echo "Successfully connected to PostgreSQL!";
    
    // Test query
    $result = pg_query($dbconn, "SELECT 1");
    if (!$result) {
        echo "An error occurred.\n";
        exit;
    }
    
    // Close connection
    pg_close($dbconn);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
