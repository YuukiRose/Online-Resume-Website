<?php
/**
 * Error Page Diagnostic Tool
 * Test error pages and diagnose issues
 */

require_once '../../config/admin_auth_check.php';

echo "<h1>🔍 Error Page Diagnostics</h1>";

// Test if error pages exist and are accessible
$error_pages = [
    '403 Simple' => '../../error_pages/403_simple.php',
    '404 Simple' => '../../error_pages/404_simple.php', 
    '403 Original' => '../../error_pages/403.php',
    '404 Original' => '../../error_pages/404.php',
    'HttpCat Test' => '../../error_pages/test_httpcat.php'
];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 Error Page Files</h2>";

foreach ($error_pages as $name => $path) {
    $exists = file_exists($path);
    $icon = $exists ? '✅' : '❌';
    $color = $exists ? '#28a745' : '#dc3545';
    echo "<p style='color: {$color};'>{$icon} {$name}: " . ($exists ? "Present" : "Missing") . "</p>";
    
    if ($exists && $name === 'HttpCat Test') {
        echo "<iframe src='{$path}' style='width: 100%; height: 200px; border: 1px solid #ddd; margin: 10px 0;'></iframe>";
    }
}
echo "</div>";

// Test .htaccess configuration
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>⚙️ .htaccess Configuration</h2>";

$htaccess_path = '../../.htaccess';
if (file_exists($htaccess_path)) {
    echo "<p>✅ Main .htaccess file exists</p>";
    $content = file_get_contents($htaccess_path);
    echo "<h4>Current ErrorDocument directives:</h4>";
    echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 5px;'>";
    
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        if (stripos($line, 'ErrorDocument') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ Main .htaccess file missing</p>";
}
echo "</div>";

// Direct test links
echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🧪 Direct Tests</h2>";
echo "<p>Test error pages directly:</p>";

$tests = [
    ['url' => '/Luthor/error_pages/403_simple.php', 'desc' => 'Simple 403 page'],
    ['url' => '/Luthor/error_pages/404_simple.php', 'desc' => 'Simple 404 page'],
    ['url' => '/Luthor/error_pages/test_httpcat.php', 'desc' => 'HttpCat test'],
    ['url' => '/Luthor/secure/.env', 'desc' => 'Protected file (should trigger 403)'],
    ['url' => '/Luthor/nonexistent.php', 'desc' => 'Non-existent file (should trigger 404)']
];

foreach ($tests as $test) {
    echo "<div style='margin: 10px 0;'>";
    echo "<a href='{$test['url']}' target='_blank' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>";
    echo "🔗 Test";
    echo "</a>";
    echo " <span style='margin-left: 10px;'>{$test['desc']}</span>";
    echo "</div>";
}
echo "</div>";

// Apache error log check
echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📋 Recent Apache Errors</h2>";
echo "<p>Check for recent errors in the Apache log:</p>";

$error_log_path = 'C:\\xampp\\apache\\logs\\error.log';
if (file_exists($error_log_path)) {
    $log_content = file_get_contents($error_log_path);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -10); // Last 10 lines
    
    echo "<pre style='background: #f5c6cb; padding: 10px; border-radius: 5px; font-size: 12px; overflow-x: auto;'>";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ Could not access Apache error log</p>";
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
h1, h2, h4 { color: #2c3e50; }
pre { 
    font-size: 12px; 
    line-height: 1.4; 
    max-height: 300px;
    overflow-y: auto;
}
</style>
