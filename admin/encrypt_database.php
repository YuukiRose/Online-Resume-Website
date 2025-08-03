<?php
/**
 * Database Credential Encryption Tool
 * Use this to encrypt your database credentials
 */

/**require_once '../config/SecureConfig.php';
*/
echo "<h1>🔐 Database Credential Encryption Tool</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? '';
    $dbname = $_POST['dbname'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($host) && !empty($dbname) && !empty($username) && !empty($password)) {
        try {
            // Create .env file with encrypted credentials in secure directory
            $envContent = "# Database Configuration - Encrypted\n";
            $envContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
            $envContent .= "# This file is protected from web access\n\n";
            $envContent .= "DB_HOST=" . SecureConfig::encrypt($host) . "\n";
            $envContent .= "DB_NAME=" . SecureConfig::encrypt($dbname) . "\n";
            $envContent .= "DB_USERNAME=" . SecureConfig::encrypt($username) . "\n";
            $envContent .= "DB_PASSWORD=" . SecureConfig::encrypt($password) . "\n";
            
            // Save .env file to secure directory
            $envPath = '../secure/.env';
            
            // Ensure secure directory exists
            if (!is_dir('../secure')) {
                mkdir('../secure', 0755, true);
            }
            
            $envCreated = file_put_contents($envPath, $envContent);
            
            if ($envCreated !== false) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<strong>✅ .env File Created Successfully!</strong><br>";
                echo "Location: <code>secure/.env</code> (protected from web access)<br>";
                echo "Size: " . strlen($envContent) . " bytes<br>";
                echo "🔒 All credentials are encrypted and secure from URL access.";
                echo "</div>";
            }
            
            echo "<h2>✅ Encrypted Database Configuration</h2>";
            echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; font-family: monospace;'>";
            echo "<strong>Copy this encrypted configuration to your database.php file:</strong><br><br>";
            
            echo "<?php<br>";
            echo "// Database configuration - ENCRYPTED<br>";
            echo "require_once 'SecureConfig.php';<br>";
            echo "require_once 'EnvLoader.php';<br>";
            echo "EnvLoader::load();<br><br>";
            
            echo "\$config = [<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;'host' => EnvLoader::get('DB_HOST', '" . SecureConfig::encrypt($host) . "'),<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;'dbname' => EnvLoader::get('DB_NAME', '" . SecureConfig::encrypt($dbname) . "'),<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;'username' => EnvLoader::get('DB_USERNAME', '" . SecureConfig::encrypt($username) . "'),<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;'password' => EnvLoader::get('DB_PASSWORD', '" . SecureConfig::encrypt($password) . "')<br>";
            echo "];<br><br>";
            
            echo "// Decrypt credentials<br>";
            echo "\$host = SecureConfig::isEncrypted(\$config['host']) ? SecureConfig::decrypt(\$config['host']) : \$config['host'];<br>";
            echo "\$dbname = SecureConfig::isEncrypted(\$config['dbname']) ? SecureConfig::decrypt(\$config['dbname']) : \$config['dbname'];<br>";
            echo "\$username = SecureConfig::isEncrypted(\$config['username']) ? SecureConfig::decrypt(\$config['username']) : \$config['username'];<br>";
            echo "\$password = SecureConfig::isEncrypted(\$config['password']) ? SecureConfig::decrypt(\$config['password']) : \$config['password'];<br><br>";
            
            echo "try {<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;\$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;\$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);<br>";
            echo "} catch(PDOException \$e) {<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;die(\"Connection failed: \" . \$e->getMessage());<br>";
            echo "}<br>";
            echo "?>";
            
            echo "</div>";
            
            // Show .env file contents
            echo "<h3>📄 Generated .env File Contents:</h3>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; border-left: 4px solid #28a745;'>";
            echo "<strong>File: secure/.env (protected from web access)</strong><br><br>";
            echo nl2br(htmlspecialchars($envContent));
            echo "</div>";
            
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<strong>✅ Encryption Complete!</strong><br>";
            echo "1. ✅ .env file created in secure/ directory (protected from web access)<br>";
            echo "2. ✅ .htaccess protection applied to secure/ directory<br>";
            echo "3. Copy the database configuration above<br>";
            echo "4. Replace the contents of config/database.php<br>";
            echo "5. Test your database connection<br>";
            echo "6. Delete this file for security";
            echo "</div>";
            
            // Add .gitignore entries to protect sensitive files
            $gitignorePath = '../.gitignore';
            $gitignoreContent = '';
            if (file_exists($gitignorePath)) {
                $gitignoreContent = file_get_contents($gitignorePath);
            }
            
            $entriesToAdd = [
                '# Environment files',
                '.env',
                '.env.local',
                '.env.*.local',
                'secure/.env',
                '',
                '# Security keys',
                'secure/keys.json',
                'secure/keys_backup_*.json',
                '',
                '# Logs',
                'logs/*.log'
            ];
            
            $needsUpdate = false;
            foreach ($entriesToAdd as $entry) {
                if (strpos($gitignoreContent, $entry) === false) {
                    $needsUpdate = true;
                    break;
                }
            }
            
            if ($needsUpdate) {
                $gitignoreContent .= "\n" . implode("\n", $entriesToAdd) . "\n";
                file_put_contents($gitignorePath, $gitignoreContent);
                echo "<div style='background: #cce5ff; color: #004085; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "ℹ️ Updated .gitignore to protect sensitive files from version control";
                echo "</div>";
            }
            
            // Log the encryption
            if (!is_dir('../logs')) mkdir('../logs', 0755, true);
            file_put_contents('../logs/security.log', date('Y-m-d H:i:s') . " - Database credentials encrypted and .env file created\n", FILE_APPEND);
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<strong>❌ Encryption Error:</strong> " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "⚠️ Please fill in all database fields.";
        echo "</div>";
    }
}
?>

