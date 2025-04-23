<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Check if tokens exist
if (!$sessionHandler->has('twitter_tokens')) {
    die('No authentication tokens found. Please try authenticating again.');
}

// Get tokens from session
$tokens = $sessionHandler->get('twitter_tokens');

// Display success message and tokens
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter OAuth Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .success {
            color: green;
            margin-bottom: 20px;
        }
        .tokens {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            word-break: break-all;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1da1f2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #1991db;
        }
        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1 class="success">Authentication Successful!</h1>
    <p>Your Twitter OAuth tokens have been stored in the session.</p>
    
    <h2>Token Information:</h2>
    <div class="tokens">
        <p><strong>Access Token:</strong> <?php echo htmlspecialchars($tokens['access_token']); ?></p>
        <?php if (isset($tokens['refresh_token'])): ?>
            <p><strong>Refresh Token:</strong> <?php echo htmlspecialchars($tokens['refresh_token']); ?></p>
        <?php endif; ?>
        <p><strong>Token Type:</strong> <?php echo htmlspecialchars($tokens['token_type']); ?></p>
        <p><strong>Expires In:</strong> <?php echo htmlspecialchars($tokens['expires_in']); ?> seconds</p>
    </div>
    
    <div class="btn-group">
        <a href="/tweet.php" class="btn">Create a Tweet</a>
        <a href="/basic_usage.php" class="btn">View Basic Usage Examples</a>
        <a href="/" class="btn">Return to Home</a>
    </div>
</body>
</html> 