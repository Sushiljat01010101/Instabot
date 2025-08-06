<?php
/**
 * Instagram API Integration Functions
 * Handles fetching Instagram profile info and downloading stories
 */

/**
 * Get Instagram profile information
 */
function getInstagramProfile($username) {
    $api_url = "http://145.223.80.56:5091/instagram_info?username=" . urlencode($username);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        error_log("Failed to fetch Instagram profile for username: $username");
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON response for Instagram profile: $username");
        return false;
    }
    
    if (!isset($data['username'])) {
        error_log("Invalid profile data structure for username: $username");
        return false;
    }
    
    // Sanitize and validate required fields
    $profile = [
        'username' => $data['username'] ?? $username,
        'full_name' => $data['full_name'] ?? 'N/A',
        'follower_count' => intval($data['follower_count'] ?? 0),
        'following_count' => intval($data['following_count'] ?? 0),
        'id' => $data['id'] ?? 'N/A',
        'is_private' => boolval($data['is_private'] ?? false),
        'is_verified' => boolval($data['is_verified'] ?? false),
        'media_count' => intval($data['media_count'] ?? 0),
        'external_url' => $data['external_url'] ?? '',
        'bio' => $data['bio'] ?? 'No bio available',
        'country' => $data['country'] ?? 'Unknown',
        'profile_pic_url' => $data['profile_pic_url'] ?? ''
    ];
    
    return $profile;
}

/**
 * Download Instagram stories
 */
function downloadInstagramStories($username) {
    $stories_url = "https://www.instagram.com/stories/$username/";
    $api_url = "http://145.223.80.56:5085/download_instagram?url=" . urlencode($stories_url);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        error_log("Failed to fetch Instagram stories for username: $username");
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON response for Instagram stories: $username");
        return false;
    }
    
    if (!isset($data['download_links']) || empty($data['download_links'])) {
        error_log("No download links found for Instagram stories: $username");
        return false;
    }
    
    // Validate and filter download links
    $valid_links = [];
    foreach ($data['download_links'] as $link) {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $valid_links[] = $link;
        }
    }
    
    if (empty($valid_links)) {
        error_log("No valid download links found for Instagram stories: $username");
        return false;
    }
    
    return [
        'type' => $data['type'] ?? 'unknown',
        'links' => $valid_links,
        'username' => $data['username'] ?? $username,
        'count' => count($valid_links)
    ];
}

/**
 * Validate Instagram username format
 */
function isValidInstagramUsername($username) {
    // Remove @ symbol if present
    $username = ltrim($username, '@');
    
    // Check if username matches Instagram username pattern
    return preg_match('/^[a-zA-Z0-9._]{1,30}$/', $username);
}

/**
 * Sanitize Instagram username
 */
function sanitizeInstagramUsername($username) {
    // Remove @ symbol and trim whitespace
    $username = trim(ltrim($username, '@'));
    
    // Remove any invalid characters
    $username = preg_replace('/[^a-zA-Z0-9._]/', '', $username);
    
    // Limit length to Instagram's maximum
    return substr($username, 0, 30);
}

/**
 * Check if URL is a valid Instagram media URL
 */
function isValidInstagramMediaUrl($url) {
    $parsed = parse_url($url);
    
    if (!$parsed || !isset($parsed['host'])) {
        return false;
    }
    
    // Check if it's from Instagram's CDN or known media domains
    $valid_hosts = [
        'scontent.cdninstagram.com',
        'scontent-*.cdninstagram.com',
        'instagram.com',
        'www.instagram.com'
    ];
    
    foreach ($valid_hosts as $host) {
        if (fnmatch($host, $parsed['host'])) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get file extension from URL
 */
function getFileExtensionFromUrl($url) {
    $parsed = parse_url($url);
    $path = $parsed['path'] ?? '';
    
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

/**
 * Determine media type from URL
 */
function getMediaTypeFromUrl($url) {
    $extension = getFileExtensionFromUrl($url);
    
    $video_extensions = ['mp4', 'mov', 'avi', 'webm'];
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($extension, $video_extensions)) {
        return 'video';
    } elseif (in_array($extension, $image_extensions)) {
        return 'photo';
    }
    
    // Fallback: check if URL contains video indicators
    if (strpos($url, '.mp4') !== false || strpos($url, 'video') !== false) {
        return 'video';
    }
    
    return 'photo'; // Default to photo
}

/**
 * Download Instagram content from any URL (posts, reels, IGTV)
 */
function downloadInstagramContent($url) {
    $api_url = "http://145.223.80.56:5085/download_instagram?url=" . urlencode($url);
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        error_log("Failed to fetch Instagram content for URL: $url");
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON response for Instagram content: $url");
        return false;
    }
    
    if (!isset($data['download_links']) || empty($data['download_links'])) {
        error_log("No download links found for Instagram content: $url");
        return false;
    }
    
    // Validate and filter download links
    $valid_links = [];
    foreach ($data['download_links'] as $link) {
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $valid_links[] = $link;
        }
    }
    
    if (empty($valid_links)) {
        error_log("No valid download links found for Instagram content: $url");
        return false;
    }
    
    // Extract username from URL or API response
    $username = $data['username'] ?? null;
    if (!$username) {
        preg_match('/instagram\.com\/(?:p\/|reel\/|tv\/)?(?:.*\/)?(@?[a-zA-Z0-9._]+)/', $url, $matches);
        $username = isset($matches[1]) ? ltrim($matches[1], '@') : 'unknown';
    }
    
    return [
        'type' => $data['type'] ?? 'post',
        'links' => $valid_links,
        'username' => $username,
        'count' => count($valid_links),
        'title' => $data['title'] ?? '',
        'description' => $data['description'] ?? ''
    ];
}

/**
 * Extract Instagram username from various URL formats
 */
function extractUsernameFromInstagramUrl($url) {
    // Remove query parameters and fragments
    $clean_url = strtok($url, '?');
    $clean_url = strtok($clean_url, '#');
    
    // Different Instagram URL patterns
    $patterns = [
        '/instagram\.com\/stories\/([^\/]+)/',           // Stories
        '/instagram\.com\/([^\/]+)\/?$/',                // Profile
        '/instagram\.com\/p\/[^\/]+\/\?taken-by=([^&]+)/', // Old post format
        '/instagram\.com\/reel\/[^\/]+\/\?igshid=([^&]+)/' // Reel with username
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $clean_url, $matches)) {
            return isset($matches[1]) ? $matches[1] : null;
        }
    }
    
    return null;
}

/**
 * Validate Instagram URL format
 */
function isValidInstagramUrl($url) {
    $parsed = parse_url($url);
    
    if (!$parsed || !isset($parsed['host'])) {
        return false;
    }
    
    $valid_hosts = [
        'instagram.com',
        'www.instagram.com',
        'instagr.am',
        'www.instagr.am'
    ];
    
    return in_array(strtolower($parsed['host']), $valid_hosts);
}
?>
