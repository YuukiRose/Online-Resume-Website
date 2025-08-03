<?php
/**
 * Test D.env Database Connection
 * Test the new D.env file with encrypted database credentials
 */
require_once '../../config/SecureKeyManager.php';
require_once '../../config/SecureConfig.php';
require_once '../../config/EnvLoader.php';

echo "<h1>🧪 Testing D.env Database Connection</h1>";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Initialize encryption system
    echo "<h3>Step 1: Initialize Encryption System</h3>";
    SecureKeyManager::init();
    echo "<p>✅ SecureKeyManager initialized</p>";
    
    // Load D.env file
    echo "<h3>Step 2: Load D.env File</h3>";
    EnvLoader::load();
    echo "<p>✅ EnvLoader executed</p>";
    
    // Check what we got from D.env
    echo "<h3>Step 3: Check Environment Variables</h3>";
    $dbHost = $_ENV['DB_HOST'] ?? 'NOT_FOUND';
    $dbName = $_ENV['DB_NAME'] ?? 'NOT_FOUND';
    $dbUserEncrypted = $_ENV['DB_USER_ENCRYPTED'] ?? 'NOT_FOUND';
    $dbPasswordEncrypted = $_ENV['DB_PASSWORD_ENCRYPTED'] ?? 'NOT_FOUND';
    
    echo "<p><strong>DB_HOST:</strong> " . htmlspecialchars($dbHost) . "</p>";
    echo "<p><strong>DB_NAME:</strong> " . htmlspecialchars($dbName) . "</p>";
    echo "<p><strong>DB_USER_ENCRYPTED:</strong> " . (strlen($dbUserEncrypted) > 20 ? substr($dbUserEncrypted, 0, 20) . "..." : $dbUserEncrypted) . "</p>";
    echo "<p><strong>DB_PASSWORD_ENCRYPTED:</strong> " . (strlen($dbPasswordEncrypted) > 20 ? substr($dbPasswordEncrypted, 0, 20) . "..." : $dbPasswordEncrypted) . "</p>";
    
    // Decrypt credentials
    echo "<h3>Step 4: Decrypt Database Credentials</h3>";
    if ($dbUserEncrypted !== 'NOT_FOUND' && $dbPasswordEncrypted !== 'NOT_FOUND') {
        $decryptedUser = SecureConfig::decrypt($dbUserEncrypted);
        $decryptedPassword = SecureConfig::decrypt($dbPasswordEncrypted);
        
        echo "<p><strong>Decrypted Username:</strong> " . htmlspecialchars($decryptedUser ?: 'DECRYPTION_FAILED') . "</p>";
        echo "<p><strong>Decrypted Password:</strong> " . ($decryptedPassword ? "[DECRYPTED_SUCCESSFULLY]" : "DECRYPTION_FAILED") . "</p>";
        
        // Test database connection
        echo "<h3>Step 5: Test Database Connection</h3>";
        if ($decryptedUser && $decryptedPassword) {
            try {
                $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
                $pdo = new PDO($dsn, $decryptedUser, $decryptedPassword, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                // Test query
                $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as version");
                $result = $stmt->fetch();
                
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>✅ Connection Successful!</h4>";
                echo "<p><strong>Connected Database:</strong> " . htmlspecialchars($result['current_db']) . "</p>";
                echo "<p><strong>MySQL Version:</strong> " . htmlspecialchars($result['version']) . "</p>";
                echo "<p><strong>Host:</strong> " . htmlspecialchars($dbHost) . "</p>";
                echo "<p><strong>Username:</strong> " . htmlspecialchars($decryptedUser) . "</p>";
                echo "</div>";
                
            } catch (PDOException $e) {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>❌ Database Connection Failed</h4>";
                echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>Host:</strong> " . htmlspecialchars($dbHost) . "</p>";
                echo "<p><strong>Database:</strong> " . htmlspecialchars($dbName) . "</p>";
                echo "<p><strong>Username:</strong> " . htmlspecialchars($decryptedUser) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>❌ Credential Decryption Failed</h4>";
            echo "<p>Could not decrypt username or password from D.env file.</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h4>❌ Environment Variables Missing</h4>";
        echo "<p>Could not find encrypted credentials in D.env file.</p>";
        echo "</div>";
    }
    
    // Test the existing database.php
    echo "<h3>Step 6: Test Existing database.php</h3>";
    try {
        require_once '../../config/database.php';
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            if ($result && $result['test'] == 1) {
                echo "<p style='color: green;'>✅ database.php connection successful</p>";
            } else {
                echo "<p style='color: red;'>❌ database.php connection test failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ database.php did not create PDO object</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ database.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>❌ Test Failed</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='recovery_tool.php' style='background: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>🚨 Recovery Tool</a>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>← Back to Dashboard</a>";
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
</style>
