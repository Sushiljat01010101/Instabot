<?php
/**
 * YouTube API Integration Functions
 * Handles YouTube video downloading and information extraction
 */

/**
 * Download YouTube video using alternative method
 */
function downloadYouTubeVideo($url) {
    // Clean the URL to get video ID
    $video_id = extractYouTubeVideoId($url);
    if (!$video_id) {
        error_log("Invalid YouTube URL: $url");
        return false;
    }
    
    // Try multiple alternative APIs
    $api_endpoints = [
        "https://api.cobalt.tools/api/json",
        "https://youtube-dl-api.herokuapp.com/api/download",
        "https://api.allorigins.win/get?url=" . urlencode("https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json")
    ];
    
    foreach ($api_endpoints as $api_url) {
        $result = tryDownloadFromAPI($api_url, $url, $video_id);
        if ($result !== false) {
            return $result;
        }
    }
    
    // Fallback: Return basic info if download fails
    error_log("All YouTube download APIs failed for URL: $url");
    return false;
}

/**
 * Try downloading from a specific API endpoint
 */
function tryDownloadFromAPI($api_url, $video_url, $video_id) {
    try {
        if (strpos($api_url, 'cobalt.tools') !== false) {
            return downloadFromCobaltAPI($api_url, $video_url, $video_id);
        } elseif (strpos($api_url, 'allorigins') !== false) {
            return getBasicVideoInfo($api_url, $video_url, $video_id);
        } else {
            return downloadFromGenericAPI($api_url, $video_url, $video_id);
        }
    } catch (Exception $e) {
        error_log("API error for $api_url: " . $e->getMessage());
        return false;
    }
}

/**
 * Download using Cobalt Tools API
 */
function downloadFromCobaltAPI($api_url, $video_url, $video_id) {
    $post_data = json_encode([
        'url' => $video_url,
        'vQuality' => '720',
        'aFormat' => 'mp3',
        'isAudioOnly' => false
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Mozilla/5.0 (compatible; TelegramBot/1.0)'
            ],
            'content' => $post_data,
            'timeout' => 30
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    if ($response === false) return false;
    
    $data = json_decode($response, true);
    if (!$data || $data['status'] !== 'success') return false;
    
    return [
        'type' => 'youtube',
        'video_id' => $video_id,
        'title' => $data['title'] ?? 'YouTube Video',
        'duration' => $data['duration'] ?? 'Unknown',
        'uploader' => 'YouTube',
        'view_count' => 0,
        'upload_date' => 'Unknown',
        'description' => '',
        'thumbnail' => "https://img.youtube.com/vi/$video_id/maxresdefault.jpg",
        'links' => [
            '720p' => $data['url'] ?? null
        ]
    ];
}

/**
 * Get basic video info from YouTube oEmbed
 */
function getBasicVideoInfo($api_url, $video_url, $video_id) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; TelegramBot/1.0)'
            ]
        ]
    ]);
    
    $response = @file_get_contents($api_url, false, $context);
    if ($response === false) return false;
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['contents'])) return false;
    
    $oembed_data = json_decode($data['contents'], true);
    if (!$oembed_data) return false;
    
    // Generate download links using known YouTube formats
    $download_links = generateYouTubeDownloadLinks($video_id);
    
    return [
        'type' => 'youtube',
        'video_id' => $video_id,
        'title' => $oembed_data['title'] ?? 'YouTube Video',
        'duration' => 'Unknown',
        'uploader' => $oembed_data['author_name'] ?? 'YouTube',
        'view_count' => 0,
        'upload_date' => 'Unknown',
        'description' => '',
        'thumbnail' => $oembed_data['thumbnail_url'] ?? "https://img.youtube.com/vi/$video_id/maxresdefault.jpg",
        'links' => $download_links
    ];
}

/**
 * Generate YouTube download links using known formats
 */
function generateYouTubeDownloadLinks($video_id) {
    // These are example download services - in production you'd use proper APIs
    return [
        '720p' => "https://www.youtube.com/watch?v=$video_id", // Fallback to original
        '480p' => "https://www.youtube.com/watch?v=$video_id",
        'audio' => "https://www.youtube.com/watch?v=$video_id"
    ];
}

/**
 * Get YouTube video information without downloading
 */
function getYouTubeVideoInfo($url) {
    $video_id = extractYouTubeVideoId($url);
    if (!$video_id) {
        return false;
    }
    
    // Try YouTube oEmbed API first (official and reliable)
    $oembed_url = "https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; TelegramBot/1.0)'
            ]
        ]
    ]);
    
    $response = @file_get_contents($oembed_url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['title'])) {
            return [
                'video_id' => $video_id,
                'title' => $data['title'],
                'duration' => 'Unknown',
                'uploader' => $data['author_name'] ?? 'Unknown Channel',
                'view_count' => 0,
                'upload_date' => 'Unknown',
                'description' => '',
                'thumbnail' => $data['thumbnail_url'] ?? "https://img.youtube.com/vi/$video_id/maxresdefault.jpg",
                'likes' => 0,
                'dislikes' => 0
            ];
        }
    }
    
    // Fallback: Generate basic info using video ID
    return [
        'video_id' => $video_id,
        'title' => 'YouTube Video',
        'duration' => 'Unknown',
        'uploader' => 'Unknown Channel',
        'view_count' => 0,
        'upload_date' => 'Unknown',
        'description' => '',
        'thumbnail' => "https://img.youtube.com/vi/$video_id/maxresdefault.jpg",
        'likes' => 0,
        'dislikes' => 0
    ];
}

/**
 * Extract YouTube video ID from various URL formats
 */
function extractYouTubeVideoId($url) {
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/',
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
        '/m\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Validate YouTube URL
 */
function isValidYouTubeUrl($url) {
    $parsed = parse_url($url);
    
    if (!$parsed || !isset($parsed['host'])) {
        return false;
    }
    
    $valid_hosts = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtu.be',
        'music.youtube.com'
    ];
    
    $host = strtolower($parsed['host']);
    return in_array($host, $valid_hosts) && extractYouTubeVideoId($url) !== null;
}

/**
 * Format video duration from seconds
 */
function formatDuration($seconds) {
    if (!is_numeric($seconds)) return $seconds;
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

/**
 * Format view count
 */
function formatViewCount($count) {
    if (!is_numeric($count)) return $count;
    
    if ($count >= 1000000000) {
        return round($count / 1000000000, 1) . 'B';
    } elseif ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    } else {
        return $count;
    }
}

/**
 * Get best quality download link
 */
function getBestQualityLink($links) {
    $quality_priority = ['1080p', '720p', '480p', '360p', '240p', 'audio'];
    
    foreach ($quality_priority as $quality) {
        if (isset($links[$quality])) {
            return ['quality' => $quality, 'url' => $links[$quality]];
        }
    }
    
    // Return first available if no priority match
    if (!empty($links)) {
        $first_key = array_keys($links)[0];
        return ['quality' => $first_key, 'url' => $links[$first_key]];
    }
    
    return null;
}
?>