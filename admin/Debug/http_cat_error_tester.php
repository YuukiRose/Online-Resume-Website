<?php
/**
 * HTTP Cat Error Page Tester
 * Test and verify that custom error pages with HTTP Cats are working correctly
 */

require_once '../../config/admin_auth_check.php';

echo "<h1>🔧 HTTP Cat Error Page Tester</h1>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🧪 Test Error Pages</h2>";
echo "<p>Click these links to test if the HTTP Cat error pages are working correctly:</p>";

$tests = [
    [
        'url' => '/Luthor/secure/.env',
        'expected' => '403 Forbidden',
        'description' => 'Try to access protected .env file',
        'cat_code' => 403
    ],
    [
        'url' => '/Luthor/nonexistent-page.php',
        'expected' => '404 Not Found', 
        'description' => 'Try to access non-existent page',
        'cat_code' => 404
    ],
    [
        'url' => '/Luthor/secure/keys.json',
        'expected' => '403 Forbidden',
        'description' => 'Try to access protected keys file',
        'cat_code' => 403
    ]
];

foreach ($tests as $test) {
    echo "<div style='background: white; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>🎯 {$test['description']}</h4>";
    echo "<p><strong>Expected:</strong> {$test['expected']} with HTTP Cat {$test['cat_code']}</p>";
    echo "<p><strong>Test URL:</strong> <code>{$test['url']}</code></p>";
    echo "<a href='{$test['url']}' target='_blank' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>";
    echo "🐱 Test This Error";
    echo "</a>";
    echo "</div>";
}

echo "</div>";

// Manual error trigger section
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🎮 Manual Error Triggers</h2>";
echo "<p>Test HTTP Cat error pages directly:</p>";

$error_codes = [403, 404, 500];
foreach ($error_codes as $code) {
    echo "<a href='?trigger={$code}' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>";
    echo "🐱 Trigger {$code} Error";
    echo "</a>";
}
echo "</div>";

// Handle manual triggers
if (isset($_GET['trigger'])) {
    $code = (int)$_GET['trigger'];
    if (in_array($code, [403, 404, 500])) {
        // Include and show the appropriate error page
        require_once '../../config/HttpCat.php';
        
        $messages = [
            403 => "This is a test of the 403 Forbidden error page with HTTP Cats! 🔒",
            404 => "This is a test of the 404 Not Found error page with HTTP Cats! 🔍", 
            500 => "This is a test of the 500 Server Error page with HTTP Cats! 💥"
        ];
        
        HttpCat::show($code, null, $messages[$code]);
        exit;
    }
}

// Configuration check
echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>⚙️ Configuration Status</h2>";

$config_checks = [
    'Main .htaccess' => file_exists('../../.htaccess'),
    'Secure .htaccess' => file_exists('../../secure/.htaccess'),
    'Error pages directory' => is_dir('../../error_pages'),
    '403 error page' => file_exists('../../error_pages/403.php'),
    '404 error page' => file_exists('../../error_pages/404.php'),
    '500 error page' => file_exists('../../error_pages/500.php'),
    'HttpCat class' => file_exists('../../config/HttpCat.php')
];

echo "<ul>";
foreach ($config_checks as $check => $status) {
    $icon = $status ? '✅' : '❌';
    $color = $status ? '#28a745' : '#dc3545';
    echo "<li style='color: {$color}; margin: 5px 0;'>{$icon} {$check}</li>";
}
echo "</ul>";
echo "</div>";

// Instructions
echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📋 Troubleshooting</h2>";
echo "<ol>";
echo "<li><strong>If you still see Apache error pages:</strong> Make sure the .htaccess files are properly configured and Apache allows .htaccess overrides</li>";
echo "<li><strong>Check AllowOverride:</strong> Your Apache configuration might need <code>AllowOverride All</code> for the directory</li>";
echo "<li><strong>Restart Apache:</strong> Sometimes changes require an Apache restart to take effect</li>";
echo "<li><strong>Check file permissions:</strong> Make sure the error page files are readable by the web server</li>";
echo "<li><strong>Test locally first:</strong> Try the manual triggers above to verify HTTP Cat integration works</li>";
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
h1, h2, h3, h4 { color: #2c3e50; }
code { 
    background: #e9ecef; 
    padding: 2px 6px; 
    border-radius: 3px; 
    font-family: 'Courier New', monospace;
}
</style>
