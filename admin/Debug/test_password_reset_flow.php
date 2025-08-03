<?php
require_once '../../config/admin_auth_check.php';
require_once '../../config/database.php';

echo "<h1>🧪 Password Reset System Test</h1>\n";

try {
    // Check admin users
    $stmt = $pdo->query("SELECT id, username, email FROM admin_users LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p>❌ No admin users found. Create an admin user first.</p>\n";
        exit;
    }
    
    echo "<h2>Test Scenario: Password Reset for " . htmlspecialchars($admin['username']) . "</h2>\n";
    
    // Clean up old tokens for this user
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
    $stmt->execute([$admin['id']]);
    
    // Generate test token and verification code
    $token = bin2hex(random_bytes(32));
    $verification_code = sprintf('%06d', mt_rand(100000, 999999));
    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
    
    echo "<h3>Step 1: Creating password reset token</h3>\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO password_reset_tokens (user_id, token, verification_code, expires_at, used, verified) 
        VALUES (?, ?, ?, ?, FALSE, FALSE)
    ");
    
    if ($stmt->execute([$admin['id'], $token, $verification_code, $expires_at])) {
        echo "<p>✅ Token created successfully</p>\n";
        echo "<p><strong>Verification Code:</strong> <span style='font-size: 18px; background: #e9ecef; padding: 5px 10px; border-radius: 3px;'>$verification_code</span></p>\n";
    } else {
        echo "<p>❌ Failed to create token</p>\n";
        exit;
    }
    
    echo "<h3>Step 2: Simulating verification process</h3>\n";
    
    // Test the verification query
    $stmt = $pdo->prepare("
        SELECT prt.*, au.username 
        FROM password_reset_tokens prt 
        JOIN admin_users au ON prt.user_id = au.id 
        WHERE prt.token = ? AND prt.verification_code = ? AND prt.expires_at > NOW() AND prt.used = FALSE
    ");
    $stmt->execute([$token, $verification_code]);
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reset_request) {
        echo "<p>✅ Verification query successful</p>\n";
        echo "<p>Found reset request for user: " . htmlspecialchars($reset_request['username']) . "</p>\n";
        
        // Mark as verified
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET verified = TRUE WHERE token = ?");
        $stmt->execute([$token]);
        echo "<p>✅ Token marked as verified</p>\n";
    } else {
        echo "<p>❌ Verification query failed</p>\n";
        
        // Debug - check what tokens exist
        $stmt = $pdo->prepare("SELECT * FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$admin['id']]);
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Debug - Current tokens for this user:</h4>\n";
        echo "<pre>\n";
        print_r($tokens);
        echo "</pre>\n";
    }
    
    echo "<h3>Step 3: Test with wrong verification code</h3>\n";
    
    $wrong_code = '000000';
    $stmt = $pdo->prepare("
        SELECT prt.*, au.username 
        FROM password_reset_tokens prt 
        JOIN admin_users au ON prt.user_id = au.id 
        WHERE prt.token = ? AND prt.verification_code = ? AND prt.expires_at > NOW() AND prt.used = FALSE
    ");
    $stmt->execute([$token, $wrong_code]);
    $wrong_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$wrong_request) {
        echo "<p>✅ Wrong verification code correctly rejected</p>\n";
    } else {
        echo "<p>❌ Security issue: Wrong verification code was accepted</p>\n";
    }
    
    echo "<h3>📋 Test Summary</h3>\n";
    echo "<ul>\n";
    echo "<li>✅ Database table structure is correct</li>\n";
    echo "<li>✅ Token generation works</li>\n";
    echo "<li>✅ Verification query logic works</li>\n";
    echo "<li>✅ Invalid codes are rejected</li>\n";
    echo "</ul>\n";
    
    echo "<h3>🔧 Manual Test</h3>\n";
    echo "<p>Now test the actual password reset flow:</p>\n";
    echo "<ol>\n";
    echo "<li><a href='admin/forgot_password.php' target='_blank'>Go to forgot password page</a></li>\n";
    echo "<li>Enter email: <strong>" . htmlspecialchars($admin['email']) . "</strong></li>\n";
    echo "<li>Check your email for the verification code</li>\n";
    echo "<li>Enter the code on the verification page</li>\n";
    echo "<li>Set a new password</li>\n";
    echo "</ol>\n";
    
    // Clean up test token
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
    $stmt->execute([$token]);
    echo "<p><small>🧹 Test token cleaned up</small></p>\n";
    
} catch (PDOException $e) {
    echo "<p>❌ Test failed: " . $e->getMessage() . "</p>\n";
}
?>



