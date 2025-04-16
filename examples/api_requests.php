<?php
/**
 * Example of using TwitterOAuthV2 for various API requests.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;
use Abdulbaquee\TwitterOAuthV2\OAuthToken;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Initialize with a stored access token (in a real app, you would retrieve this from a database)
$accessToken = 'YOUR_ACCESS_TOKEN';
$refreshToken = 'YOUR_REFRESH_TOKEN';
$expiresAt = time() + 3600; // Example expiration time

// Create OAuth token object
$token = new OAuthToken($accessToken, $refreshToken, null, ['tweet.read', 'users.read', 'follows.read']);

// Initialize TwitterOAuthV2 with client credentials and token
$twitter = new TwitterOAuthV2($clientId, $clientSecret);
$twitter->setOAuthToken($token);

// Initialize the TwitterAPI wrapper
$api = new TwitterAPI($twitter);

// Example 1: Get a specific tweet
try {
    $tweetId = '1234567890123456789'; // Replace with a real tweet ID
    $response = $api->getTweet($tweetId, [
        'tweet.fields' => ['created_at', 'author_id', 'public_metrics', 'entities'],
        'expansions' => ['author_id'],
        'user.fields' => ['name', 'username', 'profile_image_url']
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "Tweet Information:\n";
    echo "-----------------\n";
    
    if (isset($data['data'])) {
        $tweet = $data['data'];
        echo "Tweet ID: {$tweet['id']}\n";
        echo "Text: {$tweet['text']}\n";
        echo "Created at: {$tweet['created_at']}\n";
        echo "Author ID: {$tweet['author_id']}\n";
        echo "Likes: {$tweet['public_metrics']['like_count']}\n";
        echo "Retweets: {$tweet['public_metrics']['retweet_count']}\n";
        echo "Replies: {$tweet['public_metrics']['reply_count']}\n";
        
        // Display author information from includes
        if (isset($data['includes']['users'][0])) {
            $author = $data['includes']['users'][0];
            echo "\nAuthor Information:\n";
            echo "Name: {$author['name']}\n";
            echo "Username: @{$author['username']}\n";
            echo "Profile Image: {$author['profile_image_url']}\n";
        }
    } else {
        echo "Tweet not found.\n";
    }
} catch (Exception $e) {
    echo 'Error getting tweet: ' . $e->getMessage() . "\n";
}

// Example 2: Get a user's followers
try {
    $userId = '12345678'; // Replace with a real user ID
    $response = $api->getFollowers($userId, [
        'max_results' => 5,
        'user.fields' => ['description', 'public_metrics']
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "\nFollowers:\n";
    echo "----------\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $follower) {
            echo "User ID: {$follower['id']}\n";
            echo "Username: @{$follower['username']}\n";
            echo "Name: {$follower['name']}\n";
            echo "Description: {$follower['description']}\n";
            echo "Followers: {$follower['public_metrics']['followers_count']}\n";
            echo "Following: {$follower['public_metrics']['following_count']}\n";
            echo "----------\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n";
        }
    } else {
        echo "No followers found or user does not exist.\n";
    }
} catch (Exception $e) {
    echo 'Error getting followers: ' . $e->getMessage() . "\n";
}

// Example 3: Search for tweets with pagination
try {
    $query = 'Twitter API';
    $maxResults = 10;
    $paginationToken = null;
    $totalTweets = 0;
    $maxPages = 2;
    $currentPage = 0;
    
    echo "\nSearch Results for '{$query}':\n";
    echo "-----------------------------\n";
    
    do {
        $options = [
            'max_results' => $maxResults,
            'tweet.fields' => 'created_at,author_id',
        ];
        
        if ($paginationToken) {
            $options['pagination_token'] = $paginationToken;
        }
        
        $response = $api->searchTweets($query, $options);
        $data = $response->getDecodedBody();
        
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $tweet) {
                $totalTweets++;
                echo "{$totalTweets}. [{$tweet['created_at']}] {$tweet['text']}\n";
            }
            
            // Get next pagination token if available
            $paginationToken = $data['meta']['next_token'] ?? null;
        } else {
            echo "No tweets found.\n";
            break;
        }
        
        $currentPage++;
    } while ($paginationToken && $currentPage < $maxPages);
    
    echo "\nTotal tweets retrieved: {$totalTweets}\n";
    
} catch (Exception $e) {
    echo 'Error searching tweets: ' . $e->getMessage() . "\n";
}

// Example 4: Get tweet counts
try {
    $query = 'Twitter API';
    $response = $api->getTweetCounts($query, [
        'granularity' => 'day',
        'start_time' => date('Y-m-d\TH:i:s\Z', strtotime('-7 days')),
        'end_time' => date('Y-m-d\TH:i:s\Z')
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "\nTweet Counts for '{$query}' (Last 7 days):\n";
    echo "----------------------------------------\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $count) {
            $date = date('Y-m-d', strtotime($count['start']));
            echo "{$date}: {$count['tweet_count']} tweets\n";
        }
        
        // Display total count
        if (isset($data['meta']['total_tweet_count'])) {
            echo "\nTotal tweets: {$data['meta']['total_tweet_count']}\n";
        }
    } else {
        echo "No count data available.\n";
    }
} catch (Exception $e) {
    echo 'Error getting tweet counts: ' . $e->getMessage() . "\n";
}
