<?php
/**
 * Telegram Bot Webhook Endpoint
 * Handles incoming updates from Telegram
 */

require_once 'bot.php';
require_once 'instagram.php';
require_once 'youtube.php';

// Set headers for webhook response
header('Content-Type: application/json');

try {
    // Get the incoming update
    $input = file_get_contents('php://input');
    $update = json_decode($input, true);
    
    if (!$update) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }
    
    // Log the update for debugging (remove in production)
    error_log('Telegram Update: ' . $input);
    
    // Process the update
    if (isset($update['message'])) {
        handleMessage($update['message']);
    } elseif (isset($update['callback_query'])) {
        handleCallbackQuery($update['callback_query']);
    }
    
    // Return success response
    echo json_encode(['status' => 'ok']);
    
} catch (Exception $e) {
    error_log('Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}

/**
 * Handle incoming text messages
 */
function handleMessage($message) {
    $text = $message['text'] ?? '';
    $chat_id = $message['chat']['id'];
    $name = $message['from']['first_name'] ?? 'User';
    $user = $message['from']['username'] ?? '';
    $message_id = $message['message_id'];
    $from_id = $message['from']['id'];
    
    // Handle /start command
    if ($text === '/start') {
        sendWelcomeMessage($chat_id, $name, $from_id);
        return;
    }
    
    // Check if the message is a YouTube URL
    if (!empty($text) && (strpos($text, 'youtube.com') !== false || strpos($text, 'youtu.be') !== false)) {
        handleYouTubeUrl($chat_id, $message_id, $text);
        return;
    }
    
    // Check if the message is an Instagram URL
    if (!empty($text) && (strpos($text, 'instagram.com') !== false || strpos($text, 'instagr.am') !== false)) {
        handleInstagramUrl($chat_id, $message_id, $text);
        return;
    }
    
    // Check if the message is a valid Instagram username
    if (!empty($text) && preg_match('/^@?[a-zA-Z0-9._]+$/', $text)) {
        $username = str_replace('@', '', trim($text));
        sendUsernameOptions($chat_id, $username, $message_id);
        return;
    }
    
    // Invalid input
    if (!empty($text) && $text !== '/start') {
        sendMessage($chat_id, "*❌ Please send:*\n• Instagram username (e.g., cristiano)\n• Instagram URL (post/reel/story)\n• YouTube URL (video/shorts)", 'Markdown');
    }
}

/**
 * Handle callback queries from inline keyboards
 */
function handleCallbackQuery($callback) {
    $data = $callback['data'];
    $chat_id = $callback['message']['chat']['id'];
    $message_id = $callback['message']['message_id'];
    $from_id = $callback['from']['id'];
    
    // Answer the callback query to remove loading state
    answerCallbackQuery($callback['id']);
    
    if (strpos($data, 'info_') === 0) {
        $username = substr($data, 5);
        handleProfileInfo($chat_id, $message_id, $username);
    } elseif (strpos($data, 'stories_') === 0) {
        $username = substr($data, 8);
        handleStoriesDownload($chat_id, $message_id, $username);
    } elseif (strpos($data, 'youtube_') === 0) {
        handleYouTubeCallback($chat_id, $message_id, $data);
    } elseif (strpos($data, 'copy_id_') === 0) {
        $video_id = substr($data, 8);
        answerCallbackQuery($callback['id'], "Video ID: $video_id", true);
    }
}

/**
 * Send welcome message with bot introduction
 */
function sendWelcomeMessage($chat_id, $name, $from_id) {
    $caption = "*👋 Welcome, [{$name}](tg://user?id={$from_id})

🔗 Instagram Downloader Bot

🚀 Features:
• Instagram profile info fetching
• Instagram stories download
• Instagram posts & reels download
• YouTube video download
• YouTube audio extraction
• Support for all major URLs

📌 How to use:
• Send Instagram username (e.g., cristiano)
• Send Instagram URL (posts/reels/stories)
• Send YouTube URL (videos/shorts)
• Use buttons for quick actions*";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '👑 Bot Owner', 'url' => 'https://t.me/Glllaxy'],
                ['text' => '🌐 Our Channel', 'url' => 'https://t.me/+oTUyrmFwQsE4YTdl']
            ]
        ]
    ];
    
    sendPhoto($chat_id, 'https://t.me/pyluck/539', $caption, 'Markdown', $keyboard);
}

