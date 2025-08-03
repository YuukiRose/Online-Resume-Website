<?php
/**
 * XAMPP Apache Control Tool
 * Provides controls for Apache management
 */

require_once '../../config/admin_auth_check.php';

echo "<h1>🔄 Apache Control Panel</h1>";

// Handle restart request
if (isset($_POST['restart_apache'])) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🔄 Attempting to restart Apache...</h3>";
    
    // Method 1: Try to restart Apache via XAMPP
    $xampp_path = 'C:\\xampp\\apache\\bin\\httpd.exe';
    if (file_exists($xampp_path)) {
        echo "<p>📁 Found Apache at: {$xampp_path}</p>";
        
        // Kill existing httpd processes
        exec('taskkill /F /IM httpd.exe 2>&1', $kill_output, $kill_return);
        echo "<p>🛑 Stopping Apache processes...</p>";
        
        // Wait a moment
        sleep(2);
        
        // Start Apache again
        $start_cmd = '"' . $xampp_path . '" -D FOREGROUND';
        // We'll start it in background using Windows start command
        exec('start /B "Apache" "' . $xampp_path . '"', $start_output, $start_return);
        echo "<p>▶️ Starting Apache...</p>";
        
        sleep(2);
        
        // Check if it's running
        exec('tasklist | findstr httpd', $check_output);
        if (!empty($check_output)) {
            echo "<p style='color: #28a745;'>✅ Apache appears to be running!</p>";
        } else {
            echo "<p style='color: #dc3545;'>❌ Apache may not have started properly</p>";
        }
    } else {
        echo "<p style='color: #dc3545;'>❌ Could not find Apache executable</p>";
    }
    echo "</div>";
}

// Show current status
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📊 Current Status</h2>";

// Check if Apache is running
exec('tasklist | findstr httpd', $output);
if (!empty($output)) {
    echo "<p style='color: #28a745;'>✅ Apache is running</p>";
    foreach ($output as $line) {
        echo "<code style='display: block; background: #e9ecef; padding: 5px; margin: 2px 0;'>{$line}</code>";
    }
} else {
    echo "<p style='color: #dc3545;'>❌ Apache is not running</p>";
}
echo "</div>";

// Control buttons
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🎛️ Controls</h2>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<button type='submit' name='restart_apache' style='background: #dc3545; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
echo "🔄 Restart Apache";
echo "</button>";
echo "</form>";

echo "<p><strong>Alternative methods:</strong></p>";
echo "<ul>";
echo "<li>Use the XAMPP Control Panel GUI</li>";
echo "<li>Run commands in an elevated command prompt</li>";
echo "<li>Restart from Windows Services (if Apache is installed as a service)</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🛠️ Manual Restart Instructions</h2>";
echo "<ol>";
echo "<li><strong>Open XAMPP Control Panel</strong> (usually in C:\\xampp\\xampp-control.exe)</li>";
echo "<li><strong>Click 'Stop'</strong> next to Apache if it's running</li>";
echo "<li><strong>Wait 2-3 seconds</strong></li>";
echo "<li><strong>Click 'Start'</strong> next to Apache</li>";
echo "<li><strong>Test your .htaccess changes</strong> by accessing a protected file</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='htaccess_test.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500; margin-right: 10px;'>🧪 Test .htaccess</a>";
echo "<a href='../debug_dashboard.php' style='background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 25px; font-weight: 500;'>← Back to Dashboard</a>";
echo "</div>";
?>

<style>
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    margin: 20px; 
    background: #f8f9fa;
}
h1, h2 { color: #2c3e50; }
code { 
    font-family: 'Courier New', monospace;
    font-size: 12px;
}
button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
</style>
