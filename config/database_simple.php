<?php
// Simple database configuration for XAMPP
$host = 'localhost';
$dbname = 'luthor_db';  // You may need to create this database
$username = 'root';     // Default XAMPP MySQL username
$password = '';         // Default XAMPP MySQL password (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test the connection
    $stmt = $pdo->query("SELECT 1");
    if (!$stmt) {
        throw new Exception("Database connection test failed");
    }
    
} catch(PDOException $e) {
    // If database doesn't exist, try to create it
    if (strpos($e->getMessage(), '1049') !== false || strpos($e->getMessage(), "doesn't exist") !== false) {
        try {
            $pdo_create = new PDO("mysql:host=$host;charset=utf8", $username, $password);
            $pdo_create->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_create->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Now connect to the newly created database
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $create_e) {
            die("Database creation failed: " . $create_e->getMessage() . "<br><br>
                 Please make sure XAMPP MySQL is running and create the database '$dbname' manually.<br>
                 You can do this by visiting <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a>");
        }
    } else {
        die("Connection failed: " . $e->getMessage() . "<br><br>
             Please make sure XAMPP MySQL is running.<br>
             Default XAMPP settings: Host=localhost, Username=root, Password=(empty)");
    }
}
?>
