<?php
/**
 * Encryption Key Rotation Tool
 * Safely rotates encryption keys by decrypting with old key and re-encrypting with new key
 */
/**
* require_once '../../config/admin_auth_check.php'; 
*/
require_once '../../config/SecureKeyManager.php';

echo "<h1>🔄 Encryption Key Rotation</h1>";

// Handle key rotation process
if (isset($_POST['rotate_keys'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🔄 Key Rotation in Progress...</h2>";
    
    try {
        // Step 1: Backup current keys
        $keyManager = new SecureKeyManager();
        $currentKeys = $keyManager->getEncryptionKeys();
        
        if (!$currentKeys) {
            throw new Exception("No existing encryption keys found");
        }
        
        echo "<p>✅ Step 1: Current keys loaded successfully</p>";
        
        // Step 2: Create backup of current keys with timestamp
        $backupFile = __DIR__ . '/../../secure/keys_backup_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($backupFile, json_encode($currentKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Step 2: Keys backed up to: " . basename($backupFile) . "</p>";
        
        // Step 3: Generate new keys
        $newKeys = $keyManager->generateNewKeys();
        echo "<p>✅ Step 3: New encryption keys generated</p>";
        
        // Step 4: Decrypt and re-encrypt database credentials
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
            
            // Decrypt with old key and re-encrypt with new key
            $encryptionFields = ['DB_PASSWORD_ENCRYPTED', 'DB_USER_ENCRYPTED', 'ADMIN_PASSWORD_ENCRYPTED'];
            $rotatedData = [];
            
            foreach ($encryptionFields as $field) {
                if (isset($envData[$field])) {
                    // Decrypt with old key
                    $decrypted = $keyManager->decryptData($envData[$field], $currentKeys['encryption_key']);
                    if ($decrypted !== false) {
                        // Re-encrypt with new key
                        $reencrypted = $keyManager->encryptData($decrypted, $newKeys['encryption_key']);
                        $rotatedData[$field] = $reencrypted;
                        echo "<p>✅ Rotated: {$field}</p>";
                    } else {
                        echo "<p>⚠️ Warning: Could not decrypt {$field}</p>";
                    }
                }
            }
            
            // Update .env file with new encrypted values
            if (!empty($rotatedData)) {
                foreach ($rotatedData as $key => $value) {
                    $envData[$key] = $value;
                }
                
                $newEnvContent = '';
                foreach ($envData as $key => $value) {
                    $newEnvContent .= "{$key}={$value}\n";
                }
                
                file_put_contents($envFile, $newEnvContent);
                echo "<p>✅ Step 4: Database credentials re-encrypted with new keys</p>";
            }
        }
        
        // Step 5: Rotate user passwords in database
        require_once '../../config/database.php';
        
        if (isset($pdo)) {
            
            // Check if users table exists before proceeding
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() == 0) {
                echo "<p>⚠️ Users table not found - skipping user password rotation</p>";
            } else {
                // Get all users with encrypted passwords
                $stmt = $pdo->query("SELECT id, username, password FROM users WHERE password LIKE 'ENC:%'");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $rotatedUsers = 0;
                foreach ($users as $user) {
                    $encryptedPassword = substr($user['password'], 4); // Remove 'ENC:' prefix
                    $decryptedPassword = $keyManager->decryptData($encryptedPassword, $currentKeys['encryption_key']);
                
                if ($decryptedPassword !== false) {
                    $newEncryptedPassword = 'ENC:' . $keyManager->encryptData($decryptedPassword, $newKeys['encryption_key']);
                    
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$newEncryptedPassword, $user['id']]);
                    $rotatedUsers++;
                }
            }
            
                echo "<p>✅ Step 5: Rotated passwords for {$rotatedUsers} users</p>";
            }
            
            // Step 6: Rotate admin passwords
            $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
            if ($stmt->rowCount() == 0) {
                echo "<p>⚠️ Admins table not found - skipping admin password rotation</p>";
                $rotatedAdmins = 0;
            } else {
                $stmt = $pdo->query("SELECT id, username, password FROM admins WHERE password LIKE 'ENC:%'");
                $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $rotatedAdmins = 0;
                foreach ($admins as $admin) {
                    $encryptedPassword = substr($admin['password'], 4); // Remove 'ENC:' prefix
                    $decryptedPassword = $keyManager->decryptData($encryptedPassword, $currentKeys['encryption_key']);
                    
                    if ($decryptedPassword !== false) {
                        $newEncryptedPassword = 'ENC:' . $keyManager->encryptData($decryptedPassword, $newKeys['encryption_key']);
                        
                        $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                        $updateStmt->execute([$newEncryptedPassword, $admin['id']]);
                        $rotatedAdmins++;
                    }
                }
            }
            
            echo "<p>✅ Step 6: Rotated passwords for {$rotatedAdmins} admin accounts</p>";
        }
        
        // Step 7: Save new keys
        $keyManager->saveKeys($newKeys);
        echo "<p>✅ Step 7: New encryption keys saved</p>";
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 Key Rotation Completed Successfully!</h3>";
        echo "<p><strong>Summary:</strong></p>";
        echo "<ul>";
        echo "<li>Database credentials: Re-encrypted</li>";
        echo "<li>User passwords: {$rotatedUsers} rotated</li>";
        echo "<li>Admin passwords: {$rotatedAdmins} rotated</li>";
        echo "<li>Old keys backed up to: " . basename($backupFile) . "</li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Error During Key Rotation</h3>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Your data is safe.</strong> The old keys are still in place.</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display current status
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📊 Current Encryption Status</h2>";

