# Twitter OAuth 2.0 PHP Library

A modern, robust PHP library for integrating Twitter API v2 using OAuth 2.0 with PKCE support. This library is framework-agnostic and works seamlessly with core PHP.

## Features

- OAuth 2.0 Authorization with PKCE support
- Token management (access tokens, refresh tokens)
- User management
- Tweet creation and management
- List management
- Tweet interactions (like, retweet, reply)
- User interactions (follow, block, mute)
- PSR standards compliance (PSR-4, PSR-7, PSR-18)
- Framework-agnostic session handling

> **Note**: Media uploads (images, GIFs, videos) are not yet supported in Twitter API v2. This feature will be added once Twitter releases the media upload endpoints for v2.

## Requirements

- PHP 7.4 or higher
- Composer
- Twitter API credentials (Client ID and Client Secret)

## Installation

```bash
composer require wg/twitter-oauth-v2
```

## Configuration

1. Copy the example configuration file:
```bash
cp examples/config.example.php examples/config.php
```

2. Edit `config.php` and fill in your Twitter API credentials:
```php
return [
    'twitter' => [
        'client_id' => 'YOUR_CLIENT_ID',
        'client_secret' => 'YOUR_CLIENT_SECRET',
        'redirect_uri' => 'YOUR_REDIRECT_URI',
        'scopes' => [
            'tweet.read',
            'tweet.write',
            'users.read',
            'follows.write',
            'list.read',
            'list.write',
            'offline.access',
            'media.write'
        ]
    ],
    'session' => [
        'lifetime' => 7200,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
        'samesite' => 'lax'
    ]
];
```

## Session Handling

The library provides a flexible session handling system that works with any PHP framework. Choose the appropriate session handler for your framework:

### Vanilla PHP
```php
use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

$sessionHandler = new NativeSessionHandler();
```

### Laravel
```php
use Abdulbaquee\TwitterOAuth\Session\LaravelSessionHandler;
use Illuminate\Session\Store;

$sessionHandler = new LaravelSessionHandler(app('session.store'));
```

### Symfony
```php
use Abdulbaquee\TwitterOAuth\Session\SymfonySessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;

$sessionHandler = new SymfonySessionHandler($session);
```

### Custom Implementation
You can create your own session handler by implementing the `SessionHandlerInterface`:

```php
use Abdulbaquee\TwitterOAuth\Session\SessionHandlerInterface;

class CustomSessionHandler implements SessionHandlerInterface
{
    // Implement the required methods
}
```

## PHP Configuration Requirements (Optimal)

For optimal performance and security, we recommend the following PHP settings:

```ini
; Session Settings
session.save_handler = files
session.save_path = "/path/to/session/directory"
session.gc_maxlifetime = 7200
session.cookie_secure = true
session.cookie_httponly = true
session.cookie_samesite = "Lax"

; Memory and Execution Settings
memory_limit = 128M
max_execution_time = 30
max_input_time = 60

; Error Reporting (Development)
error_reporting = E_ALL
display_errors = On
display_startup_errors = On

; Error Reporting (Production)
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
```

Note: These are recommended settings. Adjust them according to your specific environment and security requirements.

## Usage

### Basic Setup

```php
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

// Get authorization URL
$authUrl = $oauthClient->getAuthorizationUrl();
// Redirect user to $authUrl

// After user authorization, exchange code for tokens
$tokens = $oauthClient->getAccessToken($_GET['code']);

// Initialize Twitter API client
$twitterApi = new TwitterApi($tokens['access_token']);
```

### User Operations

```php
// Get user profile
$userProfile = $twitterApi->getUserProfile();

// Get user tweets
$userTweets = $twitterApi->getUserTweets($userId);

// Get user followers
$followers = $twitterApi->getUserFollowers($userId);

// Get user following
$following = $twitterApi->getUserFollowing($userId);

// Get user mentions
$mentions = $twitterApi->getUserMentions($userId);

// Get user likes
$likes = $twitterApi->getUserLikes($userId);
```

### Tweet Operations

