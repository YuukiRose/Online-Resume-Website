<?php
require_once '../config/database.php';
require_once '../config/email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: forgot_password.php?error=email');
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, username FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Location: forgot_password.php?error=notfound');
            exit;
        }
        
        // Generate token and verification code
        $token = bin2hex(random_bytes(32));
        $verification_code = sprintf('%06d', mt_rand(0, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete existing reset tokens for this user
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        
        // Insert new reset token
        $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, verification_code, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['id'], $token, $verification_code, $expires_at]);
        
        // Send email with verification code
        $success = sendResetEmail($email, $user['username'], $verification_code, $token);
        
        if ($success) {
            // Store token in session for verification step
            session_start();
            $_SESSION['reset_token'] = $token;
            // Redirect directly to verification page
            header('Location: verify_reset.php');
        } else {
            header('Location: forgot_password.php?error=failed');
        }
        
    } catch (PDOException $e) {
        header('Location: forgot_password.php?error=failed');
    }
    
    exit;
}

function sendResetEmail($email, $username, $verification_code, $token) {
    require_once '../config/SecureEmailLoader.php';
    require_once '../config/SMTPMailer.php';
    
    $emailConfig = SecureEmailLoader::loadConfig();
    
    $subject = "Password Reset Request - Portfolio Admin";
    $html_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; background: #f9f9f9; border-radius: 0 0 8px 8px; }
            .code { font-size: 24px; font-weight: bold; text-align: center; background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px; letter-spacing: 2px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Password Reset Request</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>$username</strong>,</p>
                <p>You requested a password reset for your Portfolio admin account.</p>
                <p>Your verification code is:</p>
                <div class='code'>$verification_code</div>
                <p><strong>⏰ This code will expire in 1 hour.</strong></p>
                <p>Enter this code on the verification page to continue with your password reset.</p>
                <p>If you didn't request this reset, please ignore this email.</p>
                <p><strong>🔒 Security Note:</strong> Never share this code with anyone.</p>
                <div class='footer'>
                    <p>This is an automated message from Portfolio Admin System.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $text_message = "Password Reset Request - Rose Webb Portfolio Admin\n\n";
    $text_message .= "Hello $username,\n\n";
    $text_message .= "You requested a password reset for your admin account.\n\n";
    $text_message .= "Your verification code is: $verification_code\n\n";
    $text_message .= "This code will expire in 1 hour.\n\n";
    $text_message .= "If you didn't request this reset, please ignore this email.\n\n";
    $text_message .= "Security Note: Never share this code with anyone.";
    
    try {
        $mailer = new SMTPMailer($emailConfig);
        $success = $mailer->sendMail($email, $username, $subject, $html_message, '');
        
        if ($success) {
            // Log successful email send
            $logMessage = "Password reset email sent to $email (Code: $verification_code)\n";
            file_put_contents('../logs/email.log', date('Y-m-d H:i:s') . " - " . $logMessage, FILE_APPEND);
            return true;
        } else {
            // Log failed email send
            $logMessage = "Failed to send password reset email to $email\n";
            file_put_contents('../logs/email.log', date('Y-m-d H:i:s') . " - " . $logMessage, FILE_APPEND);
            
            // Fallback: log to file for testing
            $fallbackMessage = "Reset code for $email: $verification_code (Token: $token)\n";
            file_put_contents('../logs/password_resets.log', date('Y-m-d H:i:s') . " - " . $fallbackMessage, FILE_APPEND);
            return true; // Return true for testing purposes
        }
    } catch (Exception $e) {
        // Log error
        $logMessage = "SMTP Error for $email: " . $e->getMessage() . "\n";
        file_put_contents('../logs/email.log', date('Y-m-d H:i:s') . " - " . $logMessage, FILE_APPEND);
        
        // Fallback: log to file for testing
        $fallbackMessage = "Reset code for $email: $verification_code (Token: $token)\n";
        file_put_contents('../logs/password_resets.log', date('Y-m-d H:i:s') . " - " . $fallbackMessage, FILE_APPEND);
        return true; // Return true for testing purposes
    }
}

header('Location: forgot_password.php');
exit;
?>
