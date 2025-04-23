<?php
// Ensure no output is sent before headers
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\OAuthClient;
use Abdulbaquee\TwitterOAuth\TwitterClient;
use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Check if we have tokens
if (!$sessionHandler->has('twitter_tokens')) {
    ob_end_clean();
    header('Location: /auth.php');
    exit;
}

// Get tokens from session
$tokens = $sessionHandler->get('twitter_tokens');

// Initialize Twitter client
$twitterClient = new TwitterClient($tokens['access_token']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    $mediaPath = $_FILES['media']['tmp_name'] ?? null;
    $mediaType = $_FILES['media']['type'] ?? null;

    try {
        if ($mediaPath && $mediaType) {
            // Upload media and create tweet with media
            $mediaId = $twitterClient->uploadMedia($mediaPath, $mediaType);
            $tweet = $twitterClient->createTweet($text, [$mediaId]);
        } else {
            // Create text-only tweet
            $tweet = $twitterClient->createTweet($text);
        }
        $success = true;
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

// Clear any output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Tweet</title>
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
        .error {
            color: red;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        input[type="file"] {
            margin-top: 10px;
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
    <h1>Create a Tweet</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">
            <p>Tweet posted successfully!</p>
            <p>Tweet ID: <?php echo htmlspecialchars($tweet['data']['id']); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <p>Error: <?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <textarea name="text" rows="4" placeholder="What's happening?" required maxlength="280"></textarea>
        </div>
        
        <div class="form-group">
            <label for="media">Media (optional):</label>
            <input type="file" name="media" id="media" accept="image/*">
        </div>
        
        <button type="submit" class="btn">Tweet</button>
    </form>
    
    <div class="btn-group">
        <a href="/success.php" class="btn">Back to Success Page</a>
        <a href="/" class="btn">Return to Home</a>
    </div>
</body>
</html> 