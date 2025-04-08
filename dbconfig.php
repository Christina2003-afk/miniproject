<?php
// PostgreSQL connection details from Render
$host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "dbart";
$user = "dbart_user";
$password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

// First, try using PDO if available
try {
    if (class_exists('PDO')) {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
        $conn = new PDO($dsn);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Connected to PostgreSQL database using PDO successfully!<br>";
    } else if (function_exists('pg_connect')) {
        // If PDO not available, try using pg_connect
        $connString = "host=$host port=$port dbname=$dbname user=$user password=$password";
        $conn = pg_connect($connString);
        
        if (!$conn) {
            throw new Exception("PostgreSQL connection failed: " . pg_last_error());
        }
        // echo "Connected to PostgreSQL database using pg_connect successfully!<br>";
    } else {
        // If neither PostgreSQL extension is available, fallback to MySQL if that's all that's available
        throw new Exception("No PostgreSQL extensions available. Please install php-pgsql extension.");
    }
} catch (Exception $e) {
    // Display detailed error message
    die("Connection failed: " . $e->getMessage() . 
        "<br><br>Please make sure the PostgreSQL extension is installed:<br>" .
        "For Ubuntu/Debian: <code>sudo apt-get install php-pgsql</code><br>" .
        "For CentOS/RHEL: <code>sudo yum install php-pgsql</code><br>" .
        "Then restart your web server: <code>sudo systemctl restart apache2</code> (or nginx/httpd)<br><br>" .
        "To check installed PHP extensions: <code>php -m | grep pgsql</code>");
}

// If you get here, connection was successful
?>
