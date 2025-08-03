<?php
/**
 * Secure Key Manager
 * Loads and manages cryptographically secure keys from JSON storage
 */

class SecureKeyManager {
    private static $keys = null;
    private static $keysPath = null;
    
    /**
     * Initialize the key manager
     */
    public static function init($keysPath = null) {
        if ($keysPath === null) {
            // First try secure directory, fallback to config directory for backward compatibility
            $secureKeysPath = __DIR__ . '/../secure/keys.json';
            $configKeysPath = __DIR__ . '/keys.json';
            
            if (file_exists($secureKeysPath)) {
                self::$keysPath = $secureKeysPath;
            } elseif (file_exists($configKeysPath)) {
                self::$keysPath = $configKeysPath;
            } else {
                self::$keysPath = $secureKeysPath; // Default to secure path for new installations
            }
        } else {
            self::$keysPath = $keysPath;
        }
        
        self::loadKeys();
    }
    
    /**
     * Load keys from JSON file
     */
    private static function loadKeys() {
        if (!file_exists(self::$keysPath)) {
            throw new Exception('Keys file not found. Run generate_keys.php first to create secure keys.');
        }
        
        $keysContent = file_get_contents(self::$keysPath);
        if ($keysContent === false) {
            throw new Exception('Failed to read keys file. Check file permissions.');
        }
        
        self::$keys = json_decode($keysContent, true);
        if (self::$keys === null) {
            throw new Exception('Invalid keys file format. Keys file may be corrupted.');
        }
        
        // Check if keys have expired
        if (isset(self::$keys['expires_at'])) {
            $expiryDate = new DateTime(self::$keys['expires_at']);
            $now = new DateTime();
            
            if ($now > $expiryDate) {
                error_log('WARNING: Encryption keys have expired. Please regenerate keys using generate_keys.php');
                // Still allow usage but log the warning
            }
        }
    }
    
    /**
     * Get a specific key by name
     * @param string $keyName Name of the key to retrieve
     * @param string $default Default value if key not found
     * @return string The key value
     */
    public static function getKey($keyName, $default = null) {
        if (self::$keys === null) {
            self::init();
        }
        
        if (!isset(self::$keys[$keyName])) {
            if ($default !== null) {
                return $default;
            }
            throw new Exception("Key '{$keyName}' not found in keys file.");
        }
        
        return self::$keys[$keyName];
    }
    
    /**
     * Get the encryption key for AES-256
     * @return string Base64 encoded encryption key
     */
    public static function getEncryptionKey() {
        return self::getKey('encryption_key');
    }
    
    /**
     * Get the JWT secret key
     * @return string Base64 encoded JWT secret
     */
    public static function getJWTSecret() {
        return self::getKey('jwt_secret');
    }
    
    /**
     * Get the session encryption key
     * @return string Base64 encoded session key
     */
    public static function getSessionKey() {
        return self::getKey('session_key');
    }
    
    /**
     * Get the CSRF secret key
     * @return string Base64 encoded CSRF secret
     */
    public static function getCSRFSecret() {
        return self::getKey('csrf_secret');
    }
    
    /**
     * Get the API key
     * @return string Base64 encoded API key
     */
    public static function getAPIKey() {
        return self::getKey('api_key');
    }
    
    /**
     * Get the password salt
     * @return string Base64 encoded password salt
     */
    public static function getPasswordSalt() {
        return self::getKey('password_salt');
    }
    
    /**
     * Check if keys are expired
     * @return bool True if keys are expired
     */
    public static function areKeysExpired() {
        if (self::$keys === null) {
            self::init();
        }
        
        if (!isset(self::$keys['expires_at'])) {
            return false; // No expiry set
        }
        
        $expiryDate = new DateTime(self::$keys['expires_at']);
        $now = new DateTime();
        
        return $now > $expiryDate;
    }
    
