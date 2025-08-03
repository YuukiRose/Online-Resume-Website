<?php
/**
 * Security configuration for encryption
 * Uses secure JSON-based key management system
 */

require_once __DIR__ . '/SecureKeyManager.php';

// Initialize the secure key manager
try {
    SecureKeyManager::init();
    
    // Check if keys are expired and log warning
    if (SecureKeyManager::areKeysExpired()) {
        error_log('WARNING: Encryption keys have expired. Please regenerate using generate_keys.php');
    }
    
} catch (Exception $e) {
    // Fallback to environment or hardcoded key if JSON keys not available
    error_log('Key Manager Error: ' . $e->getMessage());
    
    // Load environment loader as fallback
    if (file_exists(__DIR__ . '/EnvLoader.php')) {
        require_once 'EnvLoader.php';
        EnvLoader::load();
    }
}

/**
 * Get encryption key with multiple fallback methods
 * Priority: JSON keys > Environment variable > Hardcoded fallback
 */
function getEncryptionKey() {
    try {
        // Try to get from secure JSON keys first
        return SecureKeyManager::getEncryptionKey();
    } catch (Exception $e) {
        // Fallback to environment variable
        if (function_exists('EnvLoader::get')) {
            $envKey = EnvLoader::get('ENCRYPTION_KEY', null);
            if ($envKey !== null && $envKey !== 'RW2025_Secure_Key_Change_This_To_Random_String_32chars!') {
                return $envKey;
            }
        }
        
        // Final fallback (not recommended for production)
        error_log('WARNING: Using fallback encryption key. Please run generate_keys.php to create secure keys.');
        return 'RW2025_Secure_Key_Change_This_To_Random_String_32chars!';
    }
}

/**
 * Get other security keys with fallbacks
 */
function getJWTSecret() {
    try {
        return SecureKeyManager::getJWTSecret();
    } catch (Exception $e) {
        return hash('sha256', getEncryptionKey() . 'jwt_salt');
    }
}

function getSessionKey() {
    try {
        return SecureKeyManager::getSessionKey();
    } catch (Exception $e) {
        return hash('sha256', getEncryptionKey() . 'session_salt');
    }
}

function getCSRFSecret() {
    try {
        return SecureKeyManager::getCSRFSecret();
    } catch (Exception $e) {
        return hash('sha256', getEncryptionKey() . 'csrf_salt');
    }
}

// Return the security configuration
return [
    'encryption_key' => getEncryptionKey(),
    'encryption_method' => 'AES-256-CBC',
    'jwt_secret' => getJWTSecret(),
    'session_key' => getSessionKey(),
    'csrf_secret' => getCSRFSecret(),
    'password_algorithm' => PASSWORD_ARGON2ID,
    'password_options' => [
        'memory_cost' => 65536,  // 64 MB
        'time_cost' => 4,        // 4 iterations
        'threads' => 3           // 3 threads
    ],
    'key_info' => function() {
        try {
            return SecureKeyManager::getKeyInfo();
        } catch (Exception $e) {
            return ['status' => 'fallback', 'message' => 'Using fallback keys'];
        }
    }
];
?>
