# TwitterOAuthV2

The most popular PHP library for Twitter's OAuth 2.0 REST API v2.

## Requirements

- PHP 7.4 or higher
- [Composer](https://getcomposer.org/)
- cURL extension
- JSON extension

## Installation

```bash
composer require abdulbaquee/twitteroauth-v2
```

## Authentication

TwitterOAuthV2 supports two authentication methods:

1. **Bearer Token (App-Only) Authentication** - For accessing public data without user context
2. **OAuth 2.0 Authorization Code Flow with PKCE** - For accessing user data with specific permissions

### Bearer Token Authentication

```php
use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;

// Initialize with your credentials
$twitter = new TwitterOAuthV2('your_client_id', 'your_client_secret');

// Get a bearer token
$bearerToken = $twitter->getBearerToken();

// Use the API
$api = new TwitterAPI($twitter);
$response = $api->searchTweets('Twitter API v2');
```

### OAuth 2.0 Authorization Code Flow with PKCE

```php
// Start a session to store OAuth state and code verifier
session_start();

// Initialize with your credentials and redirect URI
$twitter = new TwitterOAuthV2(
    'your_client_id',
    'your_client_secret',
    'your_redirect_uri'
);

// Step 1: Generate authorization URL and redirect user
if (!isset($_GET['code'])) {
    $authUrl = $twitter->getAuthorizationUrl([
        'scope' => 'tweet.read users.read tweet.write'
    ]);
    header('Location: ' . $authUrl);
    exit;
}

// Step 2: Exchange authorization code for access token
$token = $twitter->getAccessToken($_GET['code']);

// Use the API
$api = new TwitterAPI($twitter);
$response = $api->getMe();
```

## Making API Requests

The library provides a `TwitterAPI` class with methods for common Twitter API v2 endpoints:

```php
// Get a user by username
$response = $api->getUserByUsername('twitterdev');

// Get a tweet
$response = $api->getTweet('1234567890123456789');

// Post a tweet
$response = $api->postTweet('Hello, Twitter API v2!');

// Search for tweets
$response = $api->searchTweets('Twitter API', [
    'max_results' => 10,
    'tweet.fields' => 'created_at,author_id,public_metrics'
]);

// Get a user's followers
$response = $api->getFollowers('user_id');

// Like a tweet
$response = $api->likeTweet('user_id', 'tweet_id');
```

## Response Handling

All API methods return a `Response` object that provides access to the HTTP status code, headers, and response body:

```php
$response = $api->getUserByUsername('twitterdev');

// Check if request was successful
if ($response->isSuccess()) {
    // Get decoded response body
    $data = $response->getDecodedBody();
    
    // Access user data
    $user = $data['data'][0];
    echo "Username: @{$user['username']}\n";
    
    // Get rate limit information
    $rateLimit = $response->getRateLimitInfo();
    echo "Rate limit remaining: {$rateLimit['remaining']}\n";
} else {
    echo "Error: HTTP {$response->getHttpCode()}\n";
}
```

## Error Handling

The library throws `TwitterOAuthException` for errors:

```php
try {
    $response = $api->getUserByUsername('nonexistent_user');
} catch (TwitterOAuthException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "HTTP Status: {$e->getHttpCode()}\n";
    
    // Get error data from Twitter API
    $errorData = $e->getErrorData();
    if ($errorData) {
        echo "Error code: {$errorData['errors'][0]['code']}\n";
        echo "Error message: {$errorData['errors'][0]['message']}\n";
    }
}
```

## Examples

See the `examples` directory for complete examples:

- `app_only_auth.php` - Bearer Token (App-Only) authentication example
- `oauth_flow.php` - OAuth 2.0 Authorization Code Flow with PKCE example
- `api_requests.php` - Various API request examples
- `media_upload.php` - Media upload example with Twitter API v2
- `tweet_posting.php` - Posting tweets with various options
- `user_timeline.php` - Getting user timeline data
- `search_tweets.php` - Searching tweets with pagination
- `user_management.php` - Follow/unfollow users and get followers/following
- `tweet_interactions.php` - Like/retweet tweets and get interaction data
- `list_management.php` - Create and manage Twitter lists
- `direct_messages.php` - Send and receive direct messages
- `streaming.php` - Connect to Twitter streaming endpoints

## Media Upload

Twitter API v2 uses the v1.1 endpoint for media uploads. Here's how to upload media:

```php
// Initialize media upload
$initResponse = $twitter->post('media/upload', [
    'command' => 'INIT',
    'media_type' => 'image/jpeg',
    'total_bytes' => filesize($imagePath)
]);

$mediaId = $initResponse->getDecodedBody()['media_id_string'];

// Upload media chunks
$mediaData = file_get_contents($imagePath);
$chunkSize = 1024 * 1024; // 1MB chunks
$segmentIndex = 0;

for ($offset = 0; $offset < strlen($mediaData); $offset += $chunkSize) {
    $chunk = substr($mediaData, $offset, $chunkSize);
    
    $twitter->post('media/upload', [
        'command' => 'APPEND',
        'media_id' => $mediaId,
        'segment_index' => $segmentIndex,
        'media' => base64_encode($chunk)
    ]);
    
    $segmentIndex++;
}

// Finalize media upload
$twitter->post('media/upload', [
    'command' => 'FINALIZE',
    'media_id' => $mediaId
]);

// Post a tweet with the uploaded media
$api->postTweet('Check out this image!', [
    'media' => [
        'media_ids' => [$mediaId]
    ]
]);
```

## API Reference

### TwitterOAuthV2 Class

The main class for authentication and API requests.

#### Constructor

```php
__construct(
    string $clientId,
    ?string $clientSecret = null,
    ?string $redirectUri = null,
    ?string $bearerToken = null,
    ?OAuthToken $oauthToken = null
)
```

#### Authentication Methods

```php
// Bearer Token (App-Only) Authentication
getBearerToken(): BearerToken
setBearerToken($token): self

// OAuth 2.0 Authorization Code Flow with PKCE
getAuthorizationUrl(array $options = []): string
getAccessToken(string $code, ?string $codeVerifier = null, ?string $state = null): OAuthToken
refreshAccessToken(?string $refreshToken = null): OAuthToken
setOAuthToken($token): self
```

#### API Request Methods

```php
get(string $path, array $parameters = []): Response
post(string $path, array $parameters = []): Response
put(string $path, array $parameters = []): Response
delete(string $path, array $parameters = []): Response
```

### TwitterAPI Class

A wrapper class with methods for common Twitter API v2 endpoints.

```php
// User methods
getUserByUsername(string $username, array $fields = []): Response
getUserById(string $id, array $fields = []): Response
getMe(array $fields = []): Response
getFollowers(string $userId, array $options = []): Response
getFollowing(string $userId, array $options = []): Response
followUser(string $userId, string $targetUserId): Response
unfollowUser(string $userId, string $targetUserId): Response

// Tweet methods
getTweet(string $id, array $fields = []): Response
getTweets(array $ids, array $fields = []): Response
searchTweets(string $query, array $options = []): Response
getUserTimeline(string $userId, array $options = []): Response
postTweet(string $text, array $options = []): Response
deleteTweet(string $id): Response
getTweetCounts(string $query, array $options = []): Response

// Like methods
likeTweet(string $userId, string $tweetId): Response
unlikeTweet(string $userId, string $tweetId): Response
getLikedTweets(string $userId, array $options = []): Response
getLikingUsers(string $tweetId, array $options = []): Response

// Retweet methods
retweet(string $userId, string $tweetId): Response
unretweet(string $userId, string $tweetId): Response
getRetweetedBy(string $tweetId, array $options = []): Response

// List methods
getList(string $id, array $fields = []): Response
getUserLists(string $userId, array $options = []): Response
createList(string $name, array $options = []): Response
updateList(string $id, array $options): Response
deleteList(string $id): Response
getListMembers(string $id, array $options = []): Response
addListMember(string $listId, string $userId): Response
removeListMember(string $listId, string $userId): Response
getPinnedLists(string $userId, array $options = []): Response
pinList(string $userId, string $listId): Response
unpinList(string $userId, string $listId): Response
```

## License

MIT

## Credits

This library is modeled after [abraham/twitteroauth](https://github.com/abraham/twitteroauth), the most popular PHP library for Twitter's OAuth 1.0a API.
