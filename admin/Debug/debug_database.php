<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

try {
    // Check if user_id column exists in testimonials table
    $stmt = $pdo->query("DESCRIBE testimonials");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Testimonials Table Structure:</h2>\n";
    echo "<pre>\n";
    foreach ($columns as $column) {
        echo "Column: " . $column['Field'] . " | Type: " . $column['Type'] . " | Null: " . $column['Null'] . " | Key: " . $column['Key'] . " | Default: " . $column['Default'] . "\n";
    }
    echo "</pre>\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $userTableExists = $stmt->fetch();
    
    echo "<h2>Users Table Exists: " . ($userTableExists ? "YES" : "NO") . "</h2>\n";
    
    if ($userTableExists) {
        $stmt = $pdo->query("DESCRIBE users");
        $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Users Table Structure:</h2>\n";
        echo "<pre>\n";
        foreach ($userColumns as $column) {
            echo "Column: " . $column['Field'] . " | Type: " . $column['Type'] . " | Null: " . $column['Null'] . " | Key: " . $column['Key'] . " | Default: " . $column['Default'] . "\n";
        }
        echo "</pre>\n";
    }
    
    // Check current testimonials data
    $stmt = $pdo->query("SELECT * FROM testimonials LIMIT 5");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Sample Testimonials Data:</h2>\n";
    echo "<pre>\n";
    print_r($testimonials);
    echo "</pre>\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>