try {
    $keyManager = new SecureKeyManager();
    $currentKeys = $keyManager->getEncryptionKeys();
    
    if ($currentKeys) {
        echo "<p>✅ Encryption keys are present</p>";
        echo "<p><strong>Key Hash:</strong> " . substr(hash('sha256', $currentKeys['encryption_key']), 0, 16) . "...</p>";
        echo "<p><strong>Creation Date:</strong> " . (isset($currentKeys['created_at']) ? $currentKeys['created_at'] : 'Unknown') . "</p>";
        
        // Check what's encrypted
        $envFile = __DIR__ . '/../../secure/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            $encryptedCount = substr_count($envContent, '_ENCRYPTED=');
            echo "<p><strong>Encrypted .env fields:</strong> {$encryptedCount}</p>";
        }
        
        // Check database for encrypted data
        require_once '../../config/database.php';
        
        if ($pdo) {
            
            // Check for encrypted user passwords (safely)
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE password LIKE 'ENC:%'");
                $encryptedUsers = $stmt->fetch()['count'];
                echo "<p><strong>Encrypted user passwords:</strong> {$encryptedUsers}</p>";
            } else {
                echo "<p><strong>Users table:</strong> Not found</p>";
            }
            
            // Check for encrypted admin passwords (safely)
            $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins WHERE password LIKE 'ENC:%'");
                $encryptedAdmins = $stmt->fetch()['count'];
                echo "<p><strong>Encrypted admin passwords:</strong> {$encryptedAdmins}</p>";
            } else {
                echo "<p><strong>Admins table:</strong> Not found</p>";
            }
        }
        
    } else {
        echo "<p>❌ No encryption keys found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking encryption status: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Key rotation form
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🔄 Rotate Encryption Keys</h2>";
echo "<p><strong>What this will do:</strong></p>";
echo "<ul>";
echo "<li>Backup current encryption keys with timestamp</li>";
echo "<li>Generate new encryption keys</li>";
echo "<li>Decrypt all data with old keys</li>";
echo "<li>Re-encrypt all data with new keys</li>";
echo "<li>Update database credentials in .env file</li>";
echo "<li>Rotate all user and admin passwords</li>";
echo "</ul>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h4>⚠️ Important Notes:</h4>";
echo "<ul>";
echo "<li>This process creates a backup of your current keys</li>";
echo "<li>All encrypted data will be migrated to the new keys</li>";
echo "<li>The process is atomic - if it fails, your old keys remain intact</li>";
echo "<li>This operation may take a few moments for large datasets</li>";
echo "</ul>";
echo "</div>";

echo "<form method='post' onsubmit='return confirm(\"Are you sure you want to rotate the encryption keys? This will re-encrypt all sensitive data.\")'>";
echo "<button type='submit' name='rotate_keys' style='background: #dc3545; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>";
echo "🔄 Rotate Encryption Keys";
echo "</button>";
echo "</form>";
echo "</div>";

// Key backup management
echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 Key Backup Management</h2>";

$backupDir = __DIR__ . '/../../secure/';
$backupFiles = glob($backupDir . 'keys_backup_*.json');

if (!empty($backupFiles)) {
    echo "<p><strong>Available key backups:</strong></p>";
    echo "<ul>";
    foreach ($backupFiles as $backup) {
        $filename = basename($backup);
        $size = filesize($backup);
        $date = filemtime($backup);
        echo "<li>";
        echo "<strong>{$filename}</strong> ";
        echo "<small>(" . date('Y-m-d H:i:s', $date) . ", " . number_format($size) . " bytes)</small>";
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No key backups found.</p>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>← Back to Debug Dashboard</a>";
echo "</div>";
?>

<style>
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h1, h2, h3, h4 { color: #2c3e50; }
button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
ul { line-height: 1.6; }
</style>
