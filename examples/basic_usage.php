<?php
// Ensure no output is sent before headers
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\OAuthClient;
use Abdulbaquee\TwitterOAuth\TwitterApi;
use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Initialize OAuth client
$oauthClient = new OAuthClient(
    $config['twitter']['client_id'],
    $config['twitter']['client_secret'],
    $config['twitter']['redirect_uri'],
    $config['twitter']['scopes'],
    null,
    $sessionHandler
);

// Get stored tokens
$tokens = $oauthClient->getStoredTokens();
if (!$tokens) {
    header('Location: auth.php');
    exit;
}

// Initialize Twitter API client
$twitterApi = new TwitterApi($tokens['access_token']);

// Get user profile
try {
    $userProfile = $twitterApi->getUserProfile();
} catch (RuntimeException $e) {
    $error = "Error fetching user profile: " . $e->getMessage();
    $userProfile = null;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_tweet':
                if (!empty($_POST['tweet_text'])) {
                    try {
                        $tweet = $twitterApi->createTweet($_POST['tweet_text']);
                        echo "<p class='success'>Tweet created successfully!</p>";
                        // Clear cached tweets after creating a new one
                        $sessionHandler->remove('cached_tweets');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error creating tweet: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'delete_tweet':
                if (!empty($_POST['tweet_id'])) {
                    try {
                        $twitterApi->deleteTweet($_POST['tweet_id']);
                        echo "<p class='success'>Tweet deleted successfully!</p>";
                        // Clear cached tweets after deletion
                        $sessionHandler->remove('cached_tweets');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error deleting tweet: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;
        }
    }
}

// Get user tweets with caching
$userTweets = null;
$rateLimitError = false;

// Check cache first
if ($sessionHandler->has('cached_tweets') && $sessionHandler->has('tweets_cache_time')) {
    $cacheTime = $sessionHandler->get('tweets_cache_time');
    if (time() - $cacheTime < 300) { // Cache for 5 minutes
        $userTweets = $sessionHandler->get('cached_tweets');
    }
}

// If not in cache or cache expired, fetch from API
if (!$userTweets && $userProfile) {
    try {
        $userTweets = $twitterApi->getUserTweets($userProfile['data']['id']);
        // Cache the results
        $sessionHandler->set('cached_tweets', $userTweets);
        $sessionHandler->set('tweets_cache_time', time());
    } catch (RuntimeException $e) {
        if (strpos($e->getMessage(), '429') !== false) {
            $rateLimitError = true;
            // Try to get cached data even if expired
            if ($sessionHandler->has('cached_tweets')) {
                $userTweets = $sessionHandler->get('cached_tweets');
            }
        } else {
            $error = "Error fetching tweets: " . $e->getMessage();
        }
    }
}

// Clear any output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter Basic Usage</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        .tweet { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .form-group { margin-bottom: 10px; }
        .navigation { margin-bottom: 20px; }
        .navigation a { 
            display: inline-block;
            padding: 8px 16px;
            margin-right: 10px;
            background-color: #1DA1F2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .navigation a:hover {
            background-color: #1991db;
        }
        .error {
            color: #dc3545;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .rate-limit-notice {
            color: #856404;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="users.php">User Profile & Relationships</a>
        <a href="lists.php">List Management</a>
        <a href="interactions.php">Tweet Interactions</a>
        <a href="auth.php">OAuth Flow</a>
    </div>

    <h1>Twitter Basic Usage</h1>

    <?php if (isset($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($rateLimitError): ?>
        <div class="rate-limit-notice">
            <strong>Rate Limit Notice:</strong> Twitter API rate limit reached. Showing cached data. Please try again in a few minutes.
        </div>
    <?php endif; ?>

    <?php if ($userProfile): ?>
        <div class="section">
            <h2>User Profile</h2>
            <div class="user">
                <img src="<?= htmlspecialchars($userProfile['data']['profile_image_url']) ?>" alt="Profile Image">
                <h3><?= htmlspecialchars($userProfile['data']['name']) ?></h3>
                <p>@<?= htmlspecialchars($userProfile['data']['username']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Create Tweet</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_tweet">
            <div class="form-group">
                <label for="tweet_text">Tweet Text:</label>
                <textarea id="tweet_text" name="tweet_text" required rows="4" cols="50"></textarea>
            </div>
            <button type="submit">Create Tweet</button>
        </form>
    </div>

    <div class="section">
        <h2>Recent Tweets</h2>
        <?php if ($userTweets && isset($userTweets['data'])): ?>
            <?php foreach ($userTweets['data'] as $tweet): ?>
                <div class="tweet">
                    <p><?= htmlspecialchars($tweet['text']) ?></p>
                    <small><?= date('Y-m-d H:i:s', strtotime($tweet['created_at'])) ?></small>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_tweet">
                        <input type="hidden" name="tweet_id" value="<?= $tweet['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this tweet?')">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tweets found or unable to fetch tweets at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html>