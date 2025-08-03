<?php
/**
 * Security Setup Script
 * Easy setup for secure key generation and configuration
 */
require_once 'admin_auth_check.php';

echo "🔐 Security Setup for Luthor Portfolio\n";
echo "=====================================\n\n";

// Check PHP version and required extensions
$phpVersion = phpversion();
echo "📋 System Check:\n";
echo "   - PHP Version: $phpVersion\n";

$requiredExtensions = ['openssl', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ Extension '$ext': Available\n";
    } else {
        echo "   ❌ Extension '$ext': Missing\n";
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "\n❌ Missing required PHP extensions: " . implode(', ', $missingExtensions) . "\n";
    echo "Please install these extensions before proceeding.\n";
    exit(1);
}

echo "\n🔑 Key Management Options:\n";
echo "1. Generate new secure keys\n";
echo "2. Check existing keys status\n";
echo "3. View key information\n";
echo "4. Add keys.json to .gitignore\n";
echo "5. Exit\n\n";

// Get user choice
if (php_sapi_name() === 'cli') {
    echo "Enter your choice (1-5): ";
    $choice = trim(fgets(STDIN));
} else {
    $choice = $_GET['choice'] ?? '1';
    echo "Choice: $choice (via web interface)\n";
}

switch ($choice) {
    case '1':
        echo "\n🔄 Generating new secure keys...\n";
        include __DIR__ . '/generate_keys.php';
        break;
        
    case '2':
        echo "\n📊 Checking key status...\n";
        checkKeyStatus();
        break;
        
    case '3':
        echo "\n📋 Key information...\n";
        showKeyInfo();
        break;
        
    case '4':
        echo "\n📝 Adding keys.json to .gitignore...\n";
        addToGitignore();
        break;
        
    case '5':
        echo "Exiting...\n";
        exit(0);
        
    default:
        echo "Invalid choice. Please run the script again.\n";
        exit(1);
}

/**
 * Check the status of existing keys
 */
function checkKeyStatus() {
    $keysPath = __DIR__ . '/keys.json';
    
    if (!file_exists($keysPath)) {
        echo "❌ No keys file found. Run option 1 to generate keys.\n";
        return;
    }
    
    try {
        require_once __DIR__ . '/SecureKeyManager.php';
        SecureKeyManager::init();
        
        $keyInfo = SecureKeyManager::getKeyInfo();
        
        echo "✅ Keys file exists and is valid\n";
        echo "📅 Generated: " . $keyInfo['generated_at'] . "\n";
        echo "⏰ Expires: " . $keyInfo['expires_at'] . "\n";
        echo "📊 Total keys: " . $keyInfo['total_keys'] . "\n";
        echo "🔴 Expired: " . ($keyInfo['expired'] ? 'Yes' : 'No') . "\n";
        
        if ($keyInfo['expired']) {
            echo "\n⚠️  Keys have expired! Consider regenerating them.\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error reading keys: " . $e->getMessage() . "\n";
    }
}

/**
 * Show detailed key information
 */
function showKeyInfo() {
    $keysPath = __DIR__ . '/keys.json';
    
    if (!file_exists($keysPath)) {
        echo "❌ No keys file found. Run option 1 to generate keys.\n";
        return;
    }
    
    try {
        $keys = json_decode(file_get_contents($keysPath), true);
        
        echo "📋 Available keys:\n";
        foreach ($keys as $keyName => $keyValue) {
            if (in_array($keyName, ['generated_at', 'expires_at', 'version'])) {
                echo "   - $keyName: $keyValue\n";
            } else {
                $keyLength = strlen(base64_decode($keyValue)) * 8; // Convert to bits
                echo "   - $keyName: $keyLength-bit key (length: " . strlen($keyValue) . " chars)\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error reading key information: " . $e->getMessage() . "\n";
    }
}

/**
 * Add keys.json to .gitignore
 */
function addToGitignore() {
    $gitignorePath = dirname(__DIR__) . '/.gitignore';
    $keysEntry = 'config/keys.json';
    $backupEntry = 'config/keys_backup_*.json';
    
    // Create .gitignore if it doesn't exist
    if (!file_exists($gitignorePath)) {
        file_put_contents($gitignorePath, '');
        echo "✅ Created new .gitignore file\n";
    }
    
    $gitignoreContent = file_get_contents($gitignorePath);
    $modified = false;
    
    // Add keys.json entry
    if (strpos($gitignoreContent, $keysEntry) === false) {
        $gitignoreContent .= "\n# Security keys - DO NOT COMMIT\n";
        $gitignoreContent .= $keysEntry . "\n";
        $modified = true;
        echo "✅ Added keys.json to .gitignore\n";
    } else {
        echo "ℹ️  keys.json already in .gitignore\n";
    }
    
    // Add backup files entry
    if (strpos($gitignoreContent, $backupEntry) === false) {
        $gitignoreContent .= $backupEntry . "\n";
        $modified = true;
        echo "✅ Added key backup files to .gitignore\n";
    } else {
        echo "ℹ️  Key backup files already in .gitignore\n";
    }
    
    if ($modified) {
        file_put_contents($gitignorePath, $gitignoreContent);
        echo "📝 .gitignore updated successfully\n";
    }
}

echo "\n✅ Setup completed!\n";
?>
