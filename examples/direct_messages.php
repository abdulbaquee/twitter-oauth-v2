<?php
/**
 * Example of direct message operations using Twitter API v2.
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

// Target user ID to send direct messages to
$recipientId = 'RECIPIENT_USER_ID';

// Example 1: Send a direct message
try {
    echo "Sending a direct message\n";
    echo "=======================\n\n";
    
    $response = $api->sendDirectMessage($recipientId, "Hello! This is a direct message sent using the Twitter API v2.");
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['dm_conversation_id'])) {
        $conversationId = $data['data']['dm_conversation_id'];
        $messageId = $data['data']['dm_event_id'];
        
        echo "Direct message sent successfully!\n";
        echo "Conversation ID: {$conversationId}\n";
        echo "Message ID: {$messageId}\n\n";
    } else {
        echo "Failed to send direct message: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error sending direct message: " . $e->getMessage() . "\n\n";
    // Use sample IDs for the rest of the examples
    $conversationId = 'SAMPLE_CONVERSATION_ID';
}

// Example 2: Get direct message conversations
try {
    echo "Getting direct message conversations\n";
    echo "==================================\n\n";
    
    $response = $api->getDirectMessageConversations([
        'max_results' => 5,
        'dm_event.fields' => 'text,created_at',
        'expansions' => 'participant_ids'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $conversation) {
            echo "Conversation ID: {$conversation['id']}\n";
            
            if (isset($conversation['participant_ids'])) {
                echo "Participants: " . implode(', ', $conversation['participant_ids']) . "\n";
            }
            
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No conversations found.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting conversations: " . $e->getMessage() . "\n\n";
}

// Example 3: Get direct messages in a conversation
try {
    echo "Getting direct messages in a conversation\n";
    echo "=======================================\n\n";
    
    $response = $api->getDirectMessages($conversationId, [
        'max_results' => 10,
        'dm_event.fields' => 'text,created_at,sender_id',
        'expansions' => 'attachments.media_keys',
        'media.fields' => 'type,url'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        // Create a map of media keys to media objects if available
        $mediaMap = [];
        if (isset($data['includes']['media'])) {
            foreach ($data['includes']['media'] as $media) {
                $mediaMap[$media['media_key']] = $media;
            }
        }
        
        foreach ($data['data'] as $message) {
            echo "Message ID: {$message['id']}\n";
            echo "Sender ID: {$message['sender_id']}\n";
            echo "Created at: {$message['created_at']}\n";
            echo "Text: {$message['text']}\n";
            
            // Display media information if available
            if (isset($message['attachments']['media_keys'])) {
                echo "Media:\n";
                
                foreach ($message['attachments']['media_keys'] as $mediaKey) {
                    if (isset($mediaMap[$mediaKey])) {
                        $media = $mediaMap[$mediaKey];
                        echo "  - Type: {$media['type']}\n";
                        
                        if (isset($media['url'])) {
                            echo "  - URL: {$media['url']}\n";
                        }
                    }
                }
            }
            
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No messages found in this conversation.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting messages: " . $e->getMessage() . "\n\n";
}

// Example 4: Send a direct message with media
try {
    echo "Sending a direct message with media\n";
    echo "=================================\n\n";
    
    // This assumes you've already uploaded media and have the media ID
    // See media_upload.php example for how to upload media
    $mediaId = 'PREVIOUSLY_UPLOADED_MEDIA_ID';
    
    $response = $api->sendDirectMessage($recipientId, "Check out this image!", [
        'media' => [
            'media_ids' => [$mediaId]
        ]
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['dm_conversation_id'])) {
        $conversationId = $data['data']['dm_conversation_id'];
        $messageId = $data['data']['dm_event_id'];
        
        echo "Direct message with media sent successfully!\n";
        echo "Conversation ID: {$conversationId}\n";
        echo "Message ID: {$messageId}\n\n";
    } else {
        echo "Failed to send direct message with media: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error sending direct message with media: " . $e->getMessage() . "\n\n";
}
