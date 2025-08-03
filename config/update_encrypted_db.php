<?php
// Script to update database.php with properly encrypted XAMPP credentials
require_once 'admin_auth_check.php';
require_once 'SecureConfig.php';
require_once 'SecureKeyManager.php';

echo "<h2>Updating Database Configuration with Encrypted XAMPP Credentials</h2>";

try {
    // Initialize the key manager
    SecureKeyManager::init();
    
    // Encrypt XAMPP default credentials
    $xamppHost = SecureConfig::encrypt('localhost');
    $xamppDbname = SecureConfig::encrypt('luthor_db');
    $xamppUsername = SecureConfig::encrypt('root');
    $xamppPassword = SecureConfig::encrypt('');
    
    echo "<h3>Generated Encrypted Credentials:</h3>";
    echo "Host: " . $xamppHost . "<br>";
    echo "Database: " . $xamppDbname . "<br>";
    echo "Username: " . $xamppUsername . "<br>";
    echo "Password: " . $xamppPassword . "<br><br>";
    
    // Update the database.php file
    $databasePhpContent = '<?php
// Database configuration - Update these with your database settings
require_once \'SecureConfig.php\';
require_once \'EnvLoader.php\';
EnvLoader::load();

$config = [
    \'host\' => EnvLoader::get(\'DB_HOST\', \'' . $xamppHost . '\'),
    \'dbname\' => EnvLoader::get(\'DB_NAME\', \'' . $xamppDbname . '\'),
    \'username\' => EnvLoader::get(\'DB_USERNAME\', \'' . $xamppUsername . '\'),
    \'password\' => EnvLoader::get(\'DB_PASSWORD\', \'' . $xamppPassword . '\')
];

// Decrypt credentials
$host = SecureConfig::isEncrypted($config[\'host\']) ? SecureConfig::decrypt($config[\'host\']) : $config[\'host\'];
$dbname = SecureConfig::isEncrypted($config[\'dbname\']) ? SecureConfig::decrypt($config[\'dbname\']) : $config[\'dbname\'];
$username = SecureConfig::isEncrypted($config[\'username\']) ? SecureConfig::decrypt($config[\'username\']) : $config[\'username\'];
$password = SecureConfig::isEncrypted($config[\'password\']) ? SecureConfig::decrypt($config[\'password\']) : $config[\'password\'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If database doesn\'t exist, try to create it
    if (strpos($e->getMessage(), \'1049\') !== false || strpos($e->getMessage(), "doesn\'t exist") !== false) {
        try {
            $pdo_create = new PDO("mysql:host=$host;charset=utf8", $username, $password);
            $pdo_create->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_create->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Now connect to the newly created database
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $create_e) {
            die("Database creation failed: " . $create_e->getMessage() . "<br><br>
                 Please make sure XAMPP MySQL is running and create the database \'$dbname\' manually.<br>
                 You can do this by visiting <a href=\'http://localhost/phpmyadmin\' target=\'_blank\'>phpMyAdmin</a>");
        }
    } else {
        die("Connection failed: " . $e->getMessage() . "<br><br>
             Please make sure XAMPP MySQL is running.<br>
             Encrypted credentials: Host=$host, Username=$username, Database=$dbname");
    }
}
?>';

    // Write the updated database.php file
    if (file_put_contents('database.php', $databasePhpContent)) {
        echo "✅ Successfully updated database.php with encrypted XAMPP credentials!<br><br>";
        
        // Test the new configuration
        echo "<h3>Testing Updated Configuration:</h3>";
        require_once 'database.php';
        
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result && $result['test'] == 1) {
            echo "✅ Database connection with encrypted credentials successful!<br>";
            echo "✅ You can now use the encrypted database system!<br><br>";
            
            echo "<p><strong>The following files now use encrypted database credentials:</strong></p>";
            echo "<ul>";
            echo "<li>All user authentication files (login.php, register.php, etc.)</li>";
            echo "<li>All debug tools in admin/Debug/</li>";
            echo "<li>Email verification system</li>";
            echo "<li>2FA system</li>";
            echo "</ul>";
            
            echo "<p><a href='../user/login.php'>Test Login Page</a> | ";
            echo "<a href='../admin/Debug/database_debug.php'>Test Debug Tools</a></p>";
        } else {
            echo "❌ Database connected but test query failed<br>";
        }
        
    } else {
        echo "❌ Failed to write database.php file. Check file permissions.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    
    if (strpos($e->getMessage(), 'Keys file not found') !== false) {
        echo "<br><h3>Encryption keys not found!</h3>";
        echo "<p><a href='generate_keys.php' target='_blank'>Click here to generate encryption keys first</a></p>";
    }
}
?>
