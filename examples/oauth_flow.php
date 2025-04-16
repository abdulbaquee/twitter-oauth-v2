<?php
/**
 * Example of using TwitterOAuthV2 with OAuth 2.0 Authorization Code Flow with PKCE.
 *
 * @license MIT
 */

// Start session for storing OAuth state and code verifier
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;
use Abdulbaquee\TwitterOAuthV2\OAuthToken;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';
$redirectUri = 'YOUR_REDIRECT_URI'; // Must match the one registered in your Twitter App

// Initialize TwitterOAuthV2 with client credentials and redirect URI
$twitter = new TwitterOAuthV2($clientId, $clientSecret, $redirectUri);

// Step 1: Generate authorization URL and redirect user
if (!isset($_GET['code'])) {
    // Generate authorization URL with required scopes
    $authUrl = $twitter->getAuthorizationUrl([
        'scope' => 'tweet.read users.read tweet.write follows.read follows.write like.read like.write'
    ]);
    
    // Redirect to Twitter authorization page
    header('Location: ' . $authUrl);
    exit;
}

// Step 2: Handle the callback with authorization code
try {
    // Exchange authorization code for access token
    $token = $twitter->getAccessToken($_GET['code']);
    
    // Store token in session (in a real app, you would store it securely in a database)
    $_SESSION['oauth_token'] = [
        'access_token' => $token->getAccessToken(),
        'refresh_token' => $token->getRefreshToken(),
        'expires_at' => $token->getExpiresAt(),
        'scopes' => $token->getScopes()
    ];
    
    echo "Authentication successful!\n";
    echo "Access Token: " . $token->getAccessToken() . "\n";
    echo "Refresh Token: " . $token->getRefreshToken() . "\n";
    echo "Expires At: " . date('Y-m-d H:i:s', $token->getExpiresAt()) . "\n";
    echo "Scopes: " . implode(', ', $token->getScopes()) . "\n\n";
    
    // Initialize the TwitterAPI wrapper
    $api = new TwitterAPI($twitter);
    
    // Example: Get authenticated user information
    $response = $api->getMe([
        'user.fields' => ['description', 'public_metrics', 'created_at', 'verified']
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "Your Twitter Profile:\n";
    echo "--------------------\n";
    
    if (isset($data['data'])) {
        $user = $data['data'];
        echo "User ID: {$user['id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Name: {$user['name']}\n";
        echo "Description: {$user['description']}\n";
        echo "Created at: {$user['created_at']}\n";
        echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
        echo "Followers: {$user['public_metrics']['followers_count']}\n";
        echo "Following: {$user['public_metrics']['following_count']}\n";
        echo "Tweet count: {$user['public_metrics']['tweet_count']}\n";
    }
    
    // Example: Post a tweet (if tweet.write scope is authorized)
    if (in_array('tweet.write', $token->getScopes())) {
        try {
            $tweetResponse = $api->postTweet("Testing the TwitterOAuthV2 PHP library! #TwitterAPI");
            $tweetData = $tweetResponse->getDecodedBody();
            
            echo "\nTweet Posted:\n";
            echo "------------\n";
            echo "Tweet ID: {$tweetData['data']['id']}\n";
            echo "Text: {$tweetData['data']['text']}\n";
        } catch (Exception $e) {
            echo "\nError posting tweet: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo 'Authentication Error: ' . $e->getMessage();
}
