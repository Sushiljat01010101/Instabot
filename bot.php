<?php
/**
 * Telegram Bot API Functions
 * Handles all interactions with Telegram Bot API
 */

// Get bot token from environment variable
define("API_KEY", getenv('TELEGRAM_BOT_TOKEN') ?: 'YOUR_TOKEN_HERE');

/**
 * Make API call to Telegram Bot API
 */
function bot($method, $data = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/$method";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if (curl_error($ch)) {
        error_log('Telegram API Curl error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code !== 200 || !$result['ok']) {
        error_log('Telegram API Error: ' . $response);
        return false;
    }
    
    return $result;
}

/**
 * Send a text message
 */
function sendMessage($chat_id, $text, $parse_mode = null, $reply_markup = null, $reply_to_message_id = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];
    
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    if ($reply_to_message_id) {
        $data['reply_to_message_id'] = $reply_to_message_id;
    }
    
    return bot('sendMessage', $data);
}

/**
 * Send a photo
 */
function sendPhoto($chat_id, $photo, $caption = null, $parse_mode = null, $reply_markup = null) {
    $data = [
        'chat_id' => $chat_id,
        'photo' => $photo
    ];
    
    if ($caption) {
        $data['caption'] = $caption;
    }
    
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    $data['disable_web_page_preview'] = true;
    
    return bot('sendPhoto', $data);
}

/**
 * Send a video
 */
function sendVideo($chat_id, $video, $caption = null, $parse_mode = null, $reply_markup = null) {
    $data = [
        'chat_id' => $chat_id,
        'video' => $video
    ];
    
    if ($caption) {
        $data['caption'] = $caption;
    }
    
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    return bot('sendVideo', $data);
}

/**
 * Send media group (multiple photos/videos)
 */
function sendMediaGroup($chat_id, $media) {
    $data = [
        'chat_id' => $chat_id,
        'media' => json_encode($media)
    ];
    
    return bot('sendMediaGroup', $data);
}

/**
 * Edit message text
 */
function editMessageText($chat_id, $message_id, $text, $parse_mode = null, $reply_markup = null) {
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text
    ];
    
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    return bot('editMessageText', $data);
}

/**
 * Delete a message
 */
function deleteMessage($chat_id, $message_id) {
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id
    ];
    
    return bot('deleteMessage', $data);
}

/**
 * Answer callback query
 */
function answerCallbackQuery($callback_query_id, $text = null, $show_alert = false) {
    $data = [
        'callback_query_id' => $callback_query_id
    ];
    
    if ($text) {
        $data['text'] = $text;
    }
    
    if ($show_alert) {
        $data['show_alert'] = true;
    }
    
    return bot('answerCallbackQuery', $data);
}

/**
 * Set webhook URL
 */
function setWebhook($url) {
    $data = [
        'url' => $url
    ];
    
    return bot('setWebhook', $data);
}

/**
 * Get webhook info
 */
function getWebhookInfo() {
    return bot('getWebhookInfo');
}

/**
 * Get bot information
 */
function getMe() {
    return bot('getMe');
}
?>
