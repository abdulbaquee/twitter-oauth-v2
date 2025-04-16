<?php
/**
 * Example of posting tweets using Twitter API v2.
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

// Example 1: Post a simple text tweet
try {
    echo "Posting a simple text tweet\n";
    echo "==========================\n\n";
    
    $response = $api->postTweet("Hello world! This is a tweet posted using the Twitter API v2 and abdulbaquee/twitter-oauth-v2 library.");
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Tweet posted successfully!\n";
        echo "Tweet ID: {$data['data']['id']}\n";
        echo "Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting tweet: " . $e->getMessage() . "\n\n";
}

// Example 2: Post a tweet with hashtags and mentions
try {
    echo "Posting a tweet with hashtags and mentions\n";
    echo "========================================\n\n";
    
    $response = $api->postTweet("Testing the Twitter API v2! #TwitterAPI #PHP @Twitter");
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Tweet posted successfully!\n";
        echo "Tweet ID: {$data['data']['id']}\n";
        echo "Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting tweet: " . $e->getMessage() . "\n\n";
}

// Example 3: Post a tweet with a poll
try {
    echo "Posting a tweet with a poll\n";
    echo "==========================\n\n";
    
    $response = $api->postTweet("What's your favorite programming language?", [
        'poll' => [
            'options' => ['PHP', 'JavaScript', 'Python', 'Java'],
            'duration_minutes' => 1440 // 24 hours
        ]
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Tweet with poll posted successfully!\n";
        echo "Tweet ID: {$data['data']['id']}\n";
        echo "Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post tweet with poll: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting tweet with poll: " . $e->getMessage() . "\n\n";
}

// Example 4: Post a reply to an existing tweet
try {
    echo "Posting a reply to an existing tweet\n";
    echo "==================================\n\n";
    
    // ID of the tweet to reply to
    $inReplyToTweetId = 'EXISTING_TWEET_ID';
    
    $response = $api->postTweet("This is a reply to the previous tweet!", [
        'reply' => [
            'in_reply_to_tweet_id' => $inReplyToTweetId
        ]
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Reply posted successfully!\n";
        echo "Reply Tweet ID: {$data['data']['id']}\n";
        echo "Reply Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post reply: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting reply: " . $e->getMessage() . "\n\n";
}

// Example 5: Post a tweet with a quote
try {
    echo "Posting a tweet with a quote\n";
    echo "===========================\n\n";
    
    // ID of the tweet to quote
    $quoteTweetId = 'EXISTING_TWEET_ID';
    
    $response = $api->postTweet("Check out this interesting tweet!", [
        'quote_tweet_id' => $quoteTweetId
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Quote tweet posted successfully!\n";
        echo "Quote Tweet ID: {$data['data']['id']}\n";
        echo "Quote Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post quote tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting quote tweet: " . $e->getMessage() . "\n\n";
}

// Example 6: Delete a tweet
try {
    echo "Deleting a tweet\n";
    echo "===============\n\n";
    
    // ID of the tweet to delete (use one created earlier)
    $tweetIdToDelete = 'TWEET_ID_TO_DELETE';
    
    $response = $api->deleteTweet($tweetIdToDelete);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['deleted']) && $data['data']['deleted'] === true) {
        echo "Tweet deleted successfully!\n";
        echo "Deleted Tweet ID: {$tweetIdToDelete}\n\n";
    } else {
        echo "Failed to delete tweet: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error deleting tweet: " . $e->getMessage() . "\n\n";
}

// Example 7: Post a tweet with media (requires media upload first)
try {
    echo "Posting a tweet with media\n";
    echo "========================\n\n";
    
    // This assumes you've already uploaded media and have the media ID
    // See media_upload.php example for how to upload media
    $mediaId = 'PREVIOUSLY_UPLOADED_MEDIA_ID';
    
    $response = $api->postTweet("Check out this image! #TwitterAPI", [
        'media' => [
            'media_ids' => [$mediaId]
        ]
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        echo "Tweet with media posted successfully!\n";
        echo "Tweet ID: {$data['data']['id']}\n";
        echo "Tweet text: {$data['data']['text']}\n\n";
    } else {
        echo "Failed to post tweet with media: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error posting tweet with media: " . $e->getMessage() . "\n\n";
}
