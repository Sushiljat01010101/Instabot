<?php
/**
 * Simple web interface for Telegram Bot setup
 * Displays bot status and webhook information
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram Telegram Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fab fa-telegram"></i> Instagram Telegram Bot</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle text-info"></i> Bot Features</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-user text-primary"></i> Instagram Profile Info</li>
                                    <li><i class="fas fa-images text-success"></i> Instagram Stories</li>
                                    <li><i class="fas fa-video text-danger"></i> Posts & Reels</li>
                                    <li><i class="fab fa-youtube text-danger"></i> YouTube Videos</li>
                                    <li><i class="fas fa-music text-info"></i> Audio Extraction</li>
                                    <li><i class="fas fa-shield-alt text-warning"></i> Smart URL Detection</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-cog text-secondary"></i> Bot Status</h5>
                                <div class="alert alert-info">
                                    <strong>Status:</strong> 
                                    <?php
                                    $api_key = getenv('TELEGRAM_BOT_TOKEN') ?: 'Not configured';
                                    if ($api_key !== 'Not configured') {
                                        echo '<span class="text-success">Active</span>';
                                    } else {
                                        echo '<span class="text-danger">Token Required</span>';
                                    }
                                    ?>
                                </div>
                                <div class="alert alert-secondary">
                                    <strong>Webhook URL:</strong><br>
                                    <code><?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/webhook.php'; ?></code>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <h5><i class="fas fa-robot text-primary"></i> Setup Instructions</h5>
                            <ol class="text-start">
                                <li>@BotFather se bot token hasil karein</li>
                                <li>Environment variable mein <code>TELEGRAM_BOT_TOKEN</code> set karein</li>
                                <li>Webhook URL set karein (automatic setup available)</li>
                                <li>Bot ko test karein <code>/start</code> command se</li>
                            </ol>
                            <div class="mt-3">
                                <a href="/setup_webhook.php" class="btn btn-success me-2">
                                    <i class="fas fa-cog"></i> Auto Setup Webhook
                                </a>
                                <a href="/webhook_instructions.md" target="_blank" class="btn btn-info">
                                    <i class="fas fa-book"></i> Detailed Instructions
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="https://core.telegram.org/bots/api#setwebhook" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> Telegram Bot API Documentation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
