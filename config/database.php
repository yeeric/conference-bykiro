<?php
/**
 * Database Configuration
 * Provides PDO connection for DBMS independence
 */

/**
 * Get a configured PDO database connection
 * 
 * @return PDO Configured database connection
 * @throws PDOException If connection fails
 */
function getDBConnection(): PDO {

/** 
    // Local Dev Database configuration
    $host = 'localhost';
    $dbname = 'ConferenceDB';
    $username = 'root';
    $password = 'jieWang1990!';
    $charset = 'utf8mb4';

    // Build DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
*/
    $username = getenv('DB_USER'); // e.g. 'your_db_user'
    $password = getenv('DB_PASS'); // e.g. 'your_db_password'
    $dbName = getenv('DB_NAME'); // e.g. 'your_db_name'
      
    $instanceUnixSocket = getenv('INSTANCE_UNIX_SOCKET'); // e.g. '/cloudsql/project:region:instance'

    // Connect using UNIX sockets
    $dsn = sprintf(
                'mysql:dbname=%s;unix_socket=%s',
                $dbName,
                $instanceUnixSocket
            );


    // PDO options for security and error handling
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error for debugging (in production, log to file)
        error_log("Database connection failed: " . $e->getMessage());
        
        // Display user-friendly error message
        die("Database connection failed. Please contact the system administrator.");
    }
}
