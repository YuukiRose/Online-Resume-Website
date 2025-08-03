<?php
/**
 * Two-Factor Authentication Service
 * Handles email verification codes for various authentication scenarios
 */

require_once __DIR__ . '/SecureKeyManager.php';

class TwoFactorAuthService {
    private $pdo;
    private $csrfSecret;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        try {
            $this->csrfSecret = SecureKeyManager::getCSRFSecret();
        } catch (Exception $e) {
            // Fallback if secure keys not available
            $this->csrfSecret = 'fallback_csrf_secret_key_2fa_service';
        }
    }
    
    /**
     * Generate a 6-digit verification code
     * @return string 6-digit numeric code
     */
    public function generateVerificationCode() {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a secure token for verification process
     * @param int $length Token length in bytes
     * @return string Base64 encoded token
     */
    public function generateSecureToken($length = 32) {
        try {
            return base64_encode(random_bytes($length));
        } catch (Exception $e) {
            // Fallback using openssl
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if (!$strong) {
                throw new Exception('Unable to generate cryptographically secure token');
            }
            return base64_encode($bytes);
        }
    }
    
    /**
     * Store verification code for password reset
     * @param int $userId User ID
     * @param string $email Email address
     * @param string $purpose Purpose: 'password_reset', 'email_verification', 'account_activation'
     * @return array Contains token and verification code
     */
    public function createVerificationToken($userId, $email, $purpose = 'password_reset') {
        $token = $this->generateSecureToken();
        $verificationCode = $this->generateVerificationCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes')); // 15-minute expiry
        
        try {
            // For password reset, use existing table
            if ($purpose === 'password_reset') {
                $stmt = $this->pdo->prepare("
                    INSERT INTO password_reset_tokens (user_id, token, verification_code, expires_at, used, verified) 
                    VALUES (?, ?, ?, ?, FALSE, FALSE)
                ");
                $stmt->execute([$userId, $token, $verificationCode, $expiresAt]);
            } else {
                // For other purposes, use the general verification table
                $this->ensureVerificationTableExists();
                $stmt = $this->pdo->prepare("
                    INSERT INTO email_verifications (user_id, email, token, verification_code, purpose, expires_at, used, verified) 
                    VALUES (?, ?, ?, ?, ?, ?, FALSE, FALSE)
                ");
                $stmt->execute([$userId, $email, $token, $verificationCode, $purpose, $expiresAt]);
            }
            
            return [
                'token' => $token,
                'verification_code' => $verificationCode,
                'expires_at' => $expiresAt
            ];
            
        } catch (PDOException $e) {
            error_log("Error creating verification token: " . $e->getMessage());
            throw new Exception('Failed to create verification token');
        }
    }
    
    /**
     * Verify the submitted code against stored token
     * @param string $token Verification token
     * @param string $submittedCode User-submitted code
     * @param string $purpose Verification purpose
     * @return array Result with success status and user info
     */
    public function verifyCode($token, $submittedCode, $purpose = 'password_reset') {
        try {
            if ($purpose === 'password_reset') {
                $stmt = $this->pdo->prepare("
                    SELECT prt.*, au.email, au.username 
                    FROM password_reset_tokens prt 
                    JOIN admin_users au ON prt.user_id = au.id 
                    WHERE prt.token = ? AND prt.expires_at > NOW() AND prt.used = FALSE
                ");
                $stmt->execute([$token]);
            } else {
                $this->ensureVerificationTableExists();
                $stmt = $this->pdo->prepare("
                    SELECT ev.*, u.email as user_email, u.username 
                    FROM email_verifications ev 
                    LEFT JOIN users u ON ev.user_id = u.id 
                    WHERE ev.token = ? AND ev.purpose = ? AND ev.expires_at > NOW() AND ev.used = FALSE
                ");
                $stmt->execute([$token, $purpose]);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'success' => false,
                    'error' => 'Invalid or expired verification token'
                ];
            }
            
            // Check if code matches
            if ($result['verification_code'] !== $submittedCode) {
                return [
                    'success' => false,
                    'error' => 'Invalid verification code'
                ];
            }
            
            // Mark as verified
            if ($purpose === 'password_reset') {
                $updateStmt = $this->pdo->prepare("
                    UPDATE password_reset_tokens 
                    SET verified = TRUE 
                    WHERE token = ?
                ");
            } else {
                $updateStmt = $this->pdo->prepare("
                    UPDATE email_verifications 
                    SET verified = TRUE 
                    WHERE token = ?
                ");
            }
            $updateStmt->execute([$token]);
            
            return [
                'success' => true,
                'user_id' => $result['user_id'],
                'email' => $result['email'] ?? $result['user_email'],
                'username' => $result['username'] ?? null,
                'token' => $token
            ];
            
        } catch (PDOException $e) {
            error_log("Error verifying code: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error during verification'
            ];
        }
    }
    
    /**
     * Mark verification token as used
     * @param string $token Verification token
     * @param string $purpose Verification purpose
     * @return bool Success status
     */
    public function markTokenAsUsed($token, $purpose = 'password_reset') {
        try {
            if ($purpose === 'password_reset') {
                $stmt = $this->pdo->prepare("
                    UPDATE password_reset_tokens 
                    SET used = TRUE 
                    WHERE token = ?
                ");
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE email_verifications 
                    SET used = TRUE 
                    WHERE token = ?
                ");
            }
            
            return $stmt->execute([$token]);
            
        } catch (PDOException $e) {
            error_log("Error marking token as used: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired verification tokens
     * @param string $purpose Optional: clean specific purpose only
     * @return int Number of tokens cleaned
     */
    public function cleanupExpiredTokens($purpose = null) {
        try {
            $deletedCount = 0;
            
            // Clean password reset tokens
            if ($purpose === null || $purpose === 'password_reset') {
                $stmt = $this->pdo->prepare("DELETE FROM password_reset_tokens WHERE expires_at <= NOW()");
                $stmt->execute();
                $deletedCount += $stmt->rowCount();
            }
            
            // Clean email verification tokens
            if ($purpose === null || $purpose !== 'password_reset') {
                $this->ensureVerificationTableExists();
                if ($purpose) {
                    $stmt = $this->pdo->prepare("DELETE FROM email_verifications WHERE expires_at <= NOW() AND purpose = ?");
                    $stmt->execute([$purpose]);
                } else {
                    $stmt = $this->pdo->prepare("DELETE FROM email_verifications WHERE expires_at <= NOW()");
                    $stmt->execute();
                }
                $deletedCount += $stmt->rowCount();
            }
            
            return $deletedCount;
            
        } catch (PDOException $e) {
            error_log("Error cleaning up expired tokens: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create email verifications table if it doesn't exist
     */
    private function ensureVerificationTableExists() {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS email_verifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    email VARCHAR(255) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    verification_code VARCHAR(6) NOT NULL,
                    purpose ENUM('email_verification', 'account_activation', 'login_verification') NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used BOOLEAN DEFAULT FALSE,
                    verified BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_token (token),
                    INDEX idx_email_purpose (email, purpose),
                    INDEX idx_expires (expires_at)
                )
            ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating email_verifications table: " . $e->getMessage());
            throw new Exception('Failed to create verification table');
        }
    }
    
    /**
     * Get verification status for a user/email
     * @param mixed $userIdOrEmail User ID or email address
     * @param string $purpose Verification purpose
     * @return array Verification status info
     */
    public function getVerificationStatus($userIdOrEmail, $purpose = 'email_verification') {
        try {
            $this->ensureVerificationTableExists();
            
            if (is_numeric($userIdOrEmail)) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM email_verifications 
                    WHERE user_id = ? AND purpose = ? 
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$userIdOrEmail, $purpose]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM email_verifications 
                    WHERE email = ? AND purpose = ? 
                    ORDER BY created_at DESC LIMIT 1
                ");
                $stmt->execute([$userIdOrEmail, $purpose]);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'has_verification' => false,
                    'verified' => false
                ];
            }
            
            $isExpired = strtotime($result['expires_at']) < time();
            
            return [
                'has_verification' => true,
                'verified' => $result['verified'] && !$isExpired,
                'expired' => $isExpired,
                'created_at' => $result['created_at'],
                'expires_at' => $result['expires_at']
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting verification status: " . $e->getMessage());
            return [
                'has_verification' => false,
                'verified' => false,
                'error' => 'Database error'
            ];
        }
    }
}
?>
