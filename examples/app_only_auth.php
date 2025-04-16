<?php
/**
 * Example of using TwitterOAuthV2 with Bearer Token (app-only) authentication.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Initialize TwitterOAuthV2 with client credentials
$twitter = new TwitterOAuthV2($clientId, $clientSecret);

// Get a bearer token for app-only authentication
try {
    $bearerToken = $twitter->getBearerToken();
    echo "Bearer token obtained successfully.\n";
} catch (Exception $e) {
    die('Error getting bearer token: ' . $e->getMessage());
}

// Initialize the TwitterAPI wrapper
$api = new TwitterAPI($twitter);

// Example: Search for tweets
try {
    $response = $api->searchTweets('Twitter API v2', [
        'max_results' => 10,
        'tweet.fields' => 'created_at,author_id,public_metrics'
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "Search Results:\n";
    echo "----------------\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $tweet) {
            echo "Tweet ID: {$tweet['id']}\n";
            echo "Text: {$tweet['text']}\n";
            echo "Created at: {$tweet['created_at']}\n";
            echo "Author ID: {$tweet['author_id']}\n";
            echo "Likes: {$tweet['public_metrics']['like_count']}\n";
            echo "Retweets: {$tweet['public_metrics']['retweet_count']}\n";
            echo "----------------\n";
        }
    } else {
        echo "No tweets found.\n";
    }
    
    // Display rate limit information
    $rateLimit = $response->getRateLimitInfo();
    echo "Rate Limit Info:\n";
    echo "Limit: {$rateLimit['limit']}\n";
    echo "Remaining: {$rateLimit['remaining']}\n";
    echo "Reset: " . date('Y-m-d H:i:s', $rateLimit['reset']) . "\n";
    
} catch (Exception $e) {
    echo 'Error searching tweets: ' . $e->getMessage() . "\n";
}

// Example: Get user by username
try {
    $response = $api->getUserByUsername('twitterdev', [
        'user.fields' => ['description', 'public_metrics', 'created_at', 'verified']
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "\nUser Information:\n";
    echo "----------------\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        $user = $data['data'][0];
        echo "User ID: {$user['id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Name: {$user['name']}\n";
        echo "Description: {$user['description']}\n";
        echo "Created at: {$user['created_at']}\n";
        echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
        echo "Followers: {$user['public_metrics']['followers_count']}\n";
        echo "Following: {$user['public_metrics']['following_count']}\n";
        echo "Tweet count: {$user['public_metrics']['tweet_count']}\n";
    } else {
        echo "User not found.\n";
    }
    
} catch (Exception $e) {
    echo 'Error getting user: ' . $e->getMessage() . "\n";
}
