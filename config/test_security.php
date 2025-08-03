<?php
/**
 * Test script for the new secure key system
 */
require_once 'admin_auth_check.php';

echo "🔐 Testing Secure Key System\n";
echo "============================\n\n";

try {
    // Test loading the security configuration
    $securityConfig = require __DIR__ . '/security.php';
    
    echo "✅ Security configuration loaded successfully\n";
    echo "📊 Available configuration:\n";
    
    // Test each key type
    $keyTypes = ['encryption_key', 'jwt_secret', 'session_key', 'csrf_secret'];
    
    foreach ($keyTypes as $keyType) {
        if (isset($securityConfig[$keyType])) {
            $key = $securityConfig[$keyType];
            $keyLength = strlen(base64_decode($key)) * 8; // Convert to bits
            echo "   - $keyType: {$keyLength}-bit key (" . substr($key, 0, 16) . "...)\n";
        } else {
            echo "   ❌ $keyType: Missing\n";
        }
    }
    
    echo "\n🔍 Key Information:\n";
    if (isset($securityConfig['key_info']) && is_callable($securityConfig['key_info'])) {
        $keyInfo = $securityConfig['key_info']();
        foreach ($keyInfo as $key => $value) {
            echo "   - $key: " . (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) . "\n";
        }
    }
    
    // Test encryption/decryption
    echo "\n🔒 Testing Encryption:\n";
    $testData = "This is a test message for encryption!";
    $encryptionKey = $securityConfig['encryption_key'];
    $method = $securityConfig['encryption_method'];
    
    // Encrypt
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    $encrypted = openssl_encrypt($testData, $method, base64_decode($encryptionKey), 0, $iv);
    $encryptedData = base64_encode($encrypted . '::' . $iv);
    
    echo "   - Original: $testData\n";
    echo "   - Encrypted: " . substr($encryptedData, 0, 32) . "...\n";
    
    // Decrypt
    list($encryptedText, $iv) = explode('::', base64_decode($encryptedData), 2);
    $decrypted = openssl_decrypt($encryptedText, $method, base64_decode($encryptionKey), 0, $iv);
    
    echo "   - Decrypted: $decrypted\n";
    
    if ($testData === $decrypted) {
        echo "   ✅ Encryption/Decryption test: PASSED\n";
    } else {
        echo "   ❌ Encryption/Decryption test: FAILED\n";
    }
    
    echo "\n🎉 All tests completed successfully!\n";
    echo "\n💡 Next steps:\n";
    echo "   1. Update any existing code to use the new security config\n";
    echo "   2. Remove any hardcoded keys from your codebase\n";
    echo "   3. Ensure keys.json is in your .gitignore file\n";
    echo "   4. Set up key rotation reminders (keys expire in 1 year)\n";
    
} catch (Exception $e) {
    echo "❌ Error testing security system: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure you've run generate_keys.php first\n";
    echo "2. Check that keys.json exists in the config directory\n";
    echo "3. Verify file permissions on keys.json\n";
}
?>
