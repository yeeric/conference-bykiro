<?php
/**
 * Database Configuration Example
 * 
 * Copy this file to database.php and update with your credentials
 */

function getDBConnection() {
    // Database configuration
    $host = 'localhost';
    $dbname = 'ConferenceDB';
    $username = 'your_username';  // Change this
    $password = 'your_password';  // Change this
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );
        
        // Set PDO attributes for security and error handling
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