/**
 * Send options for username processing
 */
function sendUsernameOptions($chat_id, $username, $reply_to_message_id) {
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '📄 Profile Info', 'callback_data' => "info_$username"],
                ['text' => '📸 Download Stories', 'callback_data' => "stories_$username"]
            ]
        ]
    ];
    
    sendMessage($chat_id, "*Select an option below 🔽*", 'Markdown', $keyboard, $reply_to_message_id);
}

/**
 * Handle profile information request
 */
function handleProfileInfo($chat_id, $message_id, $username) {
    // Update message to show loading
    editMessageText($chat_id, $message_id, "*Fetching profile info... 🔍*", 'Markdown');
    
    $info = getInstagramProfile($username);
    
    if ($info === false) {
        editMessageText($chat_id, $message_id, "*❌ Account not found or private.*", 'Markdown');
        return;
    }
    
    // Delete the loading message
    deleteMessage($chat_id, $message_id);
    
    // Format profile information
    $is_private = $info['is_private'] ? 'Yes' : 'No';
    $is_verified = $info['is_verified'] ? 'Yes' : 'No';
    $external_url = !empty($info['external_url']) ? $info['external_url'] : 'None';
    
    $caption = "*👤 Name: {$info['full_name']}
🔗 Username: @{$info['username']}
👥 Followers: " . number_format($info['follower_count']) . "
➡️ Following: " . number_format($info['following_count']) . "
🆔 User ID: {$info['id']}
🔒 Private: {$is_private}
✔️ Verified: {$is_verified}
🖼️ Posts: " . number_format($info['media_count']) . "
🌐 Website: {$external_url}
📌 Bio:
{$info['bio']}
📍 Country: {$info['country']}*";
    
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '🔗 Open Profile', 'url' => "https://instagram.com/{$info['username']}"]]
        ]
    ];
    
    sendPhoto($chat_id, $info['profile_pic_url'], $caption, 'Markdown', $keyboard);
}

/**
 * Handle stories download request
 */
function handleStoriesDownload($chat_id, $message_id, $username) {
    // Update message to show loading
    editMessageText($chat_id, $message_id, "*Downloading stories... 📥*", 'Markdown');
    
    $result = downloadInstagramStories($username);
    
    if ($result === false) {
        editMessageText($chat_id, $message_id, "*❌ No stories found or account is private.*", 'Markdown');
        return;
    }
    
    // Delete the loading message
    deleteMessage($chat_id, $message_id);
    
    // Send media using the new function
    sendInstagramMedia($chat_id, $result);
}

/**
 * Handle Instagram URL (posts, reels, stories)
 */
function handleInstagramUrl($chat_id, $message_id, $url) {
    // Send initial loading message
    sendMessage($chat_id, "*Processing Instagram URL... 🔍*", 'Markdown', null, $message_id);
    
    // Determine URL type and process accordingly
    if (strpos($url, '/stories/') !== false) {
        // Extract username from story URL
        preg_match('/instagram\.com\/stories\/([^\/\?]+)/', $url, $matches);
        if (isset($matches[1])) {
            $username = $matches[1];
            sendMessage($chat_id, "*Downloading story from @{$username}... 📥*", 'Markdown');
            $result = downloadInstagramStories($username);
        } else {
            // Direct story URL processing
            $result = downloadInstagramContent($url);
        }
    } else {
        // Regular post/reel
        sendMessage($chat_id, "*Downloading Instagram content... 📥*", 'Markdown');
        $result = downloadInstagramContent($url);
    }
    
    if ($result === false) {
        sendMessage($chat_id, "*❌ Failed to download content. The post might be private or deleted.*", 'Markdown');
        return;
    }
    
    // Send the downloaded media
    sendInstagramMedia($chat_id, $result);
}

/**
 * Send Instagram media (photos/videos) to chat
 */
