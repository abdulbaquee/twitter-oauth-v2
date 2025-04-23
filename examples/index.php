<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Check if user is already authenticated
$isAuthenticated = $sessionHandler->has('twitter_tokens');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter OAuth 2.0 PHP Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1da1f2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
            margin: 5px;
        }
        .btn:hover {
            background: #1991db;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        .features {
            margin-top: 30px;
        }
        .features ul {
            list-style-type: none;
            padding: 0;
        }
        .features li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
        }
        .features li:before {
            content: "✓";
            color: #1da1f2;
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Twitter OAuth 2.0 PHP Library</h1>
        <p>A modern, robust PHP library for Twitter API v2 integration</p>
    </div>

    <div class="info">
        <p>This library provides a seamless way to integrate Twitter API v2 into your PHP applications using OAuth 2.0 with PKCE support. It's designed to be framework-agnostic, working with any PHP framework or vanilla PHP. The library handles all aspects of the OAuth flow, including authorization, token management, and API requests. It supports tweet creation, user management, and follows PSR standards for HTTP clients, logging, and caching. The implementation is secure, following OAuth 2.0 best practices and includes comprehensive error handling.</p>
    </div>

    <div class="features">
        <h2>Key Features:</h2>
        <ul>
            <li>OAuth 2.0 Authorization with PKCE support</li>
            <li>Token management (access tokens, refresh tokens)</li>
            <li>User management</li>
            <li>Tweet creation and management</li>
            <li>PSR standards compliance (PSR-4, PSR-7, PSR-18)</li>
            <li>Framework-agnostic session handling</li>
            <li>Comprehensive error handling and logging</li>
        </ul>
    </div>

    <div class="btn-container">
        <?php if ($isAuthenticated): ?>
            <a href="/success.php" class="btn">View Your Twitter Profile</a>
            <a href="/basic_usage.php" class="btn">View Basic Usage Examples</a>
            <a href="/tweet.php" class="btn">Create a Tweet</a>
        <?php else: ?>
            <a href="/auth.php" class="btn">Connect with Twitter</a>
        <?php endif; ?>
    </div>
</body>
</html> 