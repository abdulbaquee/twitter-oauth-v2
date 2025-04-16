<?php
/**
 * Example of user timeline retrieval using Twitter API v2.
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

// User ID to fetch timeline for
$userId = '12345678'; // Replace with a real user ID

// Get user timeline with various parameters
try {
    $response = $api->getUserTimeline($userId, [
        'max_results' => 10,
        'tweet.fields' => 'created_at,public_metrics,entities,attachments',
        'expansions' => 'attachments.media_keys',
        'media.fields' => 'type,url,width,height',
        'exclude' => 'retweets,replies'
    ]);
    
    $data = $response->getDecodedBody();
    
    echo "User Timeline:\n";
    echo "==============\n\n";
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $tweet) {
            echo "Tweet ID: {$tweet['id']}\n";
            echo "Created at: {$tweet['created_at']}\n";
            echo "Text: {$tweet['text']}\n";
            
            // Display metrics
            if (isset($tweet['public_metrics'])) {
                echo "Retweets: {$tweet['public_metrics']['retweet_count']}\n";
                echo "Likes: {$tweet['public_metrics']['like_count']}\n";
                echo "Replies: {$tweet['public_metrics']['reply_count']}\n";
            }
            
            // Display media information if available
            if (isset($tweet['attachments']['media_keys']) && isset($data['includes']['media'])) {
                echo "Media:\n";
                $mediaMap = [];
                
                // Create a map of media keys to media objects
                foreach ($data['includes']['media'] as $media) {
                    $mediaMap[$media['media_key']] = $media;
                }
                
                // Display information for each media attached to the tweet
                foreach ($tweet['attachments']['media_keys'] as $mediaKey) {
                    if (isset($mediaMap[$mediaKey])) {
                        $media = $mediaMap[$mediaKey];
                        echo "  - Type: {$media['type']}\n";
                        
                        if (isset($media['url'])) {
                            echo "  - URL: {$media['url']}\n";
                        }
                        
                        if (isset($media['width']) && isset($media['height'])) {
                            echo "  - Dimensions: {$media['width']}x{$media['height']}\n";
                        }
                    }
                }
            }
            
            // Display hashtags if available
            if (isset($tweet['entities']['hashtags'])) {
                echo "Hashtags: ";
                foreach ($tweet['entities']['hashtags'] as $hashtag) {
                    echo "#{$hashtag['tag']} ";
                }
                echo "\n";
            }
            
            echo "==============\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n";
            echo "Use this token to fetch the next page of results.\n";
        }
    } else {
        echo "No tweets found or user does not exist.\n";
    }
    
    // Display rate limit information
    $rateLimit = $response->getRateLimitInfo();
    echo "Rate Limit Info:\n";
    echo "Limit: {$rateLimit['limit']}\n";
    echo "Remaining: {$rateLimit['remaining']}\n";
    echo "Reset: " . date('Y-m-d H:i:s', $rateLimit['reset']) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
