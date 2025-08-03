<?php
// Test and fix the encrypted database configuration
require_once 'admin_auth_check.php';
require_once 'SecureConfig.php';
require_once 'SecureKeyManager.php';
require_once 'EnvLoader.php';

echo "<h2>Testing Encrypted Database Configuration</h2>";

try {
    // Initialize the key manager
    SecureKeyManager::init();
    
    // Load environment variables
    EnvLoader::load();
    
$config = [
    'host' => EnvLoader::get('DB_HOST', 'c3U0SExtZjlkOEY4cHIxbk1pdkpIbzF0WUhURXBvVzZpd2w3M0x1cVBnSmVFaVZ2UGxkZHN0V2piUUUycWtiMDo6f7+E9qvvVufzlZ8p8KlweQ=='),
    'dbname' => EnvLoader::get('DB_NAME', 'L1JtcVc5cFNHL3JMYTA2YU1lS1daUT09Ojp5tq/Lcs5MYZOTFPEJ98u5'),
    'username' => EnvLoader::get('DB_USERNAME', 'NlJTcjdRdFA4WWpsTVo2bmFvUlpkZz09Ojo/kyexd2xTSgHAVCr7jC6B'),
    'password' => EnvLoader::get('DB_PASSWORD', 'L2xDOVUzS2pqeGJsQ3dmOEJlQUZXZz09OjqCtNp2ZMrntRYgitMZIqWR')
];

    
    echo "<h3>Decrypting Database Credentials:</h3>";
    
    // Decrypt credentials
    $host = SecureConfig::isEncrypted($config['host']) ? SecureConfig::decrypt($config['host']) : $config['host'];
    $dbname = SecureConfig::isEncrypted($config['dbname']) ? SecureConfig::decrypt($config['dbname']) : $config['dbname'];
    $username = SecureConfig::isEncrypted($config['username']) ? SecureConfig::decrypt($config['username']) : $config['username'];
    $password = SecureConfig::isEncrypted($config['password']) ? SecureConfig::decrypt($config['password']) : $config['password'];
    
    echo "Host: " . ($host ? $host : "FAILED TO DECRYPT") . "<br>";
    echo "Database: " . ($dbname ? $dbname : "FAILED TO DECRYPT") . "<br>";
    echo "Username: " . ($username ? $username : "FAILED TO DECRYPT") . "<br>";
    echo "Password: " . ($password !== false ? "[DECRYPTED]" : "FAILED TO DECRYPT") . "<br><br>";
    
    // Test database connection
    if ($host && $dbname && $username !== false && $password !== false) {
        echo "<h3>Testing Database Connection:</h3>";
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test query
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result && $result['test'] == 1) {
                echo "✅ Database connection successful!<br>";
                echo "✅ Encrypted database configuration is working properly!<br>";
            } else {
                echo "❌ Database connected but test query failed<br>";
            }
            
        } catch(PDOException $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
            
            // If the decrypted credentials don't work, let's create new encrypted ones for XAMPP defaults
            echo "<br><h3>Creating New Encrypted Credentials for XAMPP:</h3>";
            
            $xamppHost = SecureConfig::encrypt('localhost');
            $xamppDbname = SecureConfig::encrypt('luthor_db');
            $xamppUsername = SecureConfig::encrypt('root');
            $xamppPassword = SecureConfig::encrypt('');
            
            echo "New encrypted credentials for XAMPP:<br>";
            echo "Host: " . $xamppHost . "<br>";
            echo "Database: " . $xamppDbname . "<br>";
            echo "Username: " . $xamppUsername . "<br>";
            echo "Password: " . $xamppPassword . "<br>";
            
            echo "<br>Copy these to your database.php file to replace the existing encrypted values.";
        }
    } else {
        echo "❌ Failed to decrypt one or more database credentials<br>";
        
        // Create new encrypted credentials for XAMPP
        echo "<br><h3>Creating New Encrypted Credentials for XAMPP:</h3>";
        
        $xamppHost = SecureConfig::encrypt('localhost');
        $xamppDbname = SecureConfig::encrypt('luthor_db');
        $xamppUsername = SecureConfig::encrypt('root');
        $xamppPassword = SecureConfig::encrypt('');
        
        echo "New encrypted credentials for XAMPP:<br>";
        echo "Host: " . $xamppHost . "<br>";
        echo "Database: " . $xamppDbname . "<br>";
        echo "Username: " . $xamppUsername . "<br>";
        echo "Password: " . $xamppPassword . "<br>";
        
        echo "<br>Copy these to your database.php file to replace the existing encrypted values.";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    
    // If keys don't exist, generate them
    if (strpos($e->getMessage(), 'Keys file not found') !== false) {
        echo "<br><h3>Generating encryption keys...</h3>";
        echo "<p><a href='generate_keys.php' target='_blank'>Click here to generate encryption keys first</a></p>";
    }
}
?>
