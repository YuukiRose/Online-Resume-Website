<?php
/**
 * One-Time Recovery Tool
 * Recreates all encryption keys, .env files, and re-encrypts database data
 * Use this when encryption files are lost or corrupted
 */
/**
* require_once '../../config/admin_auth_check.php'; 
*/
require_once '../../config/SecureKeyManager.php';

echo "<h1>🚨 One-Time Recovery Tool</h1>";

// Handle recovery process
if (isset($_POST['execute_recovery'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🚨 Recovery Process in Progress...</h2>";
    
    try {
        $timestamp = date('Y-m-d_H-i-s');
        
        // Step 1: Backup existing files if they exist
        echo "<p>📁 Step 1: Backing up existing files...</p>";
        $backupDir = __DIR__ . '/../../secure/recovery_backup_' . $timestamp . '/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $existingFiles = [
            'K.env' => __DIR__ . '/../../secure/K.env',
            'D.env' => __DIR__ . '/../../secure/D.env',
            'keys.json' => __DIR__ . '/../../secure/keys.json',
            'keys_config.json' => __DIR__ . '/../../config/keys.json'
        ];
        
        $backedUpFiles = 0;
        foreach ($existingFiles as $name => $path) {
            if (file_exists($path)) {
                copy($path, $backupDir . $name);
                $backedUpFiles++;
                echo "<p>✅ Backed up: {$name}</p>";
            }
        }
        echo "<p>✅ Step 1 Complete: {$backedUpFiles} existing files backed up</p>";
        
        // Step 2: Generate new encryption keys
        echo "<p>🔐 Step 2: Generating new encryption keys...</p>";
        $keyManager = new SecureKeyManager();
        $newKeys = $keyManager->generateNewKeys();
        echo "<p>✅ New encryption keys generated</p>";
        echo "<ul>";
        echo "<li><strong>Encryption Key Hash:</strong> " . substr(hash('sha256', $newKeys['encryption_key']), 0, 16) . "...</li>";
        echo "<li><strong>JWT Secret Hash:</strong> " . substr(hash('sha256', $newKeys['jwt_secret']), 0, 16) . "...</li>";
        echo "<li><strong>Hash Salt Hash:</strong> " . substr(hash('sha256', $newKeys['hash_salt']), 0, 16) . "...</li>";
        echo "<li><strong>Generated Date:</strong> " . ($newKeys['generated_at'] ?? date('Y-m-d H:i:s')) . "</li>";
        echo "<li><strong>Expires Date:</strong> " . ($newKeys['expires_at'] ?? 'No expiry') . "</li>";
        echo "<li><strong>Version:</strong> " . ($newKeys['version'] ?? '2.0') . "</li>";
        echo "</ul>";
        
        // Step 3: Create all key files
        echo "<p>💾 Step 3: Creating key files in secure folder...</p>";
        
        // Create K.env file
        $kenvPath = __DIR__ . '/../../secure/K.env';
        file_put_contents($kenvPath, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Created: K.env</p>";
        
        // Create keys.json file (for compatibility)
        $keysJsonPath = __DIR__ . '/../../secure/keys.json';
        file_put_contents($keysJsonPath, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Created: keys.json</p>";
        
        // Create config keys.json (for backward compatibility)
        $configKeysPath = __DIR__ . '/../../config/keys.json';
        $configDir = dirname($configKeysPath);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        file_put_contents($configKeysPath, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Created: config/keys.json</p>";
        
        // Step 4: Generate database credentials and create .env file
        echo "<p>🗄️ Step 4: Generating database credentials and .env file...</p>";
        
        // Emergency hardcoded database connection (use only when normal DB connection fails)
        $dbHost = 'mysql-200-133.mysql.prositehosting.net';
        $dbName = 'DB_01_RWEBB_RES';
        $dbUser = 'Admin12';
        $dbPassword = 'WhiteRabbit324!';
        
        echo "<p>⚠️ Using emergency hardcoded database credentials</p>";
        echo "<p>📝 Emergency DB Host: {$dbHost}</p>";
        echo "<p>📝 Emergency DB Name: {$dbName}</p>";
        echo "<p>📝 Emergency DB User: {$dbUser}</p>";
        
        // Try to connect to the emergency database
        $emergencyPdo = null;
        try {
            $emergencyDsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $emergencyPdo = new PDO($emergencyDsn, $dbUser, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "<p>✅ Emergency database connection established</p>";
        } catch (PDOException $e) {
            echo "<p>❌ Emergency database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>⚠️ Continuing with .env file creation...</p>";
        }
        
        // Encrypt database credentials with new keys
        $encryptedDbUser = $keyManager->encryptData($dbUser, $newKeys['encryption_key']);
        $encryptedDbPassword = $keyManager->encryptData($dbPassword, $newKeys['encryption_key']);
        $encryptedAdminPassword = $keyManager->encryptData('admin123', $newKeys['encryption_key']); // Default admin password
        
        // Create D.env file content with emergency database settings
        $envContent = "# Emergency Database Configuration\n";
        $envContent .= "# Generated by Recovery Tool - Emergency Mode\n";
        $envContent .= "# D.env - Database Environment File with Encrypted Credentials\n";
        $envContent .= "DB_HOST={$dbHost}\n";
        $envContent .= "DB_NAME={$dbName}\n";
        $envContent .= "DB_USER_ENCRYPTED={$encryptedDbUser}\n";
        $envContent .= "DB_PASSWORD_ENCRYPTED={$encryptedDbPassword}\n";
        $envContent .= "\n# Admin Configuration\n";
        $envContent .= "ADMIN_PASSWORD_ENCRYPTED={$encryptedAdminPassword}\n";
        $envContent .= "\n# Application Settings\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "\n# Security Settings\n";
        $envContent .= "ENCRYPTION_ENABLED=true\n";
        $envContent .= "SESSION_SECURE=true\n";
        $envContent .= "\n# Emergency Recovery Settings\n";
        $envContent .= "RECOVERY_MODE=true\n";
        $envContent .= "RECOVERY_TIMESTAMP=" . date('Y-m-d H:i:s') . "\n";
        $envContent .= "\n# File generated by Emergency Recovery Tool on " . date('Y-m-d H:i:s') . "\n";
        $envContent .= "# D.env contains encrypted database credentials for secure storage\n";
        
        // Save D.env file
        $envPath = __DIR__ . '/../../secure/D.env';
        file_put_contents($envPath, $envContent);
        echo "<p>✅ Created: D.env file with emergency encrypted credentials</p>";
        echo "<p>📝 Emergency Database Host: {$dbHost}</p>";
        echo "<p>📝 Emergency Database Name: {$dbName}</p>";
        echo "<p>📝 Emergency Database User: {$dbUser} (encrypted)</p>";
        echo "<p>📝 Default Admin Password: admin123 (encrypted)</p>";
        
        // Step 5: Re-encrypt existing database passwords if any exist
        echo "<p>🔄 Step 5: Re-encrypting existing database passwords...</p>";
        
        $reencryptedUsers = 0;
        $reencryptedAdmins = 0;
        
        // Use the emergency database connection if available
        $dbConnection = $emergencyPdo; // Use emergency connection from Step 4
        
        if ($dbConnection) {
            // Check if users table exists and has encrypted passwords
            try {
                $stmt = $dbConnection->query("SHOW TABLES LIKE 'users'");
                if ($stmt->rowCount() > 0) {
                    // For recovery, we'll reset all user passwords to a default and encrypt them
                    $defaultPassword = 'TempPassword123!';
                    $encryptedDefaultPassword = 'ENC:' . $keyManager->encryptData($defaultPassword, $newKeys['encryption_key']);
                    
                    $stmt = $dbConnection->prepare("UPDATE users SET password = ?");
                    $result = $stmt->execute([$encryptedDefaultPassword]);
                    
                    if ($result) {
                        $affectedRows = $stmt->rowCount();
                        $reencryptedUsers = $affectedRows;
                        echo "<p>✅ Reset and encrypted {$reencryptedUsers} user passwords using emergency connection</p>";
                        echo "<p>📝 Default user password: {$defaultPassword}</p>";
                    }
                } else {
                    echo "<p>⚠️ Users table not found in emergency database</p>";
                }
            } catch (PDOException $e) {
                echo "<p>⚠️ Could not update users table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Check if admins table exists
            try {
                $stmt = $dbConnection->query("SHOW TABLES LIKE 'admins'");
                if ($stmt->rowCount() > 0) {
                    // Reset admin passwords
                    $defaultAdminPassword = 'AdminRecovery123!';
                    $encryptedAdminPassword = 'ENC:' . $keyManager->encryptData($defaultAdminPassword, $newKeys['encryption_key']);
                    
                    $stmt = $dbConnection->prepare("UPDATE admins SET password = ?");
                    $result = $stmt->execute([$encryptedAdminPassword]);
                    
                    if ($result) {
                        $affectedRows = $stmt->rowCount();
                        $reencryptedAdmins = $affectedRows;
                        echo "<p>✅ Reset and encrypted {$reencryptedAdmins} admin passwords using emergency connection</p>";
                        echo "<p>📝 Default admin password: {$defaultAdminPassword}</p>";
                    }
                } else {
                    echo "<p>⚠️ Admins table not found in emergency database</p>";
                }
            } catch (PDOException $e) {
                echo "<p>⚠️ Could not update admins table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>⚠️ No database connection available - passwords not updated</p>";
            echo "<p>📝 You will need to manually reset database passwords after recovery</p>";
        }
        
        // Step 6: Create recovery backup of new files
        echo "<p>💾 Step 6: Creating recovery backup...</p>";
        $recoveryBackupFile = __DIR__ . '/../../secure/recovery_keys_backup_' . $timestamp . '.json';
        file_put_contents($recoveryBackupFile, json_encode($newKeys, JSON_PRETTY_PRINT));
        echo "<p>✅ Recovery backup created: " . basename($recoveryBackupFile) . "</p>";
        
        // Step 7: Test encryption functionality
        echo "<p>🧪 Step 7: Testing encryption functionality...</p>";
        $testData = "recovery_test_" . time();
        $encrypted = $keyManager->encryptData($testData, $newKeys['encryption_key']);
        $decrypted = $keyManager->decryptData($encrypted, $newKeys['encryption_key']);
        
        if ($testData === $decrypted) {
            echo "<p>✅ Encryption/Decryption test passed</p>";
        } else {
            throw new Exception("Encryption test failed - recovery may be incomplete");
        }
        
        // Success summary
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>🎉 Recovery Completed Successfully!</h3>";
        echo "<p><strong>Files Created:</strong></p>";
        echo "<ul>";
        echo "<li>✅ K.env - Primary encryption keys</li>";
        echo "<li>✅ keys.json - Compatible encryption keys</li>";
        echo "<li>✅ config/keys.json - Backward compatible keys</li>";
        echo "<li>✅ D.env - Database configuration with encrypted credentials</li>";
        echo "</ul>";
        echo "<p><strong>Database Recovery:</strong></p>";
        echo "<ul>";
        echo "<li>🔄 User passwords reset: {$reencryptedUsers}</li>";
        echo "<li>🔄 Admin passwords reset: {$reencryptedAdmins}</li>";
        echo "</ul>";
        echo "<p><strong>Backup Information:</strong></p>";
        echo "<ul>";
        echo "<li>📁 Old files backed up to: recovery_backup_{$timestamp}/</li>";
        echo "<li>💾 New keys backed up to: " . basename($recoveryBackupFile) . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>🔑 Important Recovery Information:</h4>";
        echo "<ul>";
        echo "<li><strong>Emergency Database Host:</strong> mysql-200-133.mysql.prositehosting.net</li>";
        echo "<li><strong>Emergency Database Name:</strong> DB_01_RWEBB_RES</li>";
        echo "<li><strong>Emergency Database User:</strong> Admin12 (encrypted in D.env)</li>";
        echo "<li><strong>Emergency Database Password:</strong> WhiteRabbit324! (encrypted in D.env)</li>";
        echo "<li><strong>Default User Password:</strong> TempPassword123!</li>";
        echo "<li><strong>Default Admin Password:</strong> AdminRecovery123!</li>";
        echo "<li><strong>All passwords are now encrypted with the new keys</strong></li>";
        echo "</ul>";
        echo "<p><strong>⚠️ Remember to change these default passwords after recovery!</strong></p>";
        echo "<p><strong>🚨 This tool uses hardcoded emergency database credentials for disaster recovery only!</strong></p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Recovery Failed</h3>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Check the backup files in recovery_backup_{$timestamp}/ if they were created.</strong></p>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Display current status
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📊 Current File Status</h2>";

$secureDir = __DIR__ . '/../../secure/';
$configDir = __DIR__ . '/../../config/';

$criticalFiles = [
    'K.env' => $secureDir . 'K.env',
    'D.env' => $secureDir . 'D.env',
    'keys.json (secure)' => $secureDir . 'keys.json',
    'keys.json (config)' => $configDir . 'keys.json'
];

echo "<h4>🔍 Critical Files Check:</h4>";
$missingFiles = 0;
foreach ($criticalFiles as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        $date = filemtime($path);
        echo "<p>✅ <strong>{$name}:</strong> Present (" . number_format($size) . " bytes, " . date('Y-m-d H:i:s', $date) . ")</p>";
    } else {
        echo "<p>❌ <strong>{$name}:</strong> Missing</p>";
        $missingFiles++;
    }
}

if ($missingFiles > 0) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<p><strong>⚠️ {$missingFiles} critical files are missing!</strong></p>";
    echo "<p>This recovery tool can recreate all missing encryption files and reset database passwords.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<p><strong>✅ All critical files are present.</strong></p>";
    echo "<p>Recovery is not needed unless files are corrupted or you've lost access.</p>";
    echo "</div>";
}

echo "</div>";

// Recovery form
echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🚨 Execute Recovery Process</h2>";
echo "<p><strong>⚠️ WARNING: This will overwrite existing encryption files and reset all passwords!</strong></p>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h4>🔄 What this recovery process will do:</h4>";
echo "<ul>";
echo "<li>Backup any existing encryption files with timestamp</li>";
echo "<li>Generate completely new encryption keys</li>";
echo "<li>Create new K.env file with fresh keys</li>";
echo "<li>Create new keys.json files for compatibility</li>";
echo "<li>Generate new D.env file with encrypted database credentials</li>";
echo "<li>Reset all user passwords to default: <strong>TempPassword123!</strong></li>";
echo "<li>Reset all admin passwords to default: <strong>AdminRecovery123!</strong></li>";
echo "<li>Create recovery backup of all new files</li>";
echo "<li>Test encryption functionality</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #dc3545; color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<h4>🚨 CRITICAL WARNINGS:</h4>";
echo "<ul>";
echo "<li><strong>ALL EXISTING ENCRYPTED DATA WILL BE LOST</strong> if you don't have backups</li>";
echo "<li><strong>ALL USER/ADMIN PASSWORDS WILL BE RESET</strong> to default values</li>";
echo "<li><strong>EXISTING ENCRYPTION KEYS WILL BE REPLACED</strong> - old encrypted data will be unrecoverable</li>";
echo "<li><strong>THIS PROCESS IS IRREVERSIBLE</strong> - use only for disaster recovery</li>";
echo "<li><strong>ONLY USE IF</strong> encryption files are lost, corrupted, or you've lost access</li>";
echo "</ul>";
echo "</div>";

echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<form method='post' onsubmit='return confirm(\"🚨 FINAL WARNING 🚨\\n\\nThis will DESTROY all existing encryption keys and RESET all passwords!\\n\\nThis action is IRREVERSIBLE!\\n\\nOnly proceed if this is a disaster recovery situation.\\n\\nType YES in the confirmation to continue.\") && prompt(\"Type YES to confirm you understand this will destroy existing encrypted data:\") === \"YES\"'>";
echo "<button type='submit' name='execute_recovery' style='background: #dc3545; color: white; border: none; padding: 20px 40px; border-radius: 5px; cursor: pointer; font-size: 18px; font-weight: bold;'>";
echo "🚨 EXECUTE EMERGENCY RECOVERY";
echo "</button>";
echo "</form>";
echo "</div>";

echo "</div>";

// Backup file management
echo "<div style='background: #e9ecef; color: #495057; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 Recovery Backup Files</h2>";

$backupFiles = glob($secureDir . 'recovery_*');
$keyBackups = glob($secureDir . 'recovery_keys_backup_*.json');
$folderBackups = glob($secureDir . 'recovery_backup_*', GLOB_ONLYDIR);

if (!empty($keyBackups)) {
    echo "<h4>🔑 Recovery Key Backups:</h4>";
    echo "<ul>";
    foreach ($keyBackups as $backup) {
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

if (!empty($folderBackups)) {
    echo "<h4>📁 Recovery File Backups:</h4>";
    echo "<ul>";
    foreach ($folderBackups as $backup) {
        $foldername = basename($backup);
        $date = filemtime($backup);
        $fileCount = count(glob($backup . '/*'));
        echo "<li>";
        echo "<strong>{$foldername}/</strong> ";
        echo "<small>(" . date('Y-m-d H:i:s', $date) . ", {$fileCount} files)</small>";
        echo "</li>";
    }
    echo "</ul>";
}

if (empty($backupFiles)) {
    echo "<p><em>No recovery backups found yet.</em></p>";
}

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>← Back to Debug Dashboard</a>";
echo "<a href='onetime_key_generator.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>🔐 Key Generator</a>";
echo "<a href='key_rotation.php' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>🔄 Key Rotation</a>";
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
    max-width: 900px;
    margin: 0 auto;
}
</style>
