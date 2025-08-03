<?php
/**
 * Secure Key Generator
 * Generates cryptographically secure keys and stores them in JSON format
 */
require_once 'admin_auth_check.php';

// Ensure this script is run from command line or has proper authentication
if (php_sapi_name() !== 'cli' && !isset($_GET['force_generate'])) {
    die('This script should be run from command line for security. Add ?force_generate=1 to run from web (not recommended in production).');
}

/**
 * Generate a cryptographically secure random key
 * @param int $length Length of the key in bytes
 * @return string Base64 encoded key
 */
function generateSecureKey($length = 32) {
    try {
        $randomBytes = random_bytes($length);
        return base64_encode($randomBytes);
    } catch (Exception $e) {
        // Fallback to openssl_random_pseudo_bytes if random_bytes fails
        $randomBytes = openssl_random_pseudo_bytes($length, $strong);
        if (!$strong) {
            throw new Exception('Unable to generate cryptographically secure random bytes');
        }
        return base64_encode($randomBytes);
    }
}

/**
 * Generate multiple keys for different purposes
 * @return array Array of generated keys
 */
function generateKeySet() {
    return [
        'encryption_key' => generateSecureKey(32),      // 256-bit key for AES-256
        'jwt_secret' => generateSecureKey(64),          // 512-bit key for JWT signing
        'session_key' => generateSecureKey(32),         // 256-bit key for session encryption
        'csrf_secret' => generateSecureKey(32),         // 256-bit key for CSRF tokens
        'api_key' => generateSecureKey(48),             // 384-bit key for API authentication
        'password_salt' => generateSecureKey(32),       // 256-bit salt for password hashing
        'generated_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year')), // Keys expire in 1 year
        'version' => '1.0'
    ];
}

// Path to store the keys
$keysPath = __DIR__ . '/keys.json';
$backupPath = __DIR__ . '/keys_backup_' . date('Y-m-d_H-i-s') . '.json';

try {
    // Backup existing keys if they exist
    if (file_exists($keysPath)) {
        $existingKeys = file_get_contents($keysPath);
        file_put_contents($backupPath, $existingKeys);
        echo "✅ Backed up existing keys to: " . basename($backupPath) . "\n";
    }
    
    // Generate new key set
    echo "🔑 Generating new cryptographically secure keys...\n";
    $keys = generateKeySet();
    
    // Save to JSON file with proper permissions
    $jsonKeys = json_encode($keys, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if (file_put_contents($keysPath, $jsonKeys, LOCK_EX) === false) {
        throw new Exception('Failed to write keys to file');
    }
    
    // Set restrictive file permissions (owner read/write only)
    if (function_exists('chmod')) {
        chmod($keysPath, 0600);
    }
    
    echo "✅ Keys generated successfully and saved to: keys.json\n";
    echo "🔒 File permissions set to 600 (owner read/write only)\n";
    echo "📊 Generated keys:\n";
    foreach ($keys as $keyName => $keyValue) {
        if (in_array($keyName, ['generated_at', 'expires_at', 'version'])) {
            echo "   - {$keyName}: {$keyValue}\n";
        } else {
            echo "   - {$keyName}: " . substr($keyValue, 0, 16) . "... (truncated for security)\n";
        }
    }
    
    echo "\n⚠️  IMPORTANT SECURITY NOTES:\n";
    echo "   - Keep the keys.json file secure and never commit it to version control\n";
    echo "   - Add keys.json to your .gitignore file\n";
    echo "   - Keys expire on: " . $keys['expires_at'] . "\n";
    echo "   - Backup files are created automatically when regenerating keys\n";
    
} catch (Exception $e) {
    echo "❌ Error generating keys: " . $e->getMessage() . "\n";
    exit(1);
}
?>
