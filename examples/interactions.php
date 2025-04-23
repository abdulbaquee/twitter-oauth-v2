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

// Get user profile with error handling
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
            case 'like_tweet':
                if (!empty($_POST['tweet_id'])) {
                    try {
                        $twitterApi->likeTweet($_POST['tweet_id']);
                        echo "<p class='success'>Tweet liked successfully!</p>";
                        // Clear cached interactions after action
                        $sessionHandler->remove('cached_interactions');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error liking tweet: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'unlike_tweet':
                if (!empty($_POST['tweet_id'])) {
                    try {
                        $twitterApi->unlikeTweet($_POST['tweet_id']);
                        echo "<p class='success'>Tweet unliked successfully!</p>";
                        // Clear cached interactions after action
                        $sessionHandler->remove('cached_interactions');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error unliking tweet: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'retweet':
                if (!empty($_POST['tweet_id'])) {
                    try {
                        $twitterApi->retweet($_POST['tweet_id']);
                        echo "<p class='success'>Tweet retweeted successfully!</p>";
                        // Clear cached interactions after action
                        $sessionHandler->remove('cached_interactions');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error retweeting: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'unretweet':
                if (!empty($_POST['tweet_id'])) {
                    try {
                        $twitterApi->unretweet($_POST['tweet_id']);
                        echo "<p class='success'>Retweet removed successfully!</p>";
                        // Clear cached interactions after action
                        $sessionHandler->remove('cached_interactions');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error removing retweet: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;
        }
    }
}

// Get user interactions with caching
$userInteractions = null;
$rateLimitError = false;

// Check cache first
if ($sessionHandler->has('cached_interactions') && $sessionHandler->has('interactions_cache_time')) {
    $cacheTime = $sessionHandler->get('interactions_cache_time');
    if (time() - $cacheTime < 300) { // Cache for 5 minutes
        $userInteractions = $sessionHandler->get('cached_interactions');
    }
}

// If not in cache or cache expired, fetch from API
if (!$userInteractions && $userProfile) {
    try {
        $userInteractions = $twitterApi->getUserInteractions($userProfile['data']['id']);
        // Cache the results
        $sessionHandler->set('cached_interactions', $userInteractions);
        $sessionHandler->set('interactions_cache_time', time());
    } catch (RuntimeException $e) {
        if (strpos($e->getMessage(), '429') !== false) {
            $rateLimitError = true;
            // Try to get cached data even if expired
            if ($sessionHandler->has('cached_interactions')) {
                $userInteractions = $sessionHandler->get('cached_interactions');
            }
        } else {
            $error = "Error fetching interactions: " . $e->getMessage();
        }
    }
}

// Clear any output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter Interactions</title>
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
        .interaction-buttons {
            margin-top: 10px;
        }
        .interaction-buttons button {
            margin-right: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .like-button { background-color: #e0245e; color: white; }
        .retweet-button { background-color: #17bf63; color: white; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="basic_usage.php">Basic Usage</a>
        <a href="users.php">User Profile & Relationships</a>
        <a href="lists.php">List Management</a>
        <a href="auth.php">OAuth Flow</a>
    </div>

    <h1>Twitter Interactions</h1>

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
        <h2>Recent Interactions</h2>
        <?php if ($userInteractions && isset($userInteractions['data'])): ?>
            <?php foreach ($userInteractions['data'] as $interaction): ?>
                <div class="tweet">
                    <p><?= htmlspecialchars($interaction['text']) ?></p>
                    <small><?= date('Y-m-d H:i:s', strtotime($interaction['created_at'])) ?></small>
                    <div class="interaction-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="like_tweet">
                            <input type="hidden" name="tweet_id" value="<?= $interaction['id'] ?>">
                            <button type="submit" class="like-button">Like</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="retweet">
                            <input type="hidden" name="tweet_id" value="<?= $interaction['id'] ?>">
                            <button type="submit" class="retweet-button">Retweet</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No interactions found or unable to fetch interactions at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html> 