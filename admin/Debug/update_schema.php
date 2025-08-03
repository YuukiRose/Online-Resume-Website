<?php
require_once '../../config/database.php';

echo "<h1>Database Schema Update Script</h1>\n";

try {
    // Check if user_id column exists in testimonials table
    $stmt = $pdo->query("SHOW COLUMNS FROM testimonials LIKE 'user_id'");
    $userIdExists = $stmt->fetch();
    
    if (!$userIdExists) {
        echo "<p>❌ user_id column missing from testimonials table. Adding it...</p>\n";
        
        // Add user_id column to testimonials table
        $pdo->exec("ALTER TABLE testimonials ADD COLUMN user_id INT NULL AFTER id");
        echo "<p>✅ Added user_id column to testimonials table</p>\n";
        
        // Add foreign key constraint
        $pdo->exec("ALTER TABLE testimonials ADD CONSTRAINT fk_testimonials_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "<p>✅ Added foreign key constraint</p>\n";
    } else {
        echo "<p>✅ user_id column already exists in testimonials table</p>\n";
    }
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->fetch();
    
    if (!$usersTableExists) {
        echo "<p>❌ users table missing. Creating it...</p>\n";
        
        $createUsersTable = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        )";
        
        $pdo->exec($createUsersTable);
        echo "<p>✅ Created users table</p>\n";
    } else {
        echo "<p>✅ users table already exists</p>\n";
    }
    
    // Check if user_sessions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_sessions'");
    $userSessionsExists = $stmt->fetch();
    
    if (!$userSessionsExists) {
        echo "<p>❌ user_sessions table missing. Creating it...</p>\n";
        
        $createUserSessionsTable = "
        CREATE TABLE user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $pdo->exec($createUserSessionsTable);
        echo "<p>✅ Created user_sessions table</p>\n";
    } else {
        echo "<p>✅ user_sessions table already exists</p>\n";
    }
    
    echo "<h2>✅ Database schema update complete!</h2>\n";
    echo "<p><a href='user/dashboard.php'>Test User Dashboard</a></p>\n";
    echo "<p><a href='admin/dashboard.php'>Test Admin Dashboard</a></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
}
?>



