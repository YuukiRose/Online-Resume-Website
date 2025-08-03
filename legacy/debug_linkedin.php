<?php
require_once 'includes/linkedin_helper.php';

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>LinkedIn Scraping Deep Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; } 
    .debug { background: #f5f5f5; padding: 15px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
    .error { background: #f8d7da; color: #721c24; padding: 10px; }
    .success { background: #d4edda; color: #155724; padding: 10px; }
    .raw-html { max-height: 300px; overflow-y: auto; border: 1px solid #ccc; }
</style>";

$url = 'https://www.linkedin.com/in/rose-webb-798014215/';

echo "<h3>Testing URL: " . htmlspecialchars($url) . "</h3>";

if (function_exists('curl_init')) {
    echo "<div class='success'>✅ cURL is available</div>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Add headers to mimic a real browser
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'DNT: 1',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
    ]);
    
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    echo "<div class='debug'>";
    echo "HTTP Code: $http_code\n";
    echo "Error: " . ($error ?: 'None') . "\n";
    echo "Effective URL: " . $info['url'] . "\n";
    echo "Content Type: " . $info['content_type'] . "\n";
    echo "Total Time: " . $info['total_time'] . "s\n";
    echo "Size Downloaded: " . strlen($html) . " bytes\n";
    echo "</div>";
    
    if ($html) {
        echo "<div class='success'>✅ HTML Content Retrieved (" . strlen($html) . " bytes)</div>";
        
        // Search for different patterns
        echo "<h4>Pattern Search Results:</h4>";
        
        // Pattern 1: evi-image
        $pattern1 = '/<img[^>]*class="[^"]*evi-image[^"]*lazy-image[^"]*imgecit-profile-photo-frame[^"]*"[^>]*src="([^"]+)"/i';
        if (preg_match($pattern1, $html, $matches1)) {
            echo "<div class='success'>✅ Found evi-image pattern: " . htmlspecialchars($matches1[1]) . "</div>";
        } else {
            echo "<div class='error'>❌ evi-image pattern not found</div>";
        }
        
        // Pattern 2: Any media.licdn.com
        if (preg_match_all('/src="(https:\/\/media\.licdn\.com\/[^"]+)"/i', $html, $matches2)) {
            echo "<div class='success'>✅ Found " . count($matches2[1]) . " media.licdn.com URLs:</div>";
            foreach ($matches2[1] as $url) {
                echo "<div class='debug'>" . htmlspecialchars($url) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ No media.licdn.com URLs found</div>";
        }
        
        // Pattern 3: Open Graph
        if (preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $matches3)) {
            echo "<div class='success'>✅ Found Open Graph image: " . htmlspecialchars($matches3[1]) . "</div>";
        } else {
            echo "<div class='error'>❌ No Open Graph image found</div>";
        }
        
        // Pattern 4: Any img with profile in class
        if (preg_match_all('/<img[^>]*class="[^"]*profile[^"]*"[^>]*src="([^"]+)"/i', $html, $matches4)) {
            echo "<div class='success'>✅ Found " . count($matches4[1]) . " profile images:</div>";
            foreach ($matches4[1] as $url) {
                echo "<div class='debug'>" . htmlspecialchars($url) . "</div>";
            }
        } else {
            echo "<div class='error'>❌ No profile class images found</div>";
        }
        
        // Show a sample of the HTML to see what we're actually getting
        echo "<h4>HTML Sample (first 2000 characters):</h4>";
        echo "<div class='debug raw-html'>" . htmlspecialchars(substr($html, 0, 2000)) . "</div>";
        
        // Look for any img tags
        if (preg_match_all('/<img[^>]*>/i', $html, $all_imgs)) {
            echo "<h4>All IMG tags found (" . count($all_imgs[0]) . "):</h4>";
            echo "<div class='debug raw-html'>";
            foreach (array_slice($all_imgs[0], 0, 10) as $img) {
                echo htmlspecialchars($img) . "\n\n";
            }
            if (count($all_imgs[0]) > 10) {
                echo "... and " . (count($all_imgs[0]) - 10) . " more";
            }
            echo "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ No HTML content retrieved</div>";
    }
    
} else {
    echo "<div class='error'>❌ cURL is not available</div>";
}
?>
