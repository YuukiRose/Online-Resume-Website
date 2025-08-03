<?php
require_once 'config/admin_auth_check.php';
require_once 'includes/linkedin_helper.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test LinkedIn profile picture scraping
$test_urls = [
    'https://www.linkedin.com/in/rose-webb-798014215/',
    'https://linkedin.com/in/test-profile'
];

echo "<h1>LinkedIn Profile Picture Test - Updated with evi-image Pattern</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; } 
    .test { margin: 20px 0; padding: 20px; border: 1px solid #ddd; } 
    .debug { background: #f5f5f5; padding: 10px; margin: 10px 0; font-family: monospace; } 
    .success { background: #d4edda; color: #155724; padding: 10px; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; }
    img { max-width: 200px; height: auto; border: 1px solid #ddd; }
    .pattern { background: #e3f2fd; padding: 5px; margin: 5px 0; }
</style>";

// Check if cURL is available
echo "<div class='debug'>";
echo "<h3>System Check</h3>";
echo "<p><strong>cURL Available:</strong> " . (function_exists('curl_init') ? 'Yes' : 'No') . "</p>";
echo "<p><strong>allow_url_fopen:</strong> " . (ini_get('allow_url_fopen') ? 'Yes' : 'No') . "</p>";
echo "</div>";

foreach ($test_urls as $url) {
    echo "<div class='test'>";
    echo "<h3>Testing: " . htmlspecialchars($url) . "</h3>";
    
    $username = extractLinkedInUsername($url);
    echo "<p><strong>Extracted Username:</strong> " . htmlspecialchars($username ?: 'None') . "</p>";
    
    // Show the patterns we're looking for
    echo "<div class='debug'>";
    echo "<strong>Searching for these patterns:</strong><br>";
    echo "<div class='pattern'>1. evi-image lazy-image imgecit-profile-photo-frame</div>";
    echo "<div class='pattern'>2. Open Graph og:image</div>";
    echo "<div class='pattern'>3. media.licdn.com URLs</div>";
    echo "<div class='pattern'>4. Traditional profile-photo classes</div>";
    echo "</div>";
    
    // Test actual scraping with detailed debugging
    echo "<div class='debug'><strong>Attempting to scrape LinkedIn...</strong><br>";
    
    // Manual scraping test to see what we get
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: $http_code<br>";
        
        if ($html) {
            // Look for the specific pattern you showed
            $evi_pattern = '/<img[^>]*class="[^"]*evi-image[^"]*lazy-image[^"]*imgecit-profile-photo-frame[^"]*"[^>]*src="([^"]+)"/i';
            if (preg_match($evi_pattern, $html, $matches)) {
                echo "<div class='success'>✅ Found evi-image pattern: " . htmlspecialchars($matches[1]) . "</div>";
            } else {
                echo "<div class='error'>❌ evi-image pattern not found</div>";
            }
            
            // Look for media.licdn.com URLs
            if (preg_match('/src="(https:\/\/media\.licdn\.com\/[^"]+)"/i', $html, $matches)) {
                echo "<div class='success'>✅ Found media.licdn.com URL: " . htmlspecialchars($matches[1]) . "</div>";
            } else {
                echo "<div class='error'>❌ No media.licdn.com URLs found</div>";
            }
            
            // Look for Open Graph
            if (preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $matches)) {
                echo "<div class='success'>✅ Found Open Graph image: " . htmlspecialchars($matches[1]) . "</div>";
            } else {
                echo "<div class='error'>❌ No Open Graph image found</div>";
            }
        } else {
            echo "<div class='error'>❌ No HTML content retrieved</div>";
        }
    }
    
    $scraped_pic = fetchLinkedInProfilePicture($url);
    echo "Final scraped result: " . htmlspecialchars($scraped_pic ?: 'Failed to scrape') . "</div>";
    
    $profile_pic = getLinkedInProfilePicture($url, 'Rose Webb');
    echo "<p><strong>Profile Picture URL:</strong> " . htmlspecialchars($profile_pic ?: 'None') . "</p>";
    
    if ($profile_pic) {
        echo "<p><strong>Profile Picture:</strong><br><img src='" . htmlspecialchars($profile_pic) . "' alt='Profile Picture' onerror='this.style.border=\"2px solid red\"; this.alt=\"Failed to load\"'></p>";
    }
    
    echo "</div>";
}

// Test just the placeholder generation
echo "<div class='test'>";
echo "<h3>Testing Placeholder Generation</h3>";
$placeholder = generateProfessionalPlaceholder('Rose Webb');
echo "<p><strong>Professional Placeholder (should show 'RW'):</strong><br><img src='" . htmlspecialchars($placeholder) . "' alt='Professional Placeholder'></p>";
echo "<p>URL: " . htmlspecialchars($placeholder) . "</p>";
echo "</div>";
?>
