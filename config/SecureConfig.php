<?php
/**
 * Encryption/Decryption utilities for sensitive data
 */

require_once 'SecureKeyManager.php';

class SecureConfig {
    private static $encryptionKey = null;
    private static $method = 'AES-256-CBC';
    
    private static function getEncryptionKey() {
        if (self::$encryptionKey === null) {
            SecureKeyManager::init();
            self::$encryptionKey = hash('sha256', SecureKeyManager::getKey('encryption_key'));
        }
        return self::$encryptionKey;
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$method));
        $encrypted = openssl_encrypt($data, self::$method, self::getEncryptionKey(), 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($data) {
        try {
            list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
            return openssl_decrypt($encrypted_data, self::$method, self::getEncryptionKey(), 0, $iv);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if data is encrypted (base64 with :: separator)
     */
    public static function isEncrypted($data) {
        $decoded = base64_decode($data);
        return strpos($decoded, '::') !== false;
    }
    
    /**
     * Safely get email configuration with decryption
     */
    public static function getEmailConfig() {
        $config = include 'email.php';
        
        // Decrypt password if it's encrypted
        if (isset($config['smtp_password']) && self::isEncrypted($config['smtp_password'])) {
            $config['smtp_password'] = self::decrypt($config['smtp_password']);
        }
        
        return $config;
    }
}
?>
