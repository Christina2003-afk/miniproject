<?php
// Set a flag for database connection status
$db_connected = false;

// Try PostgreSQL first if available
if (function_exists('pg_connect')) {
    try {
        // PostgreSQL connection details from Render
        $host = "dpg-cvqa16uuk2gs73d0m94g-a.oregon-postgres.render.com";
        $port = "5432";
        $dbname = "dbart";
        $user = "dbart_user";
        $password = "sT0kRz65o76nqAlYUc4gCh4mFZK2B1GZ";

        // Connect to PostgreSQL
        $connString = "host=$host port=$port dbname=$dbname user=$user password=$password";
        $conn = @pg_connect($connString); // @ to suppress warnings
        
        if ($conn) {
            $db_connected = true;
            
            // Define compatible functions
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
    } catch (Exception $e) {
        // Continue to next connection method if this fails
    }
}

// If PostgreSQL didn't work, try MySQL
if (!$db_connected && function_exists('mysqli_connect')) {
    try {
        // Try with potential different MySQL configurations
        $mysql_configs = [
            // Default local setup
            ['localhost', 'root', '', 'artgallery'],
            // Common shared hosting setup
            ['localhost', $_SERVER['USER'] ?? 'root', '', $_SERVER['USER'] ?? 'artgallery'],
            // Another common pattern
            ['127.0.0.1', 'root', '', 'artgallery']
        ];
        
        foreach ($mysql_configs as $config) {
            list($servername, $username, $password, $database) = $config;
            
            // Try to connect
            $conn = @mysqli_connect($servername, $username, $password, $database);
            
            if ($conn) {
                $db_connected = true;
                
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
                
                break; // Exit the loop if connection successful
            }
        }
    } catch (Exception $e) {
        // Continue to error handling
    }
}

// If no database connection worked
if (!$db_connected) {
    // Create a nicer error page
    echo "<html><head><title>Database Connection Error</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:40px;line-height:1.6}";
    echo ".error-container{border:1px solid #ffcccc;background:#ffeeee;padding:20px;border-radius:5px}";
    echo "h1{color:#cc0000}</style></head><body>";
    echo "<div class='error-container'>";
    echo "<h1>Database Connection Error</h1>";
    echo "<p>The application could not connect to any database. Please check your configuration.</p>";
    echo "<ul>";
    echo "<li>The PostgreSQL extension (php-pgsql) is not installed on this server.</li>";
    echo "<li>MySQL connection also failed. Make sure MySQL is running and accessible.</li>";
    echo "</ul>";
    echo "<p>For server administrators:</p>";
    echo "<pre>sudo apt-get install php-pgsql\nsudo systemctl restart apache2</pre>";
    echo "</div></body></html>";
    exit;
}

// If we get here, we have a successful connection
?>