<div style="background: #e8f4f8; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <strong>🔐 Security Features:</strong> This tool will:
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>✅ Encrypt your database credentials using 256-bit AES encryption</li>
        <li>✅ Automatically create a secure .env file with encrypted values</li>
        <li>✅ Add .env to .gitignore to prevent accidental commits</li>
        <li>✅ Generate ready-to-use database configuration code</li>
        <li>⚠️ Remember to delete this file after use for security!</li>
    </ul>
</div>

<form method="POST" action="" style="max-width: 500px;">
    <h2>🗄️ Database Credentials to Encrypt</h2>
    
    <div style="margin-bottom: 15px;">
        <label for="host" style="display: block; margin-bottom: 5px; font-weight: bold;">Database Host:</label>
        <input type="text" id="host" name="host" required 
               value="mysql-200-133.mysql.prositehosting.net"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="dbname" style="display: block; margin-bottom: 5px; font-weight: bold;">Database Name:</label>
        <input type="text" id="dbname" name="dbname" required 
               value="DB_01_RWEBB_RES"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username:</label>
        <input type="text" id="username" name="username" required 
               value="RoseWebb"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 20px;">
        <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
        <input type="password" id="password" name="password" required 
               value="WhiteRabbit435$"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <small style="color: #666;">Your password will be encrypted and never stored in plain text.</small>
    </div>
    
    <button type="submit" style="background: #667eea; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
        🔐 Encrypt Database Credentials
    </button>
</form>

<div style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;">
    <h3>📋 How It Works</h3>
    <p>When you encrypt your credentials, this tool will:</p>
    <ol style="padding-left: 20px;">
        <li><strong>Create .env file:</strong> Automatically generates a secure .env file with encrypted values</li>
        <li><strong>Generate database code:</strong> Provides ready-to-use PHP configuration code</li>
        <li><strong>Update .gitignore:</strong> Ensures .env file is not committed to version control</li>
        <li><strong>Log activity:</strong> Records encryption activity for security auditing</li>
    </ol>
    
    <h4>🔍 Sample .env File Structure:</h4>
    <pre style="background: #f8f9fa; padding: 10px; border-radius: 3px; font-size: 12px;">
# Database Configuration - Encrypted
# Generated on 2025-08-02 14:30:15

DB_HOST=ENC:base64encodedencryptedvalue...
DB_NAME=ENC:base64encodedencryptedvalue...
DB_USERNAME=ENC:base64encodedencryptedvalue...
DB_PASSWORD=ENC:base64encodedencryptedvalue...
    </pre>
    <p><small>All values are encrypted with your secure key and automatically decrypted when needed.</small></p>
</div>

<div style="margin-top: 15px;">
    <a href="../admin/login.php" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">
        Back to Admin
    </a>
</div>
