# Telegram Bot Webhook Setup Guide

## Step 1: Apna Bot Token Prapt Karein

1. Telegram mein @BotFather ko message karein
2. `/newbot` command bhejein
3. Bot ka naam aur username choose karein
4. Aapko bot token milega (example: `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`)

## Step 2: Environment Variable Set Karein

**Replit mein:**
1. Left sidebar mein "Secrets" tab par click karein
2. "New Secret" button par click karein
3. Key: `TELEGRAM_BOT_TOKEN`
4. Value: Apna bot token paste karein
5. "Add Secret" par click karein

## Step 3: Webhook URL Set Karein

**Apka webhook URL:**
```
https://[YOUR-REPLIT-APP-NAME].replit.app/webhook.php
```

**Method 1: Setup Script Use Karein**
1. Browser mein jaiye: `https://[YOUR-REPLIT-APP-NAME].replit.app/setup_webhook.php`
2. Script automatically webhook set kar dega

**Method 2: Manual Setup (Telegram API)**
Browser mein yeh URL kholeein:
```
https://api.telegram.org/bot[YOUR_BOT_TOKEN]/setWebhook?url=https://[YOUR-REPLIT-APP-NAME].replit.app/webhook.php
```

**Method 3: cURL Command**
```bash
curl -X POST "https://api.telegram.org/bot[YOUR_BOT_TOKEN]/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url": "https://[YOUR-REPLIT-APP-NAME].replit.app/webhook.php"}'
```

## Step 4: Test Karein

1. Apne bot ko Telegram mein find karein
2. `/start` command bhejein
3. Koi Instagram username bhejein (jaise: `cristiano` ya `@virat.kohli`)
4. Profile info ya stories download karke dekhe

## Troubleshooting

**Agar webhook set nahi ho raha:**
- Check karein ki bot token sahi hai
- Environment variable properly set hai ya nahi
- Replit app publicly accessible hai

**Agar bot respond nahi kar raha:**
- Webhook URL check karein
- Server logs dekhe error ke liye
- Bot token valid hai ya nahi verify karein

## Important Notes

- Webhook URL https:// se start hona chahiye
- Replit automatically SSL certificate provide karta hai
- Har bar code change karne ke baad webhook reset karne ki zarurat nahi
- Bot token ko secret rakhein, publicly share na karein