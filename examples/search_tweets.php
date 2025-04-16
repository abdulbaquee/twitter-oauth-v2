<?php
/**
 * Example of searching tweets using Twitter API v2.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Initialize with Bearer Token for app-only authentication
$twitter = new TwitterOAuthV2($clientId, $clientSecret);
$bearerToken = $twitter->getBearerToken();

// Initialize the TwitterAPI wrapper
$api = new TwitterAPI($twitter);

// Search query
$query = 'Twitter API';

// Search for tweets with pagination
try {
    $maxResults = 10;
    $paginationToken = null;
    $totalTweets = 0;
    $maxPages = 2;
    $currentPage = 0;
    
    echo "Search Results for '{$query}':\n";
    echo "============================\n\n";
    
    do {
        $options = [
            'max_results' => $maxResults,
            'tweet.fields' => 'created_at,author_id,public_metrics,entities',
            'expansions' => 'author_id',
            'user.fields' => 'name,username,profile_image_url,verified'
        ];
        
        if ($paginationToken) {
            $options['pagination_token'] = $paginationToken;
        }
        
        $response = $api->searchTweets($query, $options);
        $data = $response->getDecodedBody();
        
        if (isset($data['data']) && is_array($data['data'])) {
            // Create a map of user IDs to user objects
            $userMap = [];
            if (isset($data['includes']['users'])) {
                foreach ($data['includes']['users'] as $user) {
                    $userMap[$user['id']] = $user;
                }
            }
            
            foreach ($data['data'] as $tweet) {
                $totalTweets++;
                echo "Tweet #{$totalTweets}\n";
                echo "ID: {$tweet['id']}\n";
                echo "Created at: {$tweet['created_at']}\n";
                echo "Text: {$tweet['text']}\n";
                
                // Display author information if available
                if (isset($tweet['author_id']) && isset($userMap[$tweet['author_id']])) {
                    $author = $userMap[$tweet['author_id']];
                    echo "Author: {$author['name']} (@{$author['username']})\n";
                    echo "Verified: " . ($author['verified'] ? 'Yes' : 'No') . "\n";
                }
                
                // Display metrics
                if (isset($tweet['public_metrics'])) {
                    echo "Retweets: {$tweet['public_metrics']['retweet_count']}\n";
                    echo "Likes: {$tweet['public_metrics']['like_count']}\n";
                    echo "Replies: {$tweet['public_metrics']['reply_count']}\n";
                }
                
                // Display hashtags if available
                if (isset($tweet['entities']['hashtags'])) {
                    echo "Hashtags: ";
                    foreach ($tweet['entities']['hashtags'] as $hashtag) {
                        echo "#{$hashtag['tag']} ";
                    }
                    echo "\n";
                }
                
                echo "----------------------------\n\n";
            }
            
            // Get next pagination token if available
            $paginationToken = $data['meta']['next_token'] ?? null;
            
            if ($paginationToken) {
                echo "Page {$currentPage} completed. Moving to next page...\n\n";
            }
        } else {
            echo "No tweets found.\n";
            break;
        }
        
        $currentPage++;
    } while ($paginationToken && $currentPage < $maxPages);
    
    echo "Total tweets retrieved: {$totalTweets}\n";
    
    // Display rate limit information
    $rateLimit = $response->getRateLimitInfo();
    echo "\nRate Limit Info:\n";
    echo "Limit: {$rateLimit['limit']}\n";
    echo "Remaining: {$rateLimit['remaining']}\n";
    echo "Reset: " . date('Y-m-d H:i:s', $rateLimit['reset']) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
