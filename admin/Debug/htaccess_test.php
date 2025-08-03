<?php
/**
 * Simple .htaccess Test Tool
 * Quick check to see if .htaccess files are working
 */

require_once '../../config/admin_auth_check.php';

echo "<h1>⚙️ .htaccess Test Tool</h1>";

// Check if .htaccess files exist
$htaccess_files = [
    'Main .htaccess' => '../../.htaccess',
    'Secure .htaccess' => '../../secure/.htaccess'
];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📁 .htaccess File Status</h2>";
foreach ($htaccess_files as $name => $path) {
    $exists = file_exists($path);
    $icon = $exists ? '✅' : '❌';
    $color = $exists ? '#28a745' : '#dc3545';
    echo "<p style='color: {$color};'>{$icon} {$name}: " . ($exists ? "Present" : "Missing") . "</p>";
    
    if ($exists) {
        $content = file_get_contents($path);
        $has_error_docs = strpos($content, 'ErrorDocument') !== false;
        $error_icon = $has_error_docs ? '✅' : '⚠️';
        $error_color = $has_error_docs ? '#28a745' : '#f39c12';
        echo "<p style='color: {$error_color}; margin-left: 20px;'>{$error_icon} ErrorDocument directives: " . ($has_error_docs ? "Configured" : "Not found") . "</p>";
    }
}
echo "</div>";

// Test URLs
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🧪 Quick Tests</h2>";
echo "<p>Click these to test if custom error pages are working:</p>";

$test_urls = [
    ['url' => '/Luthor/secure/.env', 'desc' => 'Protected .env file (should show HTTP Cat 403)'],
    ['url' => '/Luthor/doesnotexist.php', 'desc' => 'Non-existent page (should show HTTP Cat 404)']
];

foreach ($test_urls as $test) {
    echo "<div style='margin: 10px 0;'>";
    echo "<a href='{$test['url']}' target='_blank' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>";
    echo "🔗 Test Link";
    echo "</a>";
    echo " <span style='margin-left: 10px;'>{$test['desc']}</span>";
    echo "</div>";
}

echo "</div>";

// Instructions
echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>💡 Instructions</h2>";
echo "<ol>";
echo "<li>If the tests above still show the default Apache error page, the .htaccess configuration may not be taking effect</li>";
echo "<li>Make sure your Apache configuration allows .htaccess overrides (AllowOverride All)</li>";
echo "<li>Try refreshing/reloading the XAMPP Apache service</li>";
echo "<li>Check that the error page files exist and are accessible</li>";
echo "</ol>";
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
h1, h2 { color: #2c3e50; }
</style>
