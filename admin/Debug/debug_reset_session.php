<?php
require_once '../../config/admin_auth_check.php';
session_start();

echo "<h1>🔍 Session Debug for Password Reset</h1>\n";

echo "<h2>Current Session Data:</h2>\n";
echo "<pre>\n";
print_r($_SESSION);
echo "</pre>\n";

if (isset($_SESSION['reset_token'])) {
    echo "<h2>Reset Token in Session:</h2>\n";
    echo "<p>Token: " . htmlspecialchars($_SESSION['reset_token']) . "</p>\n";
    
    // Check if this token exists in database
    require_once '../../config/database.php';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$_SESSION['reset_token']]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($token_data) {
            echo "<h3>Token found in database:</h3>\n";
            echo "<ul>\n";
            echo "<li>ID: " . $token_data['id'] . "</li>\n";
            echo "<li>User ID: " . $token_data['user_id'] . "</li>\n";
            echo "<li>Verification Code: <strong>" . $token_data['verification_code'] . "</strong></li>\n";
            echo "<li>Expires: " . $token_data['expires_at'] . "</li>\n";
            echo "<li>Used: " . ($token_data['used'] ? 'YES' : 'NO') . "</li>\n";
            echo "<li>Verified: " . ($token_data['verified'] ? 'YES' : 'NO') . "</li>\n";
            echo "<li>Status: " . ($token_data['expires_at'] < date('Y-m-d H:i:s') ? 'EXPIRED' : 'ACTIVE') . "</li>\n";
            echo "</ul>\n";
        } else {
            echo "<p>❌ Token not found in database</p>\n";
        }
    } catch (PDOException $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p>No reset token in session</p>\n";
}

if (isset($_SESSION['verified_reset_token'])) {
    echo "<h2>Verified Reset Token:</h2>\n";
    echo "<p>Token: " . htmlspecialchars($_SESSION['verified_reset_token']) . "</p>\n";
}

if (isset($_SESSION['reset_user_id'])) {
    echo "<h2>Reset User ID:</h2>\n";
    echo "<p>User ID: " . htmlspecialchars($_SESSION['reset_user_id']) . "</p>\n";
}

echo "<h2>🔧 Actions:</h2>\n";
echo "<p><a href='?clear_session=1'>Clear Session</a></p>\n";
echo "<p><a href='admin/forgot_password.php'>Start New Password Reset</a></p>\n";

if (isset($_GET['clear_session'])) {
    session_unset();
    session_destroy();
    echo "<p>✅ Session cleared. <a href='?'>Refresh page</a></p>\n";
}
?>



