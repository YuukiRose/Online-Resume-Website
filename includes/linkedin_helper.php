<?php
/**
 * Enhanced LinkedIn Profile Picture Helper Functions
 * 
 * Note: LinkedIn actively blocks automated scraping for privacy and security.
 * This system prioritizes user-uploaded profile pictures and generates
 * professional placeholders based on actual names instead of usernames.
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
 * Generate LinkedIn profile picture URL with realistic expectations
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
    // and professional placeholders. Scraping attempts are minimal and expected to fail.
    
    // Method 1: Attempt scraping (likely to fail, but worth trying)
    $profile_pic = attemptLinkedInScraping($linkedin_url);
    if ($profile_pic) {
        return $profile_pic;
    }
    
    // Method 2: Use user's actual name for professional placeholder
    if (!empty($user_name)) {
        return generateProfessionalPlaceholder($user_name);
    }
    
    // Method 3: Fallback to username-based placeholder (avoid this if possible)
    return generateLinkedInPlaceholder($username);
}

/**
 * Minimal LinkedIn scraping attempt with realistic expectations
 * @param string $linkedin_url The LinkedIn profile URL
 * @return string|null The profile picture URL or null
 */
function attemptLinkedInScraping($linkedin_url) {
    // Only attempt if cURL is available
    if (!function_exists('curl_init')) {
        return null;
    }
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $linkedin_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Short timeout
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Only proceed if we get a successful response
        if ($http_code === 200 && $html) {
            // Look for Open Graph image (most reliable if available)
            if (preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $matches)) {
                $image_url = html_entity_decode($matches[1]);
                if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    return $image_url;
                }
            }
            
            // Look for media.licdn.com URLs
            if (preg_match('/src="(https:\/\/media\.licdn\.com\/[^"]+)"/i', $html, $matches)) {
                $image_url = html_entity_decode($matches[1]);
                if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    return $image_url;
                }
            }
        }
        
    } catch (Exception $e) {
        // Silently fail - this is expected
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
 * Generate a professional placeholder avatar based on LinkedIn username (fallback)
 * @param string $username The LinkedIn username
 * @return string The placeholder avatar URL
 */
function generateLinkedInPlaceholder($username) {
    // Extract meaningful initials from username
    $cleaned = preg_replace('/[^a-zA-Z]/', ' ', $username);
    $words = explode(' ', trim($cleaned));
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word) && strlen($initials) < 2) {
            $initials .= strtoupper($word[0]);
        }
    }
    
    // If we can't get good initials, use first 2 characters
    if (strlen($initials) < 2) {
        $initials = strtoupper(substr($username, 0, 2));
    }
    
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=0077b5&color=fff&size=200&format=png&bold=true&font-size=0.5";
}

/**
 * Get user's profile picture with improved priority system
 * @param array $user_data User data from database
 * @return string The profile picture URL
 */
function getUserProfilePicture($user_data) {
    // Priority order:
    // 1. Uploaded avatar file (highest priority)
    // 2. LinkedIn profile picture (attempted scraping)
    // 3. Professional placeholder based on name
    
    // Check for uploaded avatar first
    if (!empty($user_data['avatar'])) {
        $avatar_path = $user_data['avatar'];
        // Handle both absolute and relative paths
        if (strpos($avatar_path, 'http') === 0) {
            return $avatar_path;
        } else {
            $full_path = $_SERVER['DOCUMENT_ROOT'] . '/Luthor/' . $avatar_path;
            if (file_exists($full_path)) {
                return $avatar_path; // Return relative path for web use
            }
        }
    }
    
    // Get user's full name for professional placeholders
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
    
    // Generate professional placeholder based on name
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

/**
 * Generate a message explaining profile picture options
 * @param array $user_data User data
 * @return string HTML message for user
 */
function getProfilePictureMessage($user_data) {
    if (!empty($user_data['avatar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/Luthor/' . $user_data['avatar'])) {
        return "<div class='profile-pic-info success'>✅ Using your uploaded profile picture</div>";
    }
    
    $linkedin_url = $user_data['linkedin_profile'] ?? '';
    if (!empty($linkedin_url)) {
        return "<div class='profile-pic-info info'>🔗 Using LinkedIn profile. Upload a custom picture above for better control.</div>";
    }
    
    $name = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
    return "<div class='profile-pic-info'>📷 Upload a profile picture or add your LinkedIn URL for a professional avatar</div>";
}
?>
