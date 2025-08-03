<?php
session_start();
require_once '../config/database.php';
require_once '../config/TwoFactorAuthService.php';
require_once '../config/EmailVerificationService.php';

$response = ['success' => false, 'message' => '', 'redirect' => ''];

try {
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All fields are required.");
        }
    }
    
    // Check terms agreement
    if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
        $response['redirect'] = 'register.php?error=terms_required';
        throw new Exception("You must agree to the terms and conditions.");
    }
    
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $linkedinProfile = trim($_POST['linkedin_profile'] ?? '');
    
    // Validation
    if (strlen($username) < 3) {
        throw new Exception("Username must be at least 3 characters long.");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }
    
    if ($password !== $confirmPassword) {
        $response['redirect'] = 'register.php?error=password_mismatch&' . http_build_query([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email
        ]);
        throw new Exception("Passwords do not match.");
    }
    
    // Password strength validation
    if (!isPasswordStrong($password)) {
        $response['redirect'] = 'register.php?error=weak_password&' . http_build_query([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email
        ]);
        throw new Exception("Password does not meet security requirements.");
    }
    
    // LinkedIn profile validation (if provided)
    if (!empty($linkedinProfile)) {
        if (!filter_var($linkedinProfile, FILTER_VALIDATE_URL) || 
            !preg_match('/linkedin\.com\/in\//', $linkedinProfile)) {
            throw new Exception("Please enter a valid LinkedIn profile URL.");
        }
    }
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        if ($existingUser['email'] === $email) {
            $response['redirect'] = 'register.php?error=email_exists&' . http_build_query([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username
            ]);
            throw new Exception("Email already exists.");
        } else {
            $response['redirect'] = 'register.php?error=username_exists&' . http_build_query([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email
            ]);
            throw new Exception("Username already exists.");
        }
    }
    
    // Create user account (initially inactive)
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, linkedin_profile, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'inactive', NOW())
    ");
    
    if (!$stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $linkedinProfile ?: null])) {
        throw new Exception("Failed to create user account.");
    }
    
    $userId = $pdo->lastInsertId();
    
    // Initialize 2FA service and send verification email
    $twoFaService = new TwoFactorAuthService($pdo);
    $emailService = new EmailVerificationService();
    
    // Create verification token
    $verificationData = $twoFaService->createVerificationToken($userId, $email, 'account_activation');
    
    // Send verification email
    $emailSent = $emailService->sendAccountActivationEmail(
        $email,
        $username,
        $verificationData['verification_code'],
        $verificationData['token']
    );
    
    if (!$emailSent) {
        // Rollback user creation if email failed
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $response['redirect'] = 'register.php?error=email_failed&' . http_build_query([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email
        ]);
        throw new Exception("Failed to send verification email.");
    }
    
    // Store verification token in session for the verification process
    $_SESSION['pending_activation'] = [
        'user_id' => $userId,
        'email' => $email,
        'username' => $username,
        'token' => $verificationData['token']
    ];
    
    $response['success'] = true;
    $response['message'] = 'Account created successfully! Please check your email for verification instructions.';
    $response['redirect'] = 'verify_account.php?token=' . urlencode($verificationData['token']);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    
    if (empty($response['redirect'])) {
        $response['redirect'] = 'register.php?error=registration_failed';
    }
}

// Handle AJAX and regular form submissions
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        header('Location: ' . $response['redirect']);
    } else {
        header('Location: ' . $response['redirect']);
    }
}
exit;

/**
 * Check if password meets strength requirements
 * @param string $password Password to check
 * @return bool True if password is strong enough
 */
function isPasswordStrong($password) {
    // At least 8 characters
    if (strlen($password) < 8) {
        return false;
    }
    
    // Must contain uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Must contain lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Must contain number
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // Must contain special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}
?>