    /**
     * Get key information
     * @return array Key metadata
     */
    public static function getKeyInfo() {
        if (self::$keys === null) {
            self::init();
        }
        
        return [
            'generated_at' => self::$keys['generated_at'] ?? 'Unknown',
            'expires_at' => self::$keys['expires_at'] ?? 'No expiry',
            'version' => self::$keys['version'] ?? '1.0',
            'expired' => self::areKeysExpired(),
            'total_keys' => count(array_filter(array_keys(self::$keys), function($key) {
                return !in_array($key, ['generated_at', 'expires_at', 'version']);
            }))
        ];
    }
    
    /**
     * Get all encryption keys (for rotation purposes)
     * @return array All encryption keys
     */
    public function getEncryptionKeys() {
        if (self::$keys === null) {
            self::init();
        }
        return self::$keys;
    }
    
    /**
     * Generate new encryption keys
     * @return array New encryption keys
     */
    public function generateNewKeys() {
        $newKeys = [
            'encryption_key' => base64_encode(random_bytes(32)), // 256-bit key for AES-256
            'jwt_secret' => base64_encode(random_bytes(64)),     // 512-bit secret for JWT
            'hash_salt' => base64_encode(random_bytes(32)),     // 256-bit salt for hashing
            'csrf_token' => base64_encode(random_bytes(32)),    // 256-bit CSRF token
            'session_key' => base64_encode(random_bytes(32)),   // 256-bit session key
            'generated_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')), // Keys expire after 1 year
            'version' => '2.0'
        ];
        
        return $newKeys;
    }
    
    /**
     * Save new keys to file
     * @param array $keys New keys to save
     * @return bool Success status
     */
    public function saveKeys($keys) {
        // Ensure the secure directory exists
        $secureDir = dirname(self::$keysPath);
        if (!is_dir($secureDir)) {
            mkdir($secureDir, 0755, true);
        }
        
        $json = json_encode($keys, JSON_PRETTY_PRINT);
        $result = file_put_contents(self::$keysPath, $json);
        
        if ($result !== false) {
            // Update the loaded keys
            self::$keys = $keys;
            return true;
        }
        
        return false;
    }
    
    /**
     * Encrypt data with a specific key
     * @param string $data Data to encrypt
     * @param string $key Encryption key (base64 encoded)
     * @return string|false Encrypted data or false on failure
     */
    public function encryptData($data, $key) {
        try {
            $keyDecoded = base64_decode($key);
            $iv = random_bytes(16); // AES-256-CBC needs 16-byte IV
            $encrypted = openssl_encrypt($data, 'AES-256-CBC', $keyDecoded, 0, $iv);
            
            if ($encrypted === false) {
                return false;
            }
            
            // Combine IV and encrypted data
            return base64_encode($iv . $encrypted);
        } catch (Exception $e) {
            error_log("Encryption error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrypt data with a specific key
     * @param string $encryptedData Encrypted data (base64 encoded)
     * @param string $key Decryption key (base64 encoded)
     * @return string|false Decrypted data or false on failure
     */
    public function decryptData($encryptedData, $key) {
        try {
            $keyDecoded = base64_decode($key);
            $data = base64_decode($encryptedData);
            
            if ($data === false || strlen($data) < 16) {
                return false;
            }
            
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $keyDecoded, 0, $iv);
            
            return $decrypted;
        } catch (Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Regenerate keys (calls the generator script)
     * @return bool Success status
     */
    public static function regenerateKeys() {
        $generatorPath = __DIR__ . '/generate_keys.php';
        if (!file_exists($generatorPath)) {
            throw new Exception('Key generator script not found.');
        }
        
        // Execute the generator script
        $output = [];
        $returnVar = 0;
        exec("php \"$generatorPath\"", $output, $returnVar);
        
        if ($returnVar === 0) {
            // Reload keys after regeneration
            self::$keys = null;
            self::loadKeys();
            return true;
        }
        
        return false;
    }
}
?>