```php
// Create a tweet
$tweet = $twitterApi->createTweet('Hello, Twitter!');

// Create a tweet with media
$mediaId = $twitterApi->uploadMedia('/path/to/image.jpg');
$tweet = $twitterApi->createTweet('Check out this image!', [$mediaId]);

// Delete a tweet
$twitterApi->deleteTweet($tweetId);

// Like a tweet
$twitterApi->likeTweet($userId, $tweetId);

// Unlike a tweet
$twitterApi->unlikeTweet($userId, $tweetId);

// Retweet
$twitterApi->retweet($userId, $tweetId);

// Remove retweet
$twitterApi->removeRetweet($userId, $tweetId);

// Reply to tweet
$twitterApi->replyToTweet('This is a reply!', $tweetId);
```

### List Operations

```php
// Create a list
$list = $twitterApi->createList('My List', 'Description', false);

// Get user lists
$lists = $twitterApi->getUserLists($userId);

// Get list members
$members = $twitterApi->getListMembers($listId);

// Get list tweets
$tweets = $twitterApi->getListTweets($listId);

// Add member to list
$twitterApi->addListMember($listId, $userId);

// Remove member from list
$twitterApi->removeListMember($listId, $userId);

// Delete list
$twitterApi->deleteList($listId);
```

### User Interactions

```php
// Follow user
$twitterApi->followUser($sourceUserId, $targetUserId);

// Unfollow user
$twitterApi->unfollowUser($sourceUserId, $targetUserId);

// Block user
$twitterApi->blockUser($sourceUserId, $targetUserId);

// Unblock user
$twitterApi->unblockUser($sourceUserId, $targetUserId);

// Mute user
$twitterApi->muteUser($sourceUserId, $targetUserId);

// Unmute user
$twitterApi->unmuteUser($sourceUserId, $targetUserId);
```

## Example Pages

The library includes several example pages to demonstrate its functionality:

1. `auth.php` - OAuth 2.0 authorization flow
2. `callback.php` - OAuth callback handling
3. `users.php` - User profile and relationship operations
4. `tweet.php` - Tweet creation and management
5. `lists.php` - List creation and management
6. `interactions.php` - Tweet interactions (like, retweet, reply)
7. `basic_usage.php` - Basic usage examples

## Error Handling

The library throws exceptions for various error conditions:

- `RuntimeException` for general errors
- `GuzzleException` for HTTP request failures

## Base Example Class

The library provides a `BaseExample` class that serves as a foundation for implementing Twitter API operations. This class handles common functionality like authentication and request handling.

### Features

- **HTTP Method Support**: Built-in methods for GET, POST, and DELETE requests
- **Authentication**: Automatic token management and refresh
- **Error Handling**: Comprehensive error handling
- **Common Operations**: Pre-built methods for common Twitter API operations

### Usage Example

```php
use Abdulbaquee\TwitterOAuth\Examples\BaseExample;
use Abdulbaquee\TwitterOAuth\OAuthClient;

$client = new OAuthClient(
    'your_client_id',
    'your_client_secret',
    'your_redirect_uri',
    ['tweet.read', 'tweet.write', 'users.read']
);

// Create example instance
$example = new BaseExample($client);

// Get user information
$userInfo = $example->getUserInfo();

// Create a tweet
$tweet = $example->createTweet('Hello, Twitter!');

// Upload media
$media = $example->uploadMedia('/path/to/image.jpg', 'image/jpeg');

// Delete a tweet
$example->deleteTweet('tweet_id');
```

### Extending the Base Class

You can extend the `BaseExample` class to add custom functionality:

```php
class CustomExample extends BaseExample
{
    public function getTweetsByUser(string $userId): array
    {
        return $this->get("https://api.twitter.com/2/users/{$userId}/tweets", [
            'max_results' => 100,
            'tweet.fields' => 'created_at,public_metrics'
        ]);
    }
}
```

### Error Handling

The base class includes comprehensive error handling:

- API errors are properly formatted
- Network errors are caught and handled
- JSON parsing errors are handled gracefully

### Media Upload

The media upload functionality supports:

- Image files (JPG, PNG, GIF)
- Video files (MP4)
- Proper MIME type handling
- Automatic file size validation

Note: Media upload functionality is subject to Twitter API v2 limitations and requirements.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This library is licensed under the MIT License. 