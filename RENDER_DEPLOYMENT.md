# Render Deployment Guide - Instagram Telegram Bot

## 🚀 Quick Render Deployment

### Step 1: Prepare Files (✅ Already Done)
- ✅ `Dockerfile` created
- ✅ `render.yaml` created  
- ✅ `.dockerignore` created

### Step 2: Render Account Setup
1. **Create Render account**: https://render.com
2. **Connect GitHub**: Link your GitHub repository
3. **Push code**: Upload project to GitHub repository

### Step 3: Deploy on Render

#### Option A: Auto Deploy (Recommended)
1. **Connect Repository**: 
   - Go to Render Dashboard
   - Click "New +" → "Web Service"
   - Connect your GitHub repo

2. **Auto Configuration**:
   ```
   Build Command: (Auto detected from Dockerfile)
   Start Command: (Auto detected from Dockerfile)
   Port: Auto detected
   ```

#### Option B: Manual Configuration
```yaml
# render.yaml settings:
Name: instagram-telegram-bot
Environment: Docker
Build Command: docker build -t bot .
Start Command: apache2-foreground
Port: 80 (Auto-detected from PORT env var)
```

### Step 4: Environment Variables
**Add in Render Dashboard:**
```
TELEGRAM_BOT_TOKEN = your_bot_token_here
PORT = 80 (Render auto-sets this)
```

### Step 5: Custom Domain (Optional)
```
Default URL: https://your-app-name.onrender.com
Custom Domain: your-domain.com (requires upgrade)
```

---

## 📋 Detailed Steps

### A. GitHub Repository Setup
```bash
# If you don't have GitHub repo yet:
1. Go to github.com
2. Create new repository
3. Upload all project files
4. Include: Dockerfile, render.yaml, all PHP files
```

### B. Render Service Creation
1. **Login to Render**
2. **New Web Service**
3. **Connect Repository**
4. **Configure Settings:**
   ```
   Name: instagram-telegram-bot
   Branch: main
   Root Directory: . (current directory)
   Environment: Docker
   ```

### C. Environment Variables Setup
```
In Render Dashboard → Environment:
TELEGRAM_BOT_TOKEN = 1234567890:ABCdefGhIjKlMnOpQrStUvWxYz
```

### D. Webhook Configuration
After deployment, update webhook URL:
```
New Webhook URL: https://your-app-name.onrender.com/webhook.php

Update using:
https://api.telegram.org/bot{TOKEN}/setWebhook?url=https://your-app-name.onrender.com/webhook.php
```

---

## 🔧 Troubleshooting

### Common Issues:

#### 1. Dockerfile Error (✅ Fixed)
```
Error: no such file or directory
Solution: Dockerfile created ✅
```

#### 2. Port Configuration
```
Error: Port binding failed
Solution: Render auto-sets PORT environment variable
Apache configured to use ${PORT}
```

#### 3. PHP Extensions Missing
```
Error: Call to undefined function curl_init()
Solution: Dockerfile includes curl extension installation
```

#### 4. File Permissions
```
Error: Permission denied
Solution: Dockerfile sets proper permissions (755)
```

### Build Logs Check:
```
Render Dashboard → Your Service → Logs
Look for:
- ✅ Docker build successful
- ✅ Apache started
- ✅ Port listening
```

---

## 📊 Deployment Verification

### Test Checklist:
1. **Service Status**: ✅ Running
2. **URL Access**: ✅ https://your-app.onrender.com
3. **Webhook**: ✅ Telegram bot responds
4. **Instagram**: ✅ Profile info works
5. **YouTube**: ✅ Download works

### Health Check:
```
GET https://your-app.onrender.com/
Response: Bot interface loads properly
```

---

## ⚡ Performance Tips

### Render Free Tier:
- **Limitation**: Sleeps after 15 minutes of inactivity
- **Solution**: Use paid plan for 24/7 availability

### Keep Alive (Optional):
```php
// Add to webhook.php for activity
if (isset($_GET['keepalive'])) {
    echo "Bot is alive!";
    exit;
}
```

### Monitoring:
```
Render provides:
- Deployment logs
- Runtime logs  
- Performance metrics
- Automatic SSL
```

---

## 🎯 Success Checklist

- [ ] GitHub repository created
- [ ] Dockerfile working
- [ ] Render service deployed
- [ ] Environment variables set
- [ ] Webhook URL updated
- [ ] Bot responding in Telegram
- [ ] Instagram features working
- [ ] YouTube features working

**🎉 Your bot is now live on Render!**

### Important URLs:
```
Bot Interface: https://your-app-name.onrender.com
Webhook: https://your-app-name.onrender.com/webhook.php
Render Dashboard: https://dashboard.render.com
```