function sendInstagramMedia($chat_id, $result) {
    if (!$result || !isset($result['links']) || empty($result['links'])) {
        sendMessage($chat_id, "*❌ No media found to download.*", 'Markdown');
        return;
    }
    
    $username = $result['username'] ?? 'unknown';
    $media_count = count($result['links']);
    
    // Prepare media for sending
    $media = [];
    foreach ($result['links'] as $link) {
        $media_type = getMediaTypeFromUrl($link);
        $media[] = [
            'type' => $media_type,
            'url' => $link
        ];
    }
    
    // Send caption with info
    $caption = "📱 Downloaded from @{$username}\n📊 Media count: {$media_count}\n🔗 Powered by Instagram Bot";
    
    // Send media based on count
    if (count($media) == 1) {
        $item = $media[0];
        if ($item['type'] == 'video') {
            sendVideo($chat_id, $item['url'], $caption, 'Markdown');
        } else {
            sendPhoto($chat_id, $item['url'], $caption, 'Markdown');
        }
    } else if (count($media) <= 10) {
        // Send as media group (Telegram limit: 10 items)
        $media_group = array_map(function ($item) {
            return ['type' => $item['type'], 'media' => $item['url']];
        }, array_slice($media, 0, 10));
        
        $media_group[0]['caption'] = $caption;
        sendMediaGroup($chat_id, $media_group);
        
        // If more than 10 items, send remaining individually
        if (count($media) > 10) {
            sendMessage($chat_id, "*📎 Sending remaining " . (count($media) - 10) . " files individually...*", 'Markdown');
            for ($i = 10; $i < count($media); $i++) {
                $item = $media[$i];
                if ($item['type'] == 'video') {
                    sendVideo($chat_id, $item['url']);
                } else {
                    sendPhoto($chat_id, $item['url']);
                }
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 seconds
            }
        }
    }
}

/**
 * Handle YouTube URL processing
 */
function handleYouTubeUrl($chat_id, $message_id, $url) {
    // Validate YouTube URL
    if (!isValidYouTubeUrl($url)) {
        sendMessage($chat_id, "*❌ Invalid YouTube URL. Please send a valid YouTube video link.*", 'Markdown', null, $message_id);
        return;
    }
    
    // Send processing message
    sendMessage($chat_id, "*🔍 Processing YouTube video...*", 'Markdown', null, $message_id);
    
    // Get video info first
    $video_info = getYouTubeVideoInfo($url);
    
    if ($video_info === false) {
        sendMessage($chat_id, "*❌ Failed to get video information. The video might be private, deleted, or restricted.*", 'Markdown');
        return;
    }
    
    // Send video information with download options
    sendYouTubeVideoInfo($chat_id, $video_info, $url);
}

/**
 * Send YouTube video information with download options
 */
function sendYouTubeVideoInfo($chat_id, $video_info, $original_url) {
    $title = $video_info['title'];
    $uploader = $video_info['uploader'];
    $duration = formatDuration($video_info['duration']);
    $views = formatViewCount($video_info['view_count']);
    $upload_date = $video_info['upload_date'];
    
    $caption = "*🎥 YouTube Video Info*\n\n";
    $caption .= "*📺 Title:* {$title}\n";
    $caption .= "*👤 Channel:* {$uploader}\n";
    $caption .= "*⏱️ Duration:* {$duration}\n";
    $caption .= "*👁️ Views:* {$views}\n";
    $caption .= "*📅 Upload Date:* {$upload_date}\n";
    
    // Create inline keyboard for download options
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🎬 Download Video', 'callback_data' => "youtube_video_" . $video_info['video_id']],
                ['text' => '🎵 Audio Only', 'callback_data' => "youtube_audio_" . $video_info['video_id']]
            ],
            [
                ['text' => '📋 Full Info', 'callback_data' => "youtube_info_" . $video_info['video_id']],
                ['text' => '🔗 Open in YouTube', 'url' => $original_url]
            ]
        ]
    ];
    
    // Send with thumbnail if available
    if (!empty($video_info['thumbnail'])) {
        sendPhoto($chat_id, $video_info['thumbnail'], $caption, 'Markdown', $keyboard);
    } else {
        sendMessage($chat_id, $caption, 'Markdown', $keyboard);
    }
}

/**
 * Handle YouTube download callback
 */
function handleYouTubeCallback($chat_id, $message_id, $callback_data) {
    $parts = explode('_', $callback_data, 3);
    $action = $parts[1]; // video, audio, or info
    $video_id = $parts[2];
    
    $youtube_url = "https://www.youtube.com/watch?v=" . $video_id;
    
    if ($action === 'video') {
        editMessageText($chat_id, $message_id, "*📥 Downloading video... This may take a moment.*", 'Markdown');
        downloadAndSendYouTubeVideo($chat_id, $message_id, $youtube_url, 'video');
    } elseif ($action === 'audio') {
        editMessageText($chat_id, $message_id, "*🎵 Extracting audio... This may take a moment.*", 'Markdown');
        downloadAndSendYouTubeVideo($chat_id, $message_id, $youtube_url, 'audio');
    } elseif ($action === 'info') {
        showDetailedYouTubeInfo($chat_id, $message_id, $youtube_url);
    }
}

