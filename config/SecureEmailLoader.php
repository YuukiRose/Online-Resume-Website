<?php
/**
 * Secure Email Configuration Loader
 * Loads encrypted email configuration from E.env file
 */

require_once 'SecureKeyManager.php';

class SecureEmailLoader {
    private static $config = null;
    private static $encryptionKey = null;
    
    /**
     * Load encrypted email configuration
     */
    public static function loadConfig() {
        if (self::$config !== null) {
            return self::$config;
        }
        
        // Initialize encryption key
        if (self::$encryptionKey === null) {
            SecureKeyManager::init();
            self::$encryptionKey = SecureKeyManager::getKey('encryption_key');
        }
        
        $envPath = __DIR__ . '/../secure/E.env';
        
        // Fallback to old configuration if E.env doesn't exist
        if (!file_exists($envPath)) {
            return self::loadFallbackConfig();
        }
        
        try {
            $envContent = file_get_contents($envPath);
            if ($envContent === false) {
                throw new Exception('Failed to read E.env file');
            }
            
            $lines = explode("\n", $envContent);
            $config = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $config[strtolower($key)] = self::decrypt($value);
                }
            }
            
            // Validate required fields
            $required = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email'];
            foreach ($required as $field) {
                if (!isset($config[$field]) || empty($config[$field])) {
                    throw new Exception("Missing required email configuration: $field");
                }
            }
            
            // Set defaults for optional fields
            $config['from_name'] = $config['from_name'] ?? 'Portfolio Admin';
            $config['encryption'] = $config['encryption'] ?? 'ssl';
            
            self::$config = $config;
            return self::$config;
            
        } catch (Exception $e) {
            error_log("SecureEmailLoader Error: " . $e->getMessage());
            return self::loadFallbackConfig();
        }
    }
    
    /**
     * AES-256-GCM decryption
     */
    private static function decrypt($encryptedData) {
        $cipher = 'aes-256-gcm';
        $data = base64_decode($encryptedData);
        
        if ($data === false || strlen($data) < 32) {
            throw new Exception('Invalid encrypted data');
        }
        
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt($encrypted, $cipher, self::$encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }
        
        return $decrypted;
    }
    
    /**
     * Fallback to old email configuration
     */
    private static function loadFallbackConfig() {
        // Try to load the old email.php configuration
        $emailConfigPath = __DIR__ . '/email.php';
        if (file_exists($emailConfigPath)) {
            $config = include $emailConfigPath;
            if (is_array($config)) {
                self::$config = $config;
                return self::$config;
            }
        }
        
        // Ultimate fallback with basic configuration
        self::$config = [
            'smtp_host' => 'smtp.livemail.co.uk',
            'smtp_port' => 465,
            'smtp_username' => 'admin@webbr.co.uk',
            'smtp_password' => '', // Will need to be set manually
            'from_email' => 'admin@webbr.co.uk',
            'from_name' => 'Portfolio Admin',
            'encryption' => 'ssl'
        ];
        
        return self::$config;
    }
    
    /**
     * Get specific configuration value
     */
    public static function get($key, $default = null) {
        $config = self::loadConfig();
        return $config[$key] ?? $default;
    }
    
    /**
     * Check if encrypted email configuration exists
     */
    public static function hasEncryptedConfig() {
        $envPath = __DIR__ . '/../secure/E.env';
        return file_exists($envPath);
    }
    
    /**
     * Get configuration status
     */
    public static function getStatus() {
        if (self::hasEncryptedConfig()) {
            try {
                $config = self::loadConfig();
                return [
                    'status' => 'encrypted',
                    'source' => 'E.env (encrypted)',
                    'secure' => true,
                    'fields_configured' => count(array_filter($config))
                ];
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'source' => 'E.env (corrupted)',
                    'secure' => false,
                    'error' => $e->getMessage()
                ];
            }
        } else {
            return [
                'status' => 'fallback',
                'source' => 'email.php (unencrypted)',
                'secure' => false,
                'recommendation' => 'Use Email Encryption Setup to secure your configuration'
            ];
        }
    }
}
?>
