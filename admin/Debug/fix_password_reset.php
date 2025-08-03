<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>🔧 Password Reset System Fix</h1>\n";

try {
    // Check if 'verified' column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM password_reset_tokens LIKE 'verified'");
    $verifiedExists = $stmt->fetch();
    
    if (!$verifiedExists) {
        echo "<p>❌ 'verified' column missing from password_reset_tokens table. Adding it...</p>\n";
        
        $pdo->exec("ALTER TABLE password_reset_tokens ADD COLUMN verified BOOLEAN DEFAULT FALSE AFTER used");
        echo "<p>✅ Added 'verified' column to password_reset_tokens table</p>\n";
    } else {
        echo "<p>✅ 'verified' column already exists</p>\n";
    }
    
    // Clean up old/expired tokens
    echo "<h2>🧹 Cleaning up expired tokens...</h2>\n";
    $stmt = $pdo->query("DELETE FROM password_reset_tokens WHERE expires_at < NOW()");
    $deletedExpired = $stmt->rowCount();
    echo "<p>✅ Deleted $deletedExpired expired tokens</p>\n";
    
    // Reset any partially completed resets
    $stmt = $pdo->query("UPDATE password_reset_tokens SET verified = FALSE, used = FALSE WHERE verified = TRUE AND used = FALSE");
    $resetCount = $stmt->rowCount();
    echo "<p>✅ Reset $resetCount incomplete verification states</p>\n";
    
    // Show current table structure
    echo "<h2>📋 Updated Table Structure:</h2>\n";
    $stmt = $pdo->query("DESCRIBE password_reset_tokens");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>\n";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'];
        if ($column['Default'] !== null) {
            echo " (default: " . $column['Default'] . ")";
        }
        echo "</li>\n";
    }
    echo "</ul>\n";
    
    echo "<h2>✅ Password Reset System Fixed!</h2>\n";
    echo "<p>The system should now work correctly. Try the password reset process again.</p>\n";
    echo "<p><a href='admin/forgot_password.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Password Reset</a></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Error fixing password reset system: " . $e->getMessage() . "</p>\n";
}
?>



