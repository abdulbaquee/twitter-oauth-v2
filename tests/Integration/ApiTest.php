<?php
/**
 * Integration test file for TwitterOAuthV2 library.
 * 
 * Note: This test requires valid Twitter API credentials to run.
 * Set the credentials in the environment variables or modify this file.
 *
 * @license MIT
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;
use Abdulbaquee\TwitterOAuthV2\TwitterOAuthException;

// Get credentials from environment variables or set them here
$clientId = getenv('TWITTER_CLIENT_ID') ?: 'YOUR_CLIENT_ID';
$clientSecret = getenv('TWITTER_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET';

// Colors for console output
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$reset = "\033[0m";

echo "{$yellow}Twitter API v2 Integration Tests{$reset}\n";
echo "--------------------------------\n\n";

// Check if credentials are set
if ($clientId === 'YOUR_CLIENT_ID' || $clientSecret === 'YOUR_CLIENT_SECRET') {
    echo "{$yellow}Warning: Using placeholder credentials. Tests will not connect to the Twitter API.{$reset}\n";
    echo "Set TWITTER_CLIENT_ID and TWITTER_CLIENT_SECRET environment variables or update this file.\n\n";
    echo "Skipping integration tests...\n";
    exit(0);
}

// Initialize TwitterOAuthV2
echo "Initializing TwitterOAuthV2...\n";
$twitter = new TwitterOAuthV2($clientId, $clientSecret);

// Test Bearer Token authentication
echo "Testing Bearer Token authentication...\n";
try {
    $bearerToken = $twitter->getBearerToken();
    echo "{$green}✓ Successfully obtained Bearer Token{$reset}\n";
} catch (TwitterOAuthException $e) {
    echo "{$red}✗ Failed to obtain Bearer Token: {$e->getMessage()}{$reset}\n";
    exit(1);
}

// Initialize TwitterAPI
$api = new TwitterAPI($twitter);

// Test searching for tweets
echo "Testing tweet search...\n";
try {
    $response = $api->searchTweets('Twitter API', ['max_results' => 10]);
    
    if ($response->isSuccess()) {
        $data = $response->getDecodedBody();
        $tweetCount = isset($data['data']) ? count($data['data']) : 0;
        echo "{$green}✓ Successfully searched for tweets. Found {$tweetCount} tweets.{$reset}\n";
        
        // Display rate limit information
        $rateLimit = $response->getRateLimitInfo();
        echo "  Rate Limit: {$rateLimit['remaining']}/{$rateLimit['limit']} requests remaining\n";
    } else {
        echo "{$red}✗ Search request was not successful. HTTP code: {$response->getHttpCode()}{$reset}\n";
    }
} catch (TwitterOAuthException $e) {
    echo "{$red}✗ Error searching tweets: {$e->getMessage()}{$reset}\n";
}

// Test getting a user by username
echo "Testing get user by username...\n";
try {
    $response = $api->getUserByUsername('twitterdev', [
        'user.fields' => ['description', 'public_metrics', 'created_at', 'verified']
    ]);
    
    if ($response->isSuccess()) {
        $data = $response->getDecodedBody();
        if (isset($data['data']) && is_array($data['data'])) {
            $user = $data['data'][0];
            echo "{$green}✓ Successfully retrieved user @{$user['username']}{$reset}\n";
            echo "  User ID: {$user['id']}\n";
            echo "  Name: {$user['name']}\n";
            echo "  Followers: {$user['public_metrics']['followers_count']}\n";
        } else {
            echo "{$red}✗ User data not found in response{$reset}\n";
        }
    } else {
        echo "{$red}✗ User request was not successful. HTTP code: {$response->getHttpCode()}{$reset}\n";
    }
} catch (TwitterOAuthException $e) {
    echo "{$red}✗ Error getting user: {$e->getMessage()}{$reset}\n";
}

// Test getting tweet counts
echo "Testing tweet counts...\n";
try {
    $response = $api->getTweetCounts('Twitter API', [
        'granularity' => 'day',
        'start_time' => date('Y-m-d\TH:i:s\Z', strtotime('-7 days')),
        'end_time' => date('Y-m-d\TH:i:s\Z')
    ]);
    
    if ($response->isSuccess()) {
        $data = $response->getDecodedBody();
        if (isset($data['data']) && is_array($data['data'])) {
            $totalCount = isset($data['meta']['total_tweet_count']) ? $data['meta']['total_tweet_count'] : 'unknown';
            echo "{$green}✓ Successfully retrieved tweet counts. Total: {$totalCount} tweets.{$reset}\n";
        } else {
            echo "{$red}✗ Count data not found in response{$reset}\n";
        }
    } else {
        echo "{$red}✗ Count request was not successful. HTTP code: {$response->getHttpCode()}{$reset}\n";
    }
} catch (TwitterOAuthException $e) {
    echo "{$red}✗ Error getting tweet counts: {$e->getMessage()}{$reset}\n";
}

echo "\n{$green}Integration tests completed.{$reset}\n";
