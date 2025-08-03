<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $access_granted = false;
    $user_id = null;
    $token = null;
    
    // First check session variables
    if (isset($_SESSION['verified_reset_token']) && isset($_SESSION['reset_user_id'])) {
        $access_granted = true;
        $user_id = $_SESSION['reset_user_id'];
        $token = $_SESSION['verified_reset_token'];
    }
    // Fallback: Check database for verified token
    else if (isset($_SESSION['reset_token'])) {
        $token = $_SESSION['reset_token'];
        try {
            $stmt = $pdo->prepare("
                SELECT prt.user_id 
                FROM password_reset_tokens prt 
                WHERE prt.token = ? AND prt.verified = TRUE AND prt.expires_at > NOW() AND prt.used = FALSE
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $access_granted = true;
                $user_id = $result['user_id'];
            }
        } catch (PDOException $e) {
            error_log("Database error in process_reset_password.php: " . $e->getMessage());
        }
    }
    
    if (!$access_granted) {
        header('Location: forgot_password.php');
        exit;
    }
    
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // $user_id and $token are already set from the verification above
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        header('Location: reset_password.php?error=mismatch');
        exit;
    }
    
    // Validate password strength
    if (!isStrongPassword($password)) {
        header('Location: reset_password.php?error=weak');
        exit;
    }
    
    try {
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
        
        // Mark the reset token as used
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE token = ?");
        $stmt->execute([$token]);
        
        // Clear all admin sessions for this user (force re-login)
        $stmt = $pdo->prepare("DELETE FROM admin_sessions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Log the password reset for security
        $logMessage = "Password reset completed for user ID: $user_id at " . date('Y-m-d H:i:s') . "\n";
        if (!is_dir('../logs')) {
            mkdir('../logs', 0755, true);
        }
        file_put_contents('../logs/security.log', $logMessage, FILE_APPEND);
        
        // Clear session variables
        unset($_SESSION['reset_token']);
        unset($_SESSION['verified_reset_token']);
        unset($_SESSION['reset_user_id']);
        
        header('Location: login.php?reset=success');
        exit;
        
    } catch (PDOException $e) {
        header('Location: reset_password.php?error=failed');
        exit;
    }
}

function isStrongPassword($password) {
    // Minimum 8 characters
    if (strlen($password) < 8) return false;
    
    // Must contain uppercase letter
    if (!preg_match('/[A-Z]/', $password)) return false;
    
    // Must contain lowercase letter
    if (!preg_match('/[a-z]/', $password)) return false;
    
    // Must contain number
    if (!preg_match('/[0-9]/', $password)) return false;
    
    // Must contain special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    
    return true;
}

header('Location: reset_password.php');
exit;
?>
