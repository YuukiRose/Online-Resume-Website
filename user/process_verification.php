<?php
session_start();
require_once '../config/database.php';
require_once '../config/TwoFactorAuthService.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'redirect' => ''];

try {
    // Validate required fields
    if (empty($_POST['token']) || empty($_POST['verification_code'])) {
        throw new Exception("Missing required verification data.");
    }
    
    $token = trim($_POST['token']);
    $verificationCode = trim($_POST['verification_code']);
    
    // Validate code format
    if (!preg_match('/^\d{6}$/', $verificationCode)) {
        throw new Exception("Invalid verification code format.");
    }
    
    // Initialize 2FA service
    $twoFaService = new TwoFactorAuthService($pdo);
    
    // Verify the token and code
    $verificationResult = $twoFaService->verifyToken($token, $verificationCode, 'account_activation');
    
    if (!$verificationResult['valid']) {
        throw new Exception($verificationResult['message']);
    }
    
    $userId = $verificationResult['user_id'];
    $email = $verificationResult['email'];
    
    // Activate the user account
    $stmt = $pdo->prepare("UPDATE users SET status = 'active', email_verified_at = NOW() WHERE id = ? AND email = ?");
    $success = $stmt->execute([$userId, $email]);
    
    if (!$success || $stmt->rowCount() === 0) {
        throw new Exception("Failed to activate account. Please try again.");
    }
    
    // Clean up verification tokens for this user
    $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ? AND purpose = 'account_activation'");
    $stmt->execute([$userId]);
    
    // Get user data for session
    $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User account not found.");
    }
    
    // Clear pending activation session data
    unset($_SESSION['pending_activation']);
    
    // Set success session message
    $_SESSION['success_message'] = "Your account has been successfully activated! You can now log in.";
    
    $response['success'] = true;
    $response['message'] = 'Account verified successfully! Redirecting to login...';
    $response['redirect'] = 'login.php?verified=1';
    
    // Log successful verification
    error_log("Account verification successful for user ID: $userId, email: $email");
    
} catch (Exception $e) {
    error_log("Account verification error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    
    // Specific error handling
    if (strpos($e->getMessage(), 'expired') !== false) {
        $response['redirect'] = 'resend_verification.php?expired=1';
    } elseif (strpos($e->getMessage(), 'invalid') !== false || strpos($e->getMessage(), 'incorrect') !== false) {
        // Stay on verification page for code retry
        $response['message'] = "Invalid verification code. Please check your email and try again.";
    }
}

echo json_encode($response);
exit;
?>
