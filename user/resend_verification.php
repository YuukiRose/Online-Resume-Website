<?php
session_start();
require_once '../config/database.php';
require_once '../config/TwoFactorAuthService.php';
require_once '../config/EmailVerificationService.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Check if token is provided
    if (empty($_POST['token'])) {
        throw new Exception("Invalid verification token.");
    }
    
    $token = trim($_POST['token']);
    
    // Initialize services
    $twoFaService = new TwoFactorAuthService($pdo);
    $emailService = new EmailVerificationService();
    
    // Get existing verification record
    $stmt = $pdo->prepare("
        SELECT user_id, email, purpose, created_at 
        FROM email_verifications 
        WHERE token = ? AND purpose = 'account_activation'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verification) {
        throw new Exception("Invalid or expired verification token.");
    }
    
    $userId = $verification['user_id'];
    $email = $verification['email'];
    
    // Check if user account exists and is still inactive
    $stmt = $pdo->prepare("SELECT username, status FROM users WHERE id = ? AND email = ?");
    $stmt->execute([$userId, $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User account not found.");
    }
    
    if ($user['status'] === 'active') {
        throw new Exception("Account is already activated. You can log in now.");
    }
    
    // Check rate limiting (prevent spam)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as recent_count 
        FROM email_verifications 
        WHERE user_id = ? AND purpose = 'account_activation' 
        AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute([$userId]);
    $recentCount = $stmt->fetchColumn();
    
    if ($recentCount >= 3) {
        throw new Exception("Too many verification attempts. Please wait 5 minutes before requesting another code.");
    }
    
    // Create new verification token
    $verificationData = $twoFaService->createVerificationToken($userId, $email, 'account_activation');
    
    // Send new verification email
    $emailSent = $emailService->sendAccountActivationEmail(
        $email,
        $user['username'],
        $verificationData['verification_code'],
        $verificationData['token']
    );
    
    if (!$emailSent) {
        throw new Exception("Failed to send verification email. Please try again later.");
    }
    
    // Update session with new token
    if (isset($_SESSION['pending_activation'])) {
        $_SESSION['pending_activation']['token'] = $verificationData['token'];
    }
    
    $response['success'] = true;
    $response['message'] = 'New verification code sent successfully! Please check your email.';
    
    // Log successful resend
    error_log("Verification code resent for user ID: $userId, email: $email");
    
} catch (Exception $e) {
    error_log("Resend verification error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
