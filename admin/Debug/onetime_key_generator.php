<?php
/**
 * One-Time Encryption Key Generator
 * Generates initial encryption keys for database encryption
 * Use this only once when setting up encryption for the first time
 */
/**
* require_once '../../config/admin_auth_check.php'; 
*/
require_once '../../config/SecureKeyManager.php';

echo "<h1>🔐 One-Time Encryption Key Generator</h1>";

// Handle key generation process
if (isset($_POST['generate_keys'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🔐 Generating Initial Encryption Keys...</h2>";
    
    try {
        // Step 1: Check if keys already exist
        $keyManager = new SecureKeyManager();
        $existingKeys = $keyManager->getEncryptionKeys();
        
        if ($existingKeys) {
            throw new Exception("Encryption keys already exist! Use the key rotation tool instead.");
        }
        
        echo "<p>✅ Step 1: Confirmed no existing keys found</p>";
        
        // Step 2: Generate new encryption keys
        $newKeys = $keyManager->generateNewKeys();
        echo "<p>✅ Step 2: New encryption keys generated</p>";
        
        // Step 3: Display key information (for verification)
        echo "<p>✅ Step 3: Key verification</p>";
        echo "<ul>";
        echo "<li><strong>Encryption Key Hash:</strong> " . substr(hash('sha256', $newKeys['encryption_key']), 0, 16) . "...</li>";
        echo "<li><strong>Verification Hash:</strong> " . substr(hash('sha256', $newKeys['verification_hash']), 0, 16) . "...</li>";
        echo "<li><strong>Creation Date:</strong> " . $newKeys['created_at'] . "</li>";
        echo "</ul>";
        
        // Step 4: Create initial backup
        $backupFile = __DIR__ . '/../../secure/K_env_backup_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($backupFile, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Step 4: Initial backup created: " . basename($backupFile) . "</p>";
        
        // Step 5: Save the keys as K.env
        $kenvPath = __DIR__ . '/../../secure/K.env';
        $secureDir = dirname($kenvPath);
        if (!is_dir($secureDir)) {
            mkdir($secureDir, 0755, true);
        }
        file_put_contents($kenvPath, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Step 5: Encryption keys saved to K.env</p>";
        
        // Step 6: Generate sample encrypted data to verify functionality
        $testData = "test_encryption_" . time();
        $encrypted = $keyManager->encryptData($testData, $newKeys['encryption_key']);
        $decrypted = $keyManager->decryptData($encrypted, $newKeys['encryption_key']);
        
        if ($testData === $decrypted) {
            echo "<p>✅ Step 6: Encryption/Decryption test passed</p>";
        } else {
            throw new Exception("Encryption test failed - keys may be corrupted");
        }
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 Initial Encryption Keys Generated Successfully!</h3>";
        echo "<p><strong>Summary:</strong></p>";
        echo "<ul>";
        echo "<li>Encryption keys generated and saved to K.env</li>";
        echo "<li>Initial backup created: " . basename($backupFile) . "</li>";
        echo "<li>Encryption functionality verified</li>";
        echo "<li>Ready for database encryption setup</li>";
        echo "</ul>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ul>";
        echo "<li>Use the database encryption tool to encrypt sensitive data</li>";
        echo "<li>Store the backup file in a secure location</li>";
        echo "<li>Configure your .env file for encrypted database credentials</li>";
        echo "<li>The keys are now available in the K.env file</li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Error During Key Generation</h3>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display current status
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📊 Current Encryption Key Status</h2>";

try {
    $keyManager = new SecureKeyManager();
    $currentKeys = $keyManager->getEncryptionKeys();
    
    if ($currentKeys) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<p>✅ <strong>Encryption keys are already present!</strong></p>";
        echo "<p><strong>Key Hash:</strong> " . substr(hash('sha256', $currentKeys['encryption_key']), 0, 16) . "...</p>";
        echo "<p><strong>Creation Date:</strong> " . (isset($currentKeys['created_at']) ? $currentKeys['created_at'] : 'Unknown') . "</p>";
        echo "<p><em>If you need to change these keys, use the Key Rotation tool instead.</em></p>";
        echo "</div>";
        
        // Check what's already encrypted
        $envFile = __DIR__ . '/../../secure/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            $encryptedCount = substr_count($envContent, '_ENCRYPTED=');
            echo "<p><strong>Encrypted .env fields:</strong> {$encryptedCount}</p>";
        }
        
        // Check database for encrypted data
        require_once '../../config/database.php';
        if (isset($pdo)) {
            // Check for encrypted user passwords (safely)
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE password LIKE 'ENC:%'");
                $encryptedUsers = $stmt->fetch()['count'];
                echo "<p><strong>Encrypted user passwords:</strong> {$encryptedUsers}</p>";
            }
            
            // Check for encrypted admin passwords (safely)
            $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins WHERE password LIKE 'ENC:%'");
                $encryptedAdmins = $stmt->fetch()['count'];
                echo "<p><strong>Encrypted admin passwords:</strong> {$encryptedAdmins}</p>";
            }
        }
        
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px;'>";
        echo "<p>⚠️ <strong>No encryption keys found</strong></p>";
        echo "<p>This appears to be a fresh installation. Generate initial keys below.</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<p>❌ Error checking encryption status: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";

// Key generation form (only show if no keys exist)
try {
    $keyManager = new SecureKeyManager();
    $currentKeys = $keyManager->getEncryptionKeys();
    
    if (!$currentKeys) {
        echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>🔐 Generate Initial Encryption Keys</h2>";
        echo "<p><strong>What this will do:</strong></p>";
        echo "<ul>";
        echo "<li>Generate cryptographically secure encryption keys</li>";
        echo "<li>Create a verification hash for integrity checking</li>";
        echo "<li>Save keys to a secure location</li>";
        echo "<li>Create an initial backup with timestamp</li>";
        echo "<li>Test encryption/decryption functionality</li>";
        echo "</ul>";

        echo "<div style='background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>ℹ️ Key Generation Details:</h4>";
        echo "<ul>";
        echo "<li><strong>Encryption Algorithm:</strong> AES-256-GCM</li>";
        echo "<li><strong>Key Length:</strong> 256 bits (32 bytes)</li>";
        echo "<li><strong>Random Source:</strong> Cryptographically secure random bytes</li>";
        echo "<li><strong>Backup Format:</strong> JSON with timestamp</li>";
        echo "</ul>";
        echo "</div>";

        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>⚠️ Important Security Notes:</h4>";
        echo "<ul>";
        echo "<li><strong>One-Time Use:</strong> This should only be run once per installation</li>";
        echo "<li><strong>Backup Storage:</strong> Store the backup file in a secure, offline location</li>";
        echo "<li><strong>Key Security:</strong> Never share or transmit these keys over insecure channels</li>";
        echo "<li><strong>Recovery:</strong> Without these keys, encrypted data cannot be recovered</li>";
        echo "</ul>";
        echo "</div>";

        echo "<form method='post' onsubmit='return confirm(\"Are you sure you want to generate initial encryption keys? This should only be done once.\")'>";
        echo "<button type='submit' name='generate_keys' style='background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>";
        echo "🔐 Generate Initial Encryption Keys";
        echo "</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>🔄 Alternative Actions</h2>";
        echo "<p>Since encryption keys already exist, you might want to:</p>";
        echo "<ul>";
        echo "<li><a href='key_rotation.php' style='color: #004085; font-weight: bold;'>🔄 Rotate existing keys</a> - Change to new keys while preserving encrypted data</li>";
        echo "<li><a href='../encrypt_database.php' style='color: #004085; font-weight: bold;'>🔐 Encrypt database data</a> - Encrypt sensitive database fields</li>";
        echo "<li><a href='../debug_dashboard.php' style='color: #004085; font-weight: bold;'>📊 Debug dashboard</a> - View system status and tools</li>";
        echo "</ul>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<p>❌ Error determining key status: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Key backup management
echo "<div style='background: #e9ecef; color: #495057; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 Key Backup Information</h2>";

$backupDir = __DIR__ . '/../../secure/';
$allBackupFiles = glob($backupDir . '{keys_*_backup_*.json,K_env_backup_*.json}', GLOB_BRACE);
$initialBackups = glob($backupDir . '{keys_initial_backup_*.json,K_env_backup_*.json}', GLOB_BRACE);
$rotationBackups = glob($backupDir . 'keys_backup_*.json');

echo "<p><strong>Backup file locations:</strong> {$backupDir}</p>";

if (!empty($initialBackups)) {
    echo "<h4>📋 Initial Key Backups:</h4>";
    echo "<ul>";
    foreach ($initialBackups as $backup) {
        $filename = basename($backup);
        $size = filesize($backup);
        $date = filemtime($backup);
        echo "<li>";
        echo "<strong>{$filename}</strong> ";
        echo "<small>(" . date('Y-m-d H:i:s', $date) . ", " . number_format($size) . " bytes)</small>";
        echo "</li>";
    }
    echo "</ul>";
}

if (!empty($rotationBackups)) {
    echo "<h4>🔄 Key Rotation Backups:</h4>";
    echo "<ul>";
    foreach ($rotationBackups as $backup) {
        $filename = basename($backup);
        $size = filesize($backup);
        $date = filemtime($backup);
        echo "<li>";
        echo "<strong>{$filename}</strong> ";
        echo "<small>(" . date('Y-m-d H:i:s', $date) . ", " . number_format($size) . " bytes)</small>";
        echo "</li>";
    }
    echo "</ul>";
}

if (empty($allBackupFiles)) {
    echo "<p><em>No key backups found yet.</em></p>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>← Back to Debug Dashboard</a>";
echo "<a href='key_rotation.php' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>🔄 Key Rotation Tool</a>";
echo "</div>";
?>

<style>
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h1, h2, h3, h4 { 
    color: #2c3e50; 
    margin-top: 0;
}
button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
a:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
ul { 
    line-height: 1.6; 
    margin-bottom: 0;
}
li {
    margin-bottom: 5px;
}
.container {
    max-width: 800px;
    margin: 0 auto;
}
</style>
