<?php
// Simple script to create/update admin user with correct password
require_once '../../config/database.php';

$username = 'admin';
$password = 'admin123';
$email = 'admin@webbr.com';

// Generate proper password hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "✅ Admin user password updated successfully!\n";
    } else {
        // Create new admin user
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $email]);
        echo "✅ Admin user created successfully!\n";
    }
    
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Access: http://localhost/Luthor/admin/login.php\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the database is set up first!\n";
}
?>


