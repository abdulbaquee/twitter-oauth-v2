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

// Ensure we have a valid Bearer Token
if (!isset($tokens['access_token']) || $tokens['token_type'] !== 'Bearer') {
    // Clear invalid tokens and redirect to auth
    $sessionHandler->remove('twitter_oauth_tokens');
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
            case 'follow_user':
                if (!empty($_POST['user_id'])) {
                    try {
                        $twitterApi->followUser($userProfile['data']['id'], $_POST['user_id']);
                        echo "<p class='success'>User followed successfully!</p>";
                        // Clear cached relationships after action
                        $sessionHandler->remove('cached_relationships');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error following user: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'unfollow_user':
                if (!empty($_POST['user_id'])) {
                    try {
                        $twitterApi->unfollowUser($userProfile['data']['id'], $_POST['user_id']);
                        echo "<p class='success'>User unfollowed successfully!</p>";
                        // Clear cached relationships after action
                        $sessionHandler->remove('cached_relationships');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error unfollowing user: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'block_user':
                if (!empty($_POST['user_id'])) {
                    try {
                        $twitterApi->blockUser($userProfile['data']['id'], $_POST['user_id']);
                        echo "<p class='success'>User blocked successfully!</p>";
                        // Clear cached relationships after action
                        $sessionHandler->remove('cached_relationships');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error blocking user: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'unblock_user':
                if (!empty($_POST['user_id'])) {
                    try {
                        $twitterApi->unblockUser($userProfile['data']['id'], $_POST['user_id']);
                        echo "<p class='success'>User unblocked successfully!</p>";
                        // Clear cached relationships after action
                        $sessionHandler->remove('cached_relationships');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error unblocking user: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;
        }
    }
}

// Get user relationships with caching
$userRelationships = null;
$rateLimitError = false;

// Check cache first
if ($sessionHandler->has('cached_relationships') && $sessionHandler->has('relationships_cache_time')) {
    $cacheTime = $sessionHandler->get('relationships_cache_time');
    if (time() - $cacheTime < 300) { // Cache for 5 minutes
        $userRelationships = $sessionHandler->get('cached_relationships');
    }
}

// If not in cache or cache expired, fetch from API
if (!$userRelationships && $userProfile) {
    try {
        $userRelationships = $twitterApi->getUserRelationships($userProfile['data']['id']);
        // Cache the results
        $sessionHandler->set('cached_relationships', $userRelationships);
        $sessionHandler->set('relationships_cache_time', time());
    } catch (RuntimeException $e) {
        if (strpos($e->getMessage(), '429') !== false) {
            $rateLimitError = true;
            // Try to get cached data even if expired
            if ($sessionHandler->has('cached_relationships')) {
                $userRelationships = $sessionHandler->get('cached_relationships');
            }
        } else {
            $error = "Error fetching relationships: " . $e->getMessage();
        }
    }
}

// Clear any output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter User Relationships</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        .user { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
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
        .user-actions {
            margin-top: 10px;
        }
        .user-actions button {
            margin-right: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .follow-button { background-color: #1DA1F2; color: white; }
        .block-button { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="basic_usage.php">Basic Usage</a>
        <a href="lists.php">List Management</a>
        <a href="interactions.php">Tweet Interactions</a>
        <a href="auth.php">OAuth Flow</a>
    </div>

    <h1>Twitter User Relationships</h1>

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
            <h2>Your Profile</h2>
            <div class="user">
                <img src="<?= htmlspecialchars($userProfile['data']['profile_image_url']) ?>" alt="Profile Image">
                <h3><?= htmlspecialchars($userProfile['data']['name']) ?></h3>
                <p>@<?= htmlspecialchars($userProfile['data']['username']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Manage Relationships</h2>
        <form method="POST">
            <div class="form-group">
                <label for="user_id">User ID:</label>
                <input type="text" id="user_id" name="user_id" required>
            </div>
            <div class="form-group">
                <button type="submit" name="action" value="follow_user" class="follow-button">Follow User</button>
                <button type="submit" name="action" value="unfollow_user" class="follow-button">Unfollow User</button>
                <button type="submit" name="action" value="block_user" class="block-button">Block User</button>
                <button type="submit" name="action" value="unblock_user" class="block-button">Unblock User</button>
            </div>
        </form>
    </div>

    <div class="section">
        <h2>Your Relationships</h2>
        <?php if ($userRelationships && isset($userRelationships['data'])): ?>
            <?php foreach ($userRelationships['data'] as $relationship): ?>
                <div class="user">
                    <img src="<?= htmlspecialchars($relationship['profile_image_url']) ?>" alt="Profile Image">
                    <h3><?= htmlspecialchars($relationship['name']) ?></h3>
                    <p>@<?= htmlspecialchars($relationship['username']) ?></p>
                    <div class="user-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="follow_user">
                            <input type="hidden" name="user_id" value="<?= $relationship['id'] ?>">
                            <button type="submit" class="follow-button">Follow</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="block_user">
                            <input type="hidden" name="user_id" value="<?= $relationship['id'] ?>">
                            <button type="submit" class="block-button">Block</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No relationships found or unable to fetch relationships at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html> 