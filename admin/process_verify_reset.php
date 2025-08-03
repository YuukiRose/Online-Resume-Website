<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = trim($_POST['verification_code']);
    
    if (!isset($_SESSION['reset_token'])) {
        header('Location: forgot_password.php');
        exit;
    }
    
    $token = $_SESSION['reset_token'];
    
    try {
        // Verify the code and token
        $stmt = $pdo->prepare("
            SELECT prt.*, au.username 
            FROM password_reset_tokens prt 
            JOIN admin_users au ON prt.user_id = au.id 
            WHERE prt.token = ? AND prt.verification_code = ? AND prt.expires_at > NOW() AND prt.used = FALSE
        ");
        $stmt->execute([$token, $verification_code]);
        $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset_request) {
            // Check if token exists but is expired or used
            $stmt = $pdo->prepare("SELECT expires_at, used FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$token]);
            $token_info = $stmt->fetch();
            
            if ($token_info && $token_info['expires_at'] < date('Y-m-d H:i:s')) {
                header('Location: verify_reset.php?error=expired');
            } else {
                header('Location: verify_reset.php?error=invalid');
            }
            exit;
        }
        
        // Valid code - redirect to password reset form
        $_SESSION['verified_reset_token'] = $token;
        $_SESSION['reset_user_id'] = $reset_request['user_id'];
        
        // Also store in database for extra security
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET verified = TRUE WHERE token = ?");
        $stmt->execute([$token]);
        
        // Force session write and close before redirect
        session_write_close();
        
        header('Location: reset_password.php');
        exit;
        
    } catch (PDOException $e) {
        header('Location: verify_reset.php?error=invalid');
        exit;
    }
}

header('Location: verify_reset.php');
exit;
?>
