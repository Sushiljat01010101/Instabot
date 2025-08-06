# Instagram Bot

## Overview

This is a PHP-based Telegram bot that provides comprehensive Instagram integration functionality. The bot allows users to retrieve Instagram profile information and download Instagram content (photos, videos, stories, posts, reels) through Telegram chat interface. It acts as a bridge between Telegram users and Instagram content, utilizing external APIs for Instagram data scraping and content downloading.

## Recent Changes (August 6, 2025)

### Instagram Features
- **Enhanced URL Support**: Added direct Instagram URL processing for posts, reels, and stories
- **Story Downloader Feature**: Implemented automatic story detection and downloading from URLs
- **Improved Media Handling**: Enhanced media group sending with better error handling
- **URL Validation**: Added comprehensive URL validation and username extraction

### YouTube Integration (Latest)
- **YouTube Video Download**: Full YouTube video downloading with quality selection
- **Audio Extraction**: YouTube to MP3/audio conversion feature
- **Interactive UI**: Thumbnail preview with download options (Video/Audio/Info)
- **Smart Detection**: Automatic YouTube URL detection and validation
- **Multiple Formats**: Support for youtube.com, youtu.be, shorts, and mobile URLs
- **Video Information**: Display title, channel, duration, views, and upload date

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Bot Framework
- **Platform**: Telegram Bot API integration using PHP
- **Communication**: Webhook-based message handling with Telegram servers
- **HTTP Client**: cURL for making API requests to Telegram Bot API

### Core Functionality
- **Profile Information**: Retrieves Instagram user profile data including follower counts, bio, and account details
- **Content Download**: Downloads Instagram posts, stories, and media content via direct links
- **Media Processing**: Handles multiple media types (images, videos) with batch download support

### API Integration Pattern
- **External Service Dependencies**: Relies on third-party Instagram scraping APIs hosted on dedicated servers
- **Timeout Handling**: Implements 30-second timeouts for external API calls to prevent hanging requests
- **Error Resilience**: Includes error handling for network failures and malformed responses

### Data Flow
- **Input Processing**: Receives webhook data from Telegram as JSON payload
- **Command Parsing**: Extracts user messages and chat identifiers from incoming updates  
- **Response Generation**: Formats and sends responses back through Telegram Bot API

## External Dependencies

### Telegram Integration
- **Telegram Bot API**: Primary interface for receiving messages and sending responses
- **Authentication**: Requires bot token for API access (placeholder: 'YOUR_TOKEN_HERE')

### Instagram APIs
- **Profile API**: `http://145.223.80.56:5091/instagram_info` - Retrieves user profile information
- **Download API**: `http://145.223.80.56:5085/download_instagram` - Provides media download links
- **Data Format**: JSON responses with structured Instagram content metadata

### Infrastructure
- **Web Server**: PHP runtime environment with webhook support
- **Network**: HTTP/HTTPS capabilities for external API communication
- **JSON Processing**: Built-in PHP JSON handling for data parsing and generation