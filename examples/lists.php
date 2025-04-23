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
            case 'create_list':
                if (!empty($_POST['list_name']) && !empty($_POST['list_description'])) {
                    try {
                        $twitterApi->createList($_POST['list_name'], $_POST['list_description']);
                        echo "<p class='success'>List created successfully!</p>";
                        // Clear cached lists after action
                        $sessionHandler->remove('cached_lists');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error creating list: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'delete_list':
                if (!empty($_POST['list_id'])) {
                    try {
                        $twitterApi->deleteList($_POST['list_id']);
                        echo "<p class='success'>List deleted successfully!</p>";
                        // Clear cached lists after action
                        $sessionHandler->remove('cached_lists');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error deleting list: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'add_member':
                if (!empty($_POST['list_id']) && !empty($_POST['user_id'])) {
                    try {
                        $twitterApi->addListMember($_POST['list_id'], $_POST['user_id']);
                        echo "<p class='success'>Member added successfully!</p>";
                        // Clear cached lists after action
                        $sessionHandler->remove('cached_lists');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error adding member: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;

            case 'remove_member':
                if (!empty($_POST['list_id']) && !empty($_POST['user_id'])) {
                    try {
                        $twitterApi->removeListMember($_POST['list_id'], $_POST['user_id']);
                        echo "<p class='success'>Member removed successfully!</p>";
                        // Clear cached lists after action
                        $sessionHandler->remove('cached_lists');
                    } catch (RuntimeException $e) {
                        echo "<p class='error'>Error removing member: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                break;
        }
    }
}

// Get user lists with caching
$userLists = null;
$rateLimitError = false;

// Check cache first
if ($sessionHandler->has('cached_lists') && $sessionHandler->has('lists_cache_time')) {
    $cacheTime = $sessionHandler->get('lists_cache_time');
    if (time() - $cacheTime < 300) { // Cache for 5 minutes
        $userLists = $sessionHandler->get('cached_lists');
    }
}

// If not in cache or cache expired, fetch from API
if (!$userLists && $userProfile) {
    try {
        $lists = $twitterApi->getLists($userProfile['data']['id']);
        // Cache the results
        $sessionHandler->set('cached_lists', $lists);
        $sessionHandler->set('lists_cache_time', time());
        $userLists = $lists;
    } catch (RuntimeException $e) {
        if (strpos($e->getMessage(), '429') !== false) {
            $rateLimitError = true;
            // Try to get cached data even if expired
            if ($sessionHandler->has('cached_lists')) {
                $userLists = $sessionHandler->get('cached_lists');
            }
        } else {
            $error = "Error fetching lists: " . $e->getMessage();
        }
    }
}

// Clear any output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Twitter Lists</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        .list { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
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
        .list-actions {
            margin-top: 10px;
        }
        .list-actions button {
            margin-right: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-button { background-color: #dc3545; color: white; }
        .add-member-button { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="basic_usage.php">Basic Usage</a>
        <a href="users.php">User Profile & Relationships</a>
        <a href="interactions.php">Tweet Interactions</a>
        <a href="auth.php">OAuth Flow</a>
    </div>

    <h1>Twitter Lists</h1>

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
        <h2>Create New List</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_list">
            <div class="form-group">
                <label for="list_name">List Name:</label>
                <input type="text" id="list_name" name="list_name" required>
            </div>
            <div class="form-group">
                <label for="list_description">Description:</label>
                <textarea id="list_description" name="list_description" required></textarea>
            </div>
            <button type="submit">Create List</button>
        </form>
    </div>

    <div class="section">
        <h2>Your Lists</h2>
        <?php if ($userLists && isset($userLists['data'])): ?>
            <?php foreach ($userLists['data'] as $list): ?>
                <div class="list">
                    <h3><?= htmlspecialchars($list['name']) ?></h3>
                    <p><?= htmlspecialchars($list['description']) ?></p>
                    <div class="list-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete_list">
                            <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                            <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this list?')">Delete List</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="add_member">
                            <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                            <input type="text" name="user_id" placeholder="User ID to add" required>
                            <button type="submit" class="add-member-button">Add Member</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No lists found or unable to fetch lists at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html> 