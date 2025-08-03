<?php
/**
 * Emergency Key Recovery Tool
 * Recover data using backed up encryption keys in case of issues
 */

require_once '../../config/admin_auth_check.php';
require_once '../../config/SecureKeyManager.php';

echo "<h1>🚨 Emergency Key Recovery</h1>";

// Handle recovery process
if (isset($_POST['recover_with_backup']) && isset($_POST['backup_file'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🔄 Emergency Recovery in Progress...</h2>";
    
    try {
        $backupFile = $_POST['backup_file'];
        $backupPath = __DIR__ . '/../../secure/' . basename($backupFile); // Sanitize filename
        
        if (!file_exists($backupPath)) {
            throw new Exception("Backup file not found: " . basename($backupFile));
        }
        
        // Load backup keys
        $backupKeys = json_decode(file_get_contents($backupPath), true);
        if (!$backupKeys) {
            throw new Exception("Invalid backup file format");
        }
        
        echo "<p>✅ Step 1: Backup keys loaded from " . basename($backupFile) . "</p>";
        
        // Test if backup keys can decrypt current data
        $envFile = __DIR__ . '/../../secure/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            $envData = [];
            
            foreach (explode("\n", $envContent) as $line) {
                if (strpos($line, '=') !== false && !empty(trim($line))) {
                    list($key, $value) = explode('=', $line, 2);
                    $envData[trim($key)] = trim($value);
                }
            }
            
            // Test decryption with backup key
            $keyManager = new SecureKeyManager();
            $testField = 'DB_PASSWORD_ENCRYPTED';
            
            if (isset($envData[$testField])) {
                $decrypted = $keyManager->decryptData($envData[$testField], $backupKeys['encryption_key']);
                if ($decrypted !== false) {
                    echo "<p>✅ Step 2: Backup keys can successfully decrypt current data</p>";
                    
                    // Restore the backup keys as current keys
                    $keyManager->saveKeys($backupKeys);
                    echo "<p>✅ Step 3: Backup keys restored as current keys</p>";
                    
                    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                    echo "<h3>🎉 Recovery Completed Successfully!</h3>";
                    echo "<p>The backup keys have been restored and your encrypted data should now be accessible.</p>";
                    echo "</div>";
                } else {
                    echo "<p>❌ Step 2: Backup keys cannot decrypt current data</p>";
                    echo "<p>This backup may be from before the current encryption or there may be data corruption.</p>";
                }
            } else {
                echo "<p>⚠️ No encrypted data found to test with</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Error During Recovery</h3>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display available backups
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 Available Key Backups</h2>";

$backupDir = __DIR__ . '/../../secure/';
$backupFiles = glob($backupDir . 'keys_backup_*.json');

if (!empty($backupFiles)) {
    echo "<form method='post'>";
    echo "<table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Select</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Backup File</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Date Created</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Key Hash</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Version</th>";
    echo "</tr>";
    
    foreach ($backupFiles as $backup) {
        $filename = basename($backup);
        $date = filemtime($backup);
        
        // Try to read backup info
        $backupData = json_decode(file_get_contents($backup), true);
        $keyHash = $backupData ? substr(hash('sha256', $backupData['encryption_key']), 0, 16) . '...' : 'Unknown';
        $version = $backupData['version'] ?? 'Unknown';
        
        echo "<tr>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        echo "<input type='radio' name='backup_file' value='{$filename}' id='{$filename}'>";
        echo "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
        echo "<label for='{$filename}'>{$filename}</label>";
        echo "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . date('Y-m-d H:i:s', $date) . "</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd; font-family: monospace;'>{$keyHash}</td>";
        echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$version}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>⚠️ CAUTION</h4>";
    echo "<p><strong>Emergency recovery should only be used when:</strong></p>";
    echo "<ul>";
    echo "<li>Current encryption keys are corrupted or lost</li>";
    echo "<li>Key rotation failed and you need to restore a working state</li>";
    echo "<li>You need to access data encrypted with a previous key</li>";
    echo "</ul>";
    echo "<p><strong>This will replace your current encryption keys!</strong></p>";
    echo "</div>";
    
    echo "<button type='submit' name='recover_with_backup' style='background: #dc3545; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;' onclick='return confirm(\"Are you sure you want to restore from this backup? This will replace your current encryption keys.\")'>";
    echo "🚨 Restore Selected Backup";
    echo "</button>";
    echo "</form>";
    
} else {
    echo "<p>No key backups found.</p>";
    echo "<p>Key backups are automatically created when you use the Key Rotation tool.</p>";
}

echo "</div>";

// Current key status
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🔑 Current Key Status</h2>";

try {
    $keyManager = new SecureKeyManager();
    $currentKeys = $keyManager->getEncryptionKeys();
    
    if ($currentKeys) {
        echo "<p>✅ Current encryption keys are present</p>";
        echo "<p><strong>Key Hash:</strong> " . substr(hash('sha256', $currentKeys['encryption_key']), 0, 16) . "...</p>";
        echo "<p><strong>Generated:</strong> " . ($currentKeys['generated_at'] ?? 'Unknown') . "</p>";
        echo "<p><strong>Version:</strong> " . ($currentKeys['version'] ?? '1.0') . "</p>";
        
        // Test current key
        $testData = "test_encryption_data";
        $encrypted = $keyManager->encryptData($testData, $currentKeys['encryption_key']);
        if ($encrypted !== false) {
            $decrypted = $keyManager->decryptData($encrypted, $currentKeys['encryption_key']);
            if ($decrypted === $testData) {
                echo "<p style='color: #28a745;'>✅ Current keys are working properly</p>";
            } else {
                echo "<p style='color: #dc3545;'>❌ Current keys may be corrupted (decryption failed)</p>";
            }
        } else {
            echo "<p style='color: #dc3545;'>❌ Current keys may be corrupted (encryption failed)</p>";
        }
    } else {
        echo "<p style='color: #dc3545;'>❌ No current encryption keys found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: #dc3545;'>❌ Error checking current keys: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Recovery tips
echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>💡 Recovery Tips</h2>";
echo "<ol>";
echo "<li><strong>Always test key rotation in a development environment first</strong></li>";
echo "<li><strong>Keep multiple backup copies</strong> of your encryption keys in secure locations</li>";
echo "<li><strong>Document your key rotation schedule</strong> and backup procedures</li>";
echo "<li><strong>Test recovery procedures regularly</strong> to ensure they work when needed</li>";
echo "<li><strong>Consider automated key backup</strong> to external secure storage</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='key_rotation.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>🔄 Key Rotation</a>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>← Back to Dashboard</a>";
echo "</div>";
?>

<style>
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h1, h2, h3, h4 { color: #2c3e50; }
table { font-size: 14px; }
button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
ul, ol { line-height: 1.6; }
</style>
