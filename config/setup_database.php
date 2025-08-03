<?php
// Database setup script for Luthor
require_once 'admin_auth_check.php';
require_once 'database_simple.php';

echo "<h2>Setting up Luthor Database...</h2>";

try {
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        linkedin_profile VARCHAR(255) NULL,
        profile_picture_url VARCHAR(255) NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
        email_verified_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )";
    $pdo->exec($sql);
    echo "✅ Users table created successfully<br>";
    
    // Create email_verifications table
    $sql = "CREATE TABLE IF NOT EXISTS email_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(255) NOT NULL,
        verification_code VARCHAR(10) NOT NULL,
        purpose ENUM('password_reset', 'account_activation', 'email_verification', 'login_verification') NOT NULL,
        verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_user_purpose (user_id, purpose),
        INDEX idx_expires (expires_at)
    )";
    $pdo->exec($sql);
    echo "✅ Email verifications table created successfully<br>";
    
    // Create password_reset_tokens table (for compatibility)
    $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        verified BOOLEAN DEFAULT FALSE,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_expires (expires_at)
    )";
    $pdo->exec($sql);
    echo "✅ Password reset tokens table created successfully<br>";
    
    // Create a test admin user
    $hashedPassword = password_hash('admin123', PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password, first_name, last_name, status, email_verified_at) 
        VALUES (?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    if ($stmt->execute(['admin', 'admin@luthor.local', $hashedPassword, 'Admin', 'User'])) {
        echo "✅ Test admin user created (Username: admin, Password: admin123)<br>";
    } else {
        echo "ℹ️ Admin user already exists<br>";
    }
    
    // Create a regular test user
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password, first_name, last_name, status, email_verified_at) 
        VALUES (?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    if ($stmt->execute(['testuser', 'test@luthor.local', $hashedPassword, 'Test', 'User'])) {
        echo "✅ Test user created (Username: testuser, Password: admin123)<br>";
    } else {
        echo "ℹ️ Test user already exists<br>";
    }
    
    echo "<br><h3>✅ Database setup completed successfully!</h3>";
    echo "<p><a href='../user/login.php'>Go to Login Page</a></p>";
    echo "<p><a href='../user/register.php'>Go to Registration Page</a></p>";
    echo "<p><a href='../index.html'>Back to Home</a></p>";
    
} catch(PDOException $e) {
    echo "❌ Error setting up database: " . $e->getMessage();
}
?>
