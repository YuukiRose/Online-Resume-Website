<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>🔍 Password Reset Token Debug</h1>\n";

try {
    // Check password_reset_tokens table structure
    echo "<h2>Password Reset Tokens Table Structure:</h2>\n";
    $stmt = $pdo->query("DESCRIBE password_reset_tokens");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>\n";
    foreach ($columns as $column) {
        echo "Column: " . $column['Field'] . " | Type: " . $column['Type'] . " | Null: " . $column['Null'] . " | Default: " . $column['Default'] . "\n";
    }
    echo "</pre>\n";
    
    // Check recent password reset attempts
    echo "<h2>Recent Password Reset Tokens (Last 10):</h2>\n";
    $stmt = $pdo->query("
        SELECT prt.*, au.username, au.email,
               CASE 
                   WHEN prt.expires_at < NOW() THEN 'EXPIRED'
                   WHEN prt.used = TRUE THEN 'USED'
                   WHEN prt.verified = TRUE THEN 'VERIFIED'
                   ELSE 'ACTIVE'
               END as status
        FROM password_reset_tokens prt 
        LEFT JOIN admin_users au ON prt.user_id = au.id 
        ORDER BY prt.created_at DESC 
        LIMIT 10
    ");
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tokens)) {
        echo "<p>No password reset tokens found in database.</p>\n";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Verification Code</th><th>Status</th><th>Created</th><th>Expires</th></tr>\n";
        foreach ($tokens as $token) {
            echo "<tr>\n";
            echo "<td>" . htmlspecialchars($token['id']) . "</td>\n";
            echo "<td>" . htmlspecialchars($token['username'] ?? 'Unknown') . "</td>\n";
            echo "<td>" . htmlspecialchars($token['email'] ?? 'Unknown') . "</td>\n";
            echo "<td><strong>" . htmlspecialchars($token['verification_code']) . "</strong></td>\n";
            echo "<td>" . htmlspecialchars($token['status']) . "</td>\n";
            echo "<td>" . htmlspecialchars($token['created_at']) . "</td>\n";
            echo "<td>" . htmlspecialchars($token['expires_at']) . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // Check if there are verification code mismatches
    echo "<h2>Troubleshooting Information:</h2>\n";
    
    // Check for tokens without verification codes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM password_reset_tokens WHERE verification_code IS NULL OR verification_code = ''");
    $emptyCodeCount = $stmt->fetch()['count'];
    echo "<p>Tokens with empty verification codes: $emptyCodeCount</p>\n";
    
    // Check for expired tokens
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM password_reset_tokens WHERE expires_at < NOW()");
    $expiredCount = $stmt->fetch()['count'];
    echo "<p>Expired tokens: $expiredCount</p>\n";
    
    // Check for used tokens
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM password_reset_tokens WHERE used = TRUE");
    $usedCount = $stmt->fetch()['count'];
    echo "<p>Used tokens: $usedCount</p>\n";
    
    echo "<h3>🔧 Quick Actions:</h3>\n";
    echo "<p><a href='admin/forgot_password.php'>Test Password Reset</a></p>\n";
    echo "<p><a href='?cleanup=1'>Clean up old tokens</a></p>\n";
    
    // Cleanup old tokens if requested
    if (isset($_GET['cleanup'])) {
        echo "<h3>🧹 Cleaning up old tokens...</h3>\n";
        $stmt = $pdo->query("DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = TRUE");
        $deletedCount = $stmt->rowCount();
        echo "<p>✅ Deleted $deletedCount old/used tokens</p>\n";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
}
?>


