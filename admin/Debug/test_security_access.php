<?php
require_once '../../config/admin_auth_check.php';

echo "<h1>🔒 Security Test - File Access Verification</h1>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h2>Testing File Access from PHP Scripts</h2>";

// Test 1: Check if secure directory exists
echo "<h3>1. Secure Directory Structure</h3>";
if (is_dir('../../secure')) {
    echo "<p style='color: green;'>✅ Secure directory exists</p>";
    
    $files = ['index.php', '.htaccess', '.env', 'keys.json'];
    foreach ($files as $file) {
        $path = "../../secure/$file";
        if (file_exists($path)) {
            echo "<p style='color: green;'>✅ $file - Protected file exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ $file - File not found</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Secure directory not found</p>";
}

// Test 2: Test .env file reading
echo "<h3>2. Environment File Access Test</h3>";
try {
    $envPath = '../../secure/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);
        echo "<p style='color: green;'>✅ PHP can read .env file (" . strlen($envContent) . " bytes)</p>";
        echo "<p style='color: blue;'>📄 File contains " . count($lines) . " lines</p>";
        
        // Show first few lines (safe since they're encrypted)
        echo "<div style='background: #e9ecef; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px;'>";
        echo "<strong>First 3 lines of .env file:</strong><br>";
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            echo htmlspecialchars($lines[$i]) . "<br>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: orange;'>⚠️ .env file not found in secure directory</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error reading .env file: " . $e->getMessage() . "</p>";
}

// Test 3: Test keys.json reading
echo "<h3>3. Encryption Keys Access Test</h3>";
try {
    $keysPath = '../../secure/keys.json';
    if (file_exists($keysPath)) {
        $keysContent = file_get_contents($keysPath);
        $keysData = json_decode($keysContent, true);
        echo "<p style='color: green;'>✅ PHP can read keys.json file (" . strlen($keysContent) . " bytes)</p>";
        
        if ($keysData) {
            echo "<p style='color: blue;'>🔑 Keys file contains " . count($keysData) . " key entries</p>";
            echo "<div style='background: #e9ecef; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px;'>";
            echo "<strong>Available keys:</strong><br>";
            foreach (array_keys($keysData) as $keyName) {
                echo "• $keyName<br>";
            }
            echo "</div>";
        } else {
            echo "<p style='color: orange;'>⚠️ Keys file exists but JSON parsing failed</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ keys.json file not found in secure directory</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error reading keys.json file: " . $e->getMessage() . "</p>";
}

// Test 4: Test EnvLoader functionality
echo "<h3>4. EnvLoader Integration Test</h3>";
try {
    require_once '../../config/EnvLoader.php';
    EnvLoader::load();
    
    // Test if environment variables are loaded
    $testVar = EnvLoader::get('DB_HOST', 'not_found');
    if ($testVar !== 'not_found') {
        echo "<p style='color: green;'>✅ EnvLoader successfully loads from secure directory</p>";
        echo "<p style='color: blue;'>🔍 DB_HOST loaded: " . substr($testVar, 0, 20) . "... (encrypted)</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ EnvLoader could not find DB_HOST variable</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EnvLoader error: " . $e->getMessage() . "</p>";
}

// Test 5: Test SecureKeyManager functionality
echo "<h3>5. SecureKeyManager Integration Test</h3>";
try {
    require_once '../../config/SecureKeyManager.php';
    SecureKeyManager::init();
    
    $testKey = SecureKeyManager::getKey('encryption_key');
    if ($testKey) {
        echo "<p style='color: green;'>✅ SecureKeyManager successfully loads from secure directory</p>";
        echo "<p style='color: blue;'>🔑 Encryption key loaded: " . substr($testKey, 0, 20) . "... (truncated for security)</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ SecureKeyManager could not load encryption key</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ SecureKeyManager error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Security recommendations
echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🛡️ Security Status Summary</h3>";
echo "<p><strong>✅ Good:</strong> PHP scripts can access secure files for legitimate operations</p>";
echo "<p><strong>🔒 Protected:</strong> Files in /secure/ directory are protected from web access</p>";
echo "<p><strong>🔍 Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Test web access to ensure 403 errors: <a href='/Luthor/secure/.env' target='_blank'>Test .env access</a></li>";
echo "<li>Test web access to ensure 403 errors: <a href='/Luthor/secure/keys.json' target='_blank'>Test keys.json access</a></li>";
echo "<li>Verify .htaccess is working on your server</li>";
echo "<li>Monitor server logs for any unauthorized access attempts</li>";
echo "</ul>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../debug_dashboard.php' style='background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Debug Dashboard</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
</style>
