<?php
/**
 * Example of tweet interactions (like, retweet) using Twitter API v2.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';
$accessToken = 'YOUR_ACCESS_TOKEN';
$refreshToken = 'YOUR_REFRESH_TOKEN';

// Initialize TwitterOAuthV2 with OAuth token
$twitter = new TwitterOAuthV2($clientId, $clientSecret);
$twitter->setOAuthToken([
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken
]);

// Initialize the TwitterAPI wrapper
$api = new TwitterAPI($twitter);

// Your user ID (the authenticated user)
$userId = 'YOUR_USER_ID';

// Target tweet ID to interact with
$tweetId = 'TARGET_TWEET_ID';

// Example 1: Like a tweet
try {
    echo "Liking tweet ID: {$tweetId}\n";
    echo "=====================\n\n";
    
    $response = $api->likeTweet($userId, $tweetId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['liked']) && $data['data']['liked'] === true) {
        echo "Successfully liked the tweet!\n\n";
    } else {
        echo "Failed to like the tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error liking tweet: " . $e->getMessage() . "\n\n";
}

// Example 2: Get users who liked a tweet
try {
    echo "Getting users who liked tweet ID: {$tweetId}\n";
    echo "======================================\n\n";
    
    $response = $api->getLikingUsers($tweetId, [
        'max_results' => 5,
        'user.fields' => 'name,username,profile_image_url,verified'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $user) {
            echo "User: {$user['name']} (@{$user['username']})\n";
            echo "ID: {$user['id']}\n";
            echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
            if (isset($user['profile_image_url'])) {
                echo "Profile Image: {$user['profile_image_url']}\n";
            }
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No liking users found or tweet does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting liking users: " . $e->getMessage() . "\n\n";
}

// Example 3: Unlike a tweet
try {
    echo "Unliking tweet ID: {$tweetId}\n";
    echo "=======================\n\n";
    
    $response = $api->unlikeTweet($userId, $tweetId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['liked']) && $data['data']['liked'] === false) {
        echo "Successfully unliked the tweet!\n\n";
    } else {
        echo "Failed to unlike the tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error unliking tweet: " . $e->getMessage() . "\n\n";
}

// Example 4: Retweet a tweet
try {
    echo "Retweeting tweet ID: {$tweetId}\n";
    echo "=========================\n\n";
    
    $response = $api->retweet($userId, $tweetId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['retweeted']) && $data['data']['retweeted'] === true) {
        echo "Successfully retweeted the tweet!\n\n";
    } else {
        echo "Failed to retweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error retweeting: " . $e->getMessage() . "\n\n";
}

// Example 5: Get users who retweeted a tweet
try {
    echo "Getting users who retweeted tweet ID: {$tweetId}\n";
    echo "==========================================\n\n";
    
    $response = $api->getRetweetedBy($tweetId, [
        'max_results' => 5,
        'user.fields' => 'name,username,profile_image_url,verified'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $user) {
            echo "User: {$user['name']} (@{$user['username']})\n";
            echo "ID: {$user['id']}\n";
            echo "Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
            if (isset($user['profile_image_url'])) {
                echo "Profile Image: {$user['profile_image_url']}\n";
            }
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No retweeting users found or tweet does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting retweeting users: " . $e->getMessage() . "\n\n";
}

// Example 6: Undo a retweet
try {
    echo "Undoing retweet of tweet ID: {$tweetId}\n";
    echo "================================\n\n";
    
    $response = $api->unretweet($userId, $tweetId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['retweeted']) && $data['data']['retweeted'] === false) {
        echo "Successfully undid the retweet!\n\n";
    } else {
        echo "Failed to undo retweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error undoing retweet: " . $e->getMessage() . "\n\n";
}
