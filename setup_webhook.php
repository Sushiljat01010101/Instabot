<?php
/**
 * Webhook Setup Script
 * Use this script to set up the webhook for your Telegram bot
 */

require_once 'bot.php';

// Check if bot token is configured
if (API_KEY === '7826143063:AAGnVepdsZdJ359mJKg8ebL4rdK2VsoV_Tk') {
    die("❌ Please set your TELEGRAM_BOT_TOKEN environment variable first!\n");
}

// Get the webhook URL (replace with your actual domain)
$webhook_url = 'https://instabot-knx1.onrender.com/webhook.php';

// If running locally, you might use ngrok or similar
if (isset($_GET['local'])) {
    $webhook_url = 'https://your-ngrok-url.ngrok.io/webhook.php';
}

// Set the webhook
echo "Setting webhook to: $webhook_url\n";
$result = setWebhook($webhook_url);

if ($result && $result['ok']) {
    echo "✅ Webhook set successfully!\n";
    echo "Description: " . ($result['result']['description'] ?? 'Success') . "\n";
} else {
    echo "❌ Failed to set webhook\n";
    if ($result) {
        echo "Error: " . ($result['description'] ?? 'Unknown error') . "\n";
    }
}

// Get webhook info to verify
echo "\n--- Webhook Information ---\n";
$info = getWebhookInfo();
if ($info && $info['ok']) {
    $webhook_info = $info['result'];
    echo "URL: " . ($webhook_info['url'] ?? 'Not set') . "\n";
    echo "Has custom certificate: " . ($webhook_info['has_custom_certificate'] ? 'Yes' : 'No') . "\n";
    echo "Pending update count: " . ($webhook_info['pending_update_count'] ?? 0) . "\n";
    echo "Last error date: " . ($webhook_info['last_error_date'] ?? 'None') . "\n";
    echo "Last error message: " . ($webhook_info['last_error_message'] ?? 'None') . "\n";
}

// Get bot info
echo "\n--- Bot Information ---\n";
$bot_info = getMe();
if ($bot_info && $bot_info['ok']) {
    $bot = $bot_info['result'];
    echo "Bot username: @" . $bot['username'] . "\n";
    echo "Bot name: " . $bot['first_name'] . "\n";
    echo "Bot ID: " . $bot['id'] . "\n";
}
?>
