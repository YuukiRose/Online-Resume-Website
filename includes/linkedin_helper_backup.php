<?php
/**
 * LinkedIn Profile Picture Helper Functions
 */

/**
 * Extract LinkedIn username from profile URL
 * @param string $linkedin_url The LinkedIn profile URL
 * @return string|null The username or null if invalid
 */
function extractLinkedInUsername($linkedin_url) {
    if (empty($linkedin_url)) {
        return null;
    }
    
    // Match LinkedIn profile URLs
    $patterns = [
        '/linkedin\.com\/in\/([a-zA-Z0-9\-]+)/',
        '/linkedin\.com\/pub\/([a-zA-Z0-9\-]+)/',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $linkedin_url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Generate LinkedIn profile picture URL
 * Note: LinkedIn actively blocks automated scraping for privacy and security.
 * This function provides multiple approaches with upload as the primary method.
 * @param string $linkedin_url The LinkedIn profile URL
 * @param string $user_name The user's full name for fallback
 * @return string|null The profile picture URL or null
 */
function getLinkedInProfilePicture($linkedin_url, $user_name = '') {
    $username = extractLinkedInUsername($linkedin_url);
    
    if (!$username) {
        return null;
    }
    
    // LinkedIn actively blocks scraping, so we focus on user-uploaded images
    // and professional placeholders. The scraping is kept as a fallback that
    // may work in some cases but is not reliable.
    
    // Method 1: Try to extract profile picture (likely to fail due to LinkedIn's protection)
    $profile_pic = fetchLinkedInProfilePicture($linkedin_url);
    if ($profile_pic) {
        return $profile_pic;
    }
    
    // Method 2: Use user's actual name for professional placeholder
    if (!empty($user_name)) {
        return generateProfessionalPlaceholder($user_name);
    }
    
    // Method 3: Fallback to username-based placeholder
    return generateLinkedInPlaceholder($username);
}

/**
 * Attempt to fetch actual LinkedIn profile picture
 * @param string $linkedin_url The LinkedIn profile URL
 * @return string|null The profile picture URL or null
 */
function fetchLinkedInProfilePicture($linkedin_url) {
    // Check if cURL is available
    if (!function_exists('curl_init')) {
        return null;
    }
    
    try {
        // Use cURL to fetch the LinkedIn page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $linkedin_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("LinkedIn scraping error: " . $error);
            return null;
        }
        
        if ($http_code === 200 && $html) {
            // Look for profile picture in the HTML
            // LinkedIn uses various patterns for profile pictures
            $patterns = [
                // Current LinkedIn profile photo structure (2024-2025)
                '/<img[^>]*class="[^"]*evi-image[^"]*lazy-image[^"]*imgecit-profile-photo-frame[^"]*"[^>]*src="([^"]+)"/i',
                '/<img[^>]*src="([^"]+)"[^>]*class="[^"]*evi-image[^"]*lazy-image[^"]*imgecit-profile-photo-frame[^"]*"/i',
                
                // Open Graph image (most reliable)
                '/<meta property="og:image" content="([^"]+)"/i',
                // Profile photo classes
                '/<img[^>]+class="[^"]*profile-photo[^"]*"[^>]+src="([^"]+)"/i',
                '/<img[^>]+src="([^"]+)"[^>]+class="[^"]*profile-photo[^"]*"/i',
                '/<img[^>]+class="[^"]*pv-top-card-profile-picture[^"]*"[^>]+src="([^"]+)"/i',
                '/<img[^>]+src="([^"]+)"[^>]+class="[^"]*pv-top-card-profile-picture[^"]*"/i',
                // Additional patterns for different LinkedIn layouts
                '/<img[^>]+data-delayed-url="([^"]+)"/i',
                '/<img[^>]+class="[^"]*presence-entity__image[^"]*"[^>]+src="([^"]+)"/i',
                // LinkedIn media CDN patterns
                '/<img[^>]*src="(https:\/\/media\.licdn\.com\/dms\/image\/[^"]+)"/i',
                '/<img[^>]*src="(https:\/\/media\.linkedin\.com\/[^"]+)"/i'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $image_url = $matches[1];
                    
                    // Clean up and decode the URL
                    $image_url = html_entity_decode($image_url);
                    
                    // Validate that it's a valid LinkedIn image URL
                    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                        // Check if it's a LinkedIn media URL or has image extension
                        $is_linkedin_media = (strpos($image_url, 'media.licdn.com') !== false || 
                                            strpos($image_url, 'media.linkedin.com') !== false);
                        $has_image_extension = preg_match('/\.(jpg|jpeg|png|webp)/i', $image_url);
                        
                        if ($is_linkedin_media || $has_image_extension) {
                            // Test if the image is actually accessible
                            $headers = @get_headers($image_url, 1);
                            if ($headers && strpos($headers[0], '200') !== false) {
                                // Additional check for content-type if available
                                if (isset($headers['Content-Type'])) {
                                    $content_type = is_array($headers['Content-Type']) ? 
                                                  $headers['Content-Type'][0] : $headers['Content-Type'];
                                    if (strpos($content_type, 'image/') === 0) {
                                        return $image_url;
                                    }
                                } else {
                                    // If no content-type header, assume it's valid for LinkedIn media URLs
                                    if ($is_linkedin_media) {
                                        return $image_url;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("LinkedIn scraping exception: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Generate a professional placeholder avatar based on user's actual name
 * @param string $name The user's full name
 * @return string The placeholder avatar URL
 */
function generateProfessionalPlaceholder($name) {
    // Extract initials from the name
    $words = explode(' ', trim($name));
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    
    // Limit to 2-3 characters for better appearance
    if (strlen($initials) > 3) {
        $initials = substr($initials, 0, 3);
    }
    
    // Use LinkedIn's brand color for professional appearance
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=0077b5&color=fff&size=200&format=png&bold=true&font-size=0.5";
}

/**
 * Generate a professional placeholder avatar based on LinkedIn profile
 * @param string $username The LinkedIn username
 * @return string The placeholder avatar URL
 */
function generateLinkedInPlaceholder($username) {
    // Use a professional avatar service that generates avatars based on username
    // This creates consistent, professional-looking avatars
    $initials = strtoupper(substr($username, 0, 2));
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=0077b5&color=fff&size=200&format=png&bold=true&font-size=0.5";
}

/**
 * Get user's profile picture with LinkedIn integration
 * @param array $user_data User data from database
 * @return string The profile picture URL
 */
function getUserProfilePicture($user_data) {
    // Priority order:
    // 1. Uploaded avatar file
    // 2. LinkedIn profile picture (actual or professional placeholder)
    // 3. Default placeholder based on name
    
    if (!empty($user_data['avatar']) && file_exists($user_data['avatar'])) {
        return $user_data['avatar'];
    }
    
    // Get user's full name for better placeholders
    $name = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
    if (empty($name)) {
        $name = $user_data['name'] ?? $user_data['username'] ?? 'User';
    }
    
    // Try LinkedIn profile picture with name fallback
    $linkedin_url = $user_data['linkedin_profile'] ?? $user_data['user_linkedin'] ?? '';
    if (!empty($linkedin_url)) {
        $linkedin_pic = getLinkedInProfilePicture($linkedin_url, $name);
        if ($linkedin_pic) {
            return $linkedin_pic;
        }
    }
    
    // Generate default professional avatar based on name
    return generateProfessionalPlaceholder($name);
}

/**
 * Validate LinkedIn profile URL
 * @param string $url The URL to validate
 * @return bool True if valid LinkedIn URL
 */
function isValidLinkedInUrl($url) {
    if (empty($url)) {
        return true; // Optional field
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return preg_match('/linkedin\.com\/(in|pub)\/[a-zA-Z0-9\-]+/', $url);
}
?>
