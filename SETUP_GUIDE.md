# Instagram Telegram Bot - Complete Setup Guide

## 🚀 Quick Start (5 Minutes Setup)

### Step 1: Create Telegram Bot
1. **Telegram mein @BotFather ko message karein**
2. `/newbot` command send karein
3. Bot ka naam enter karein (example: "Instagram Downloader Bot")
4. Bot ka username enter karein (example: "my_insta_bot")
5. **Bot Token copy kar lein** (example: `1234567890:ABCdefGhIjKlMnOpQrStUvWxYz`)

### Step 2: Configure Bot Token
1. **Replit Secrets mein token add karein:**
   - Secret Name: `TELEGRAM_BOT_TOKEN`
   - Secret Value: Aapka bot token (Step 1 se)

### Step 3: Setup Webhook (Automatic)
1. **Webhook URL set karein:**
   ```
   https://your-repl-name.your-username.repl.co/webhook.php
   ```
2. **Auto setup ke liye:** `/setup_webhook.php` page visit karein

### Step 4: Test Your Bot
1. Telegram mein apne bot ko message karein
2. `/start` command send karein
3. Instagram username ya URL send karein

---

## 📋 Detailed Setup Instructions

### A. Prerequisites
- Replit account
- Telegram account
- Internet connection

### B. Bot Creation Process

#### 1. BotFather Setup
```
Telegram mein steps:
1. @BotFather search karein
2. /start command send karein
3. /newbot command send karein
4. Bot name enter karein: "My Instagram Bot"
5. Bot username enter karein: "my_instagram_downloader_bot"
6. Token save kar lein
```

#### 2. Environment Variables
```
Replit Secrets mein add karein:
- TELEGRAM_BOT_TOKEN = your_bot_token_here
```

#### 3. Webhook Configuration
**Option A: Manual Setup**
```bash
curl -X POST \
  "https://api.telegram.org/bot{YOUR_TOKEN}/setWebhook" \
  -d "url=https://your-repl-name.your-username.repl.co/webhook.php"
```

**Option B: Automatic Setup**
- Visit: `https://your-repl-name.your-username.repl.co/setup_webhook.php`
- Click "Setup Webhook" button

### C. Bot Commands & Features

#### Available Commands:
- `/start` - Bot start karne ke liye
- `/help` - Help menu
- Instagram username - Profile info get karne ke liye
- Instagram URL - Content download karne ke liye
- YouTube URL - Video/Audio download karne ke liye

#### Supported URLs:
```
Instagram:
- https://instagram.com/username
- https://www.instagram.com/p/POST_ID/
- https://www.instagram.com/stories/username/STORY_ID/
- https://www.instagram.com/reel/REEL_ID/

YouTube:
- https://youtube.com/watch?v=VIDEO_ID
- https://youtu.be/VIDEO_ID
- https://youtube.com/shorts/VIDEO_ID
```

### D. Testing & Troubleshooting

#### Test Checklist:
1. ✅ Bot responds to `/start`
2. ✅ Instagram profile info working
3. ✅ Instagram URL download working
4. ✅ YouTube download working
5. ✅ Error messages showing properly

#### Common Issues:

**Problem: Bot not responding**
```
Solution:
1. Check bot token in Secrets
2. Verify webhook URL
3. Check server logs
```

**Problem: Instagram download not working**
```
Solution:
1. Check internet connection
2. Verify Instagram URL format
3. Try different Instagram account
```

**Problem: YouTube download failed**
```
Solution:
1. Check YouTube URL format
2. Verify video is public
3. Try shorter videos first
```

### E. Advanced Configuration

#### Custom Messages:
Edit `webhook.php` file to customize bot responses:

```php
// Welcome message
$welcome_text = "🤖 Welcome to Instagram Bot!\n\nSend me:\n📱 Instagram username\n🔗 Instagram/YouTube URLs";

// Error messages
$error_private = "❌ This account is private";
$error_notfound = "❌ Account not found";
```

#### Rate Limiting:
Add rate limiting to prevent spam:

```php
// In webhook.php
$last_request = time();
if ($last_request - $previous_request < 2) {
    sendMessage($chat_id, "⏳ Please wait 2 seconds between requests");
    exit;
}
```

### F. Deployment Options

#### Option 1: Keep Always Running
- Replit mein "Always On" enable karein
- Bot 24/7 available rahega

#### Option 2: Manual Restart
- Server restart karne ke liye `php -S 0.0.0.0:5000` run karein

#### Option 3: Auto Deploy (Recommended)
- Replit Deployments use karein
- Production-ready hosting
- Custom domain support

---

## 🔧 File Structure

```
├── index.php          # Web interface
├── webhook.php        # Main bot logic
├── bot.php           # Telegram API functions
├── instagram.php     # Instagram scraping
├── youtube.php       # YouTube downloading
├── setup_webhook.php # Webhook configuration
└── replit.md         # Project documentation
```

## 📞 Support

**Common Commands:**
- Bot restart: Replit mein "Run" button press karein
- Logs check: Console tab dekhen
- Webhook test: `/setup_webhook.php` visit karein

**Need Help?**
1. Check error logs in Console
2. Verify all secrets are set
3. Test webhook URL manually
4. Restart the Repl

---

## ✅ Success Checklist

- [ ] Bot token configured
- [ ] Webhook URL set
- [ ] Bot responds to messages
- [ ] Instagram features working
- [ ] YouTube features working
- [ ] Error handling working
- [ ] Ready for production

**🎉 Your Instagram Telegram Bot is ready to use!**