/**
 * Download and send YouTube video/audio
 */
function downloadAndSendYouTubeVideo($chat_id, $message_id, $url, $type = 'video') {
    // For now, since reliable YouTube downloading requires complex setup,
    // we'll provide information and a helpful message
    
    $video_info = getYouTubeVideoInfo($url);
    
    if ($video_info === false) {
        editMessageText($chat_id, $message_id, "*❌ Unable to process this YouTube video.*", 'Markdown');
        return;
    }
    
    // Delete the loading message
    deleteMessage($chat_id, $message_id);
    
    $video_id = $video_info['video_id'];
    $title = $video_info['title'];
    $uploader = $video_info['uploader'];
    
    if ($type === 'audio') {
        $message = "*🎵 Audio Download Request*\n\n";
        $message .= "*📺 Title:* {$title}\n";
        $message .= "*👤 Channel:* {$uploader}\n\n";
        $message .= "*ℹ️ Note:* Direct YouTube downloads require additional setup. ";
        $message .= "You can use these alternatives:\n\n";
        $message .= "• Use online converters like y2mate.com\n";
        $message .= "• Try @SaveAsBot on Telegram\n";
        $message .= "• Use browser extensions for downloading\n\n";
        $message .= "*🔗 Original Video:* [Watch on YouTube](https://youtube.com/watch?v={$video_id})";
    } else {
        $message = "*🎬 Video Download Request*\n\n";
        $message .= "*📺 Title:* {$title}\n";
        $message .= "*👤 Channel:* {$uploader}\n\n";
        $message .= "*ℹ️ Note:* Direct YouTube downloads require additional setup. ";
        $message .= "You can use these alternatives:\n\n";
        $message .= "• Use online downloaders like ytmp3.cc\n";
        $message .= "• Try @SaveAsBot on Telegram\n";
        $message .= "• Use 4K Video Downloader app\n\n";
        $message .= "*🔗 Original Video:* [Watch on YouTube](https://youtube.com/watch?v={$video_id})";
    }
    
    // Create keyboard with helpful options
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🔗 Open in YouTube', 'url' => "https://youtube.com/watch?v={$video_id}"],
                ['text' => '📋 Copy Video ID', 'callback_data' => "copy_id_{$video_id}"]
            ],
            [
                ['text' => '🤖 Try @SaveAsBot', 'url' => 'https://t.me/SaveAsBot'],
                ['text' => '🌐 Online Downloader', 'url' => 'https://ytmp3.cc']
            ]
        ]
    ];
    
    // Send the thumbnail with the message
    if (!empty($video_info['thumbnail'])) {
        sendPhoto($chat_id, $video_info['thumbnail'], $message, 'Markdown', $keyboard);
    } else {
        sendMessage($chat_id, $message, 'Markdown', $keyboard);
    }
}

/**
 * Show detailed YouTube video information
 */
function showDetailedYouTubeInfo($chat_id, $message_id, $url) {
    $info = getYouTubeVideoInfo($url);
    
    if ($info === false) {
        editMessageText($chat_id, $message_id, "*❌ Failed to get detailed information.*", 'Markdown');
        return;
    }
    
    $detailed_info = "*🎥 Detailed Video Information*\n\n";
    $detailed_info .= "*📺 Title:* {$info['title']}\n";
    $detailed_info .= "*👤 Channel:* {$info['uploader']}\n";
    $detailed_info .= "*⏱️ Duration:* " . formatDuration($info['duration']) . "\n";
    $detailed_info .= "*👁️ Views:* " . formatViewCount($info['view_count']) . "\n";
    $detailed_info .= "*📅 Upload Date:* {$info['upload_date']}\n";
    $detailed_info .= "*🆔 Video ID:* {$info['video_id']}\n";
    
    if (!empty($info['description'])) {
        $description = strlen($info['description']) > 200 ? 
            substr($info['description'], 0, 200) . '...' : 
            $info['description'];
        $detailed_info .= "*📝 Description:* {$description}\n";
    }
    
    editMessageText($chat_id, $message_id, $detailed_info, 'Markdown');
}
?>
