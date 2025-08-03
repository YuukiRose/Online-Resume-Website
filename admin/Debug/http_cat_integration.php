<?php
/**
 * Enhanced Error Handler with HTTP Cats
 * This file demonstrates how to integrate HTTP Cats into your application's error handling
 */

require_once '../../config/admin_auth_check.php';
require_once '../../config/HttpCat.php';

echo "<h1>🐱 HTTP Cat Integration Examples</h1>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🛠️ Integration Examples</h2>";

// Example 1: Simple 404 handler
echo "<h3>Example 1: Simple 404 Handler</h3>";
echo "<pre style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo htmlspecialchars('<?php
// In your 404.php file
require_once "config/HttpCat.php";
HttpCat::show(404, "Page Not Found", "The page you\'re looking for doesn\'t exist.");
?>');
echo "</pre>";

// Example 2: Custom error with fallback
echo "<h3>Example 2: API Error Response</h3>";
echo "<pre style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo htmlspecialchars('<?php
// API endpoint with cat error responses
function apiError($code, $message) {
    if (isset($_SERVER["HTTP_ACCEPT"]) && strpos($_SERVER["HTTP_ACCEPT"], "application/json") !== false) {
        // JSON response for API clients
        header("Content-Type: application/json");
        echo json_encode([
            "error" => true,
            "code" => $code,
            "message" => $message,
            "cat" => HttpCat::getCatUrl($code)
        ]);
    } else {
        // HTML response with cat for browsers
        HttpCat::show($code, null, $message);
    }
}

// Usage
apiError(401, "You need to be logged in to access this resource");
?>');
echo "</pre>";

// Example 3: Custom error pages
echo "<h3>Example 3: Custom Error Pages with Cats</h3>";
echo "<pre style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo htmlspecialchars('<?php
// In your error handling middleware
class ErrorHandler {
    public static function handle($statusCode, $customMessage = null) {
        $messages = [
            401 => "You need to log in first! 🔐",
            403 => "Access denied - You don\'t have permission! 🚫", 
            404 => "Oops! This page went missing! 🔍",
            500 => "Something went wrong on our end! 💥",
            503 => "We\'re temporarily down for maintenance! 🔧"
        ];
        
        $message = $customMessage ?: ($messages[$statusCode] ?? "An error occurred");
        HttpCat::show($statusCode, null, $message);
    }
}

// Usage examples
ErrorHandler::handle(404); // Default 404 message with cat
ErrorHandler::handle(500, "Database connection failed!"); // Custom message
?>');
echo "</pre>";

echo "</div>";

// Interactive examples
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🎮 Try It Yourself!</h2>";
echo "<p>Click these buttons to see HTTP Cats in action:</p>";

$examples = [
    ['code' => 404, 'label' => '404 Not Found', 'desc' => 'Classic not found error'],
    ['code' => 401, 'label' => '401 Unauthorized', 'desc' => 'Authentication required'],
    ['code' => 418, 'label' => "418 I'm a Teapot", 'desc' => 'The famous RFC 2324 joke status'],
    ['code' => 500, 'label' => '500 Server Error', 'desc' => 'Internal server error'],
    ['code' => 503, 'label' => '503 Service Unavailable', 'desc' => 'Service temporarily unavailable']
];

foreach ($examples as $example) {
    echo "<div style='display: inline-block; margin: 10px;'>";
    echo "<a href='?demo={$example['code']}' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "🐱 {$example['label']}";
    echo "</a>";
    echo "<br><small style='color: #666; margin-left: 10px;'>{$example['desc']}</small>";
    echo "</div>";
}
echo "</div>";

// Handle demo requests
if (isset($_GET['demo'])) {
    $demoCode = (int)$_GET['demo'];
    if ($demoCode >= 100 && $demoCode <= 599) {
        echo "<script>window.open('?show={$demoCode}', '_blank');</script>";
    }
}

if (isset($_GET['show'])) {
    $statusCode = (int)$_GET['show'];
    HttpCat::show($statusCode, null, "This is a demo of HTTP status code {$statusCode} with a cute cat! 🐱");
}

// Benefits section
echo "<div style='background: #cce5ff; color: #004085; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>🌟 Benefits of HTTP Cats</h2>";
echo "<ul>";
echo "<li><strong>User-Friendly:</strong> Makes error pages less intimidating and more memorable</li>";
echo "<li><strong>Professional:</strong> Still conveys the technical status code information</li>";
echo "<li><strong>Engaging:</strong> Users are more likely to remember and share fun error pages</li>";
echo "<li><strong>Universal:</strong> Works for both technical and non-technical users</li>";
echo "<li><strong>API-Ready:</strong> Automatically provides JSON responses for API clients</li>";
echo "<li><strong>SEO-Friendly:</strong> Proper HTTP status codes are still sent to search engines</li>";
echo "</ul>";
echo "</div>";

// Implementation checklist
echo "<div style='background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h2>📋 Implementation Checklist</h2>";
echo "<ol>";
echo "<li>✅ Include the HttpCat.php class in your project</li>";
echo "<li>✅ Replace standard error pages with HttpCat::show() calls</li>";
echo "<li>✅ Update your 404.php, 500.php, and other error pages</li>";
echo "<li>✅ Add error handling to API endpoints</li>";
echo "<li>✅ Test with both browser and API requests</li>";
echo "<li>✅ Customize messages for your application context</li>";
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
h1, h2, h3 { color: #2c3e50; }
pre { 
    font-size: 13px; 
    line-height: 1.4; 
    overflow-x: auto;
    white-space: pre-wrap;
}
</style>
