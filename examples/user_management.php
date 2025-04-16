<?php
/**
 * Example of user management (follow/unfollow) using Twitter API v2.
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

// Target user ID to follow/unfollow
$targetUserId = 'TARGET_USER_ID';

// Example 1: Get user's followers
try {
    echo "Getting followers for user ID: {$userId}\n";
    echo "================================\n\n";
    
    $response = $api->getFollowers($userId, [
        'max_results' => 5,
        'user.fields' => 'name,username,description,public_metrics,verified'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $follower) {
            echo "Follower: {$follower['name']} (@{$follower['username']})\n";
            echo "ID: {$follower['id']}\n";
            echo "Verified: " . ($follower['verified'] ? 'Yes' : 'No') . "\n";
            echo "Description: {$follower['description']}\n";
            echo "Followers: {$follower['public_metrics']['followers_count']}\n";
            echo "Following: {$follower['public_metrics']['following_count']}\n";
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No followers found or user does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting followers: " . $e->getMessage() . "\n\n";
}

// Example 2: Get users the user is following
try {
    echo "Getting users that user ID {$userId} is following\n";
    echo "==============================================\n\n";
    
    $response = $api->getFollowing($userId, [
        'max_results' => 5,
        'user.fields' => 'name,username,description,public_metrics,verified'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $following) {
            echo "Following: {$following['name']} (@{$following['username']})\n";
            echo "ID: {$following['id']}\n";
            echo "Verified: " . ($following['verified'] ? 'Yes' : 'No') . "\n";
            echo "Description: {$following['description']}\n";
            echo "Followers: {$following['public_metrics']['followers_count']}\n";
            echo "Following: {$following['public_metrics']['following_count']}\n";
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No following users found or user does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting following: " . $e->getMessage() . "\n\n";
}

// Example 3: Follow a user
try {
    echo "Following user ID: {$targetUserId}\n";
    echo "===========================\n\n";
    
    $response = $api->followUser($userId, $targetUserId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['following']) && $data['data']['following'] === true) {
        echo "Successfully followed user!\n\n";
    } else {
        echo "Failed to follow user: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error following user: " . $e->getMessage() . "\n\n";
}

// Example 4: Unfollow a user
try {
    echo "Unfollowing user ID: {$targetUserId}\n";
    echo "=============================\n\n";
    
    $response = $api->unfollowUser($userId, $targetUserId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['following']) && $data['data']['following'] === false) {
        echo "Successfully unfollowed user!\n\n";
    } else {
        echo "Failed to unfollow user: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error unfollowing user: " . $e->getMessage() . "\n\n";
}
