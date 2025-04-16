<?php
/**
 * Example of Twitter API v2 streaming endpoints.
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

// Example 1: Connect to filtered stream
try {
    echo "Connecting to filtered stream\n";
    echo "============================\n\n";
    
    // First, let's add some rules to the stream
    $rules = [
        ['value' => 'twitter has:images', 'tag' => 'twitter with images'],
        ['value' => 'cat has:media', 'tag' => 'cats with media']
    ];
    
    echo "Adding stream rules...\n";
    $response = $twitter->post('tweets/search/stream/rules', ['add' => $rules]);
    
    if (!$response->isSuccess()) {
        echo "Failed to add stream rules: " . print_r($response->getDecodedBody(), true) . "\n\n";
    } else {
        echo "Stream rules added successfully!\n\n";
        
        // Get current rules
        echo "Current stream rules:\n";
        $response = $twitter->get('tweets/search/stream/rules');
        $data = $response->getDecodedBody();
        
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $rule) {
                echo "Rule ID: {$rule['id']}\n";
                echo "Value: {$rule['value']}\n";
                echo "Tag: {$rule['tag']}\n";
                echo "----------------------------\n\n";
            }
        }
        
        // Connect to the stream (note: this is a long-running connection)
        echo "Connecting to stream...\n";
        echo "Press Ctrl+C to stop\n\n";
        
        // In a real application, you would handle this connection properly
        // Here we're just showing how to make the request
        $response = $twitter->get('tweets/search/stream', [
            'tweet.fields' => 'created_at,entities,public_metrics',
            'expansions' => 'author_id,attachments.media_keys',
            'user.fields' => 'name,username,profile_image_url',
            'media.fields' => 'url,preview_image_url'
        ]);
        
        // This would normally be a streaming connection
        // For demonstration purposes, we're just showing the request
        echo "Stream connection made. In a real application, you would process the stream data.\n\n";
    }
} catch (Exception $e) {
    echo "Error with filtered stream: " . $e->getMessage() . "\n\n";
}

// Example 2: Delete stream rules
try {
    echo "Deleting stream rules\n";
    echo "====================\n\n";
    
    // Get current rules to delete
    $response = $twitter->get('tweets/search/stream/rules');
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        $ruleIds = array_map(function($rule) {
            return $rule['id'];
        }, $data['data']);
        
        if (!empty($ruleIds)) {
            echo "Deleting rules: " . implode(', ', $ruleIds) . "\n";
            
            $response = $twitter->post('tweets/search/stream/rules', ['delete' => ['ids' => $ruleIds]]);
            
            if ($response->isSuccess()) {
                echo "Stream rules deleted successfully!\n\n";
            } else {
                echo "Failed to delete stream rules: " . print_r($response->getDecodedBody(), true) . "\n\n";
            }
        } else {
            echo "No rules to delete.\n\n";
        }
    } else {
        echo "No rules found.\n\n";
    }
} catch (Exception $e) {
    echo "Error deleting stream rules: " . $e->getMessage() . "\n\n";
}

// Example 3: Sample stream
try {
    echo "Connecting to sample stream\n";
    echo "==========================\n\n";
    
    // Connect to the sample stream (note: this is a long-running connection)
    echo "Connecting to sample stream...\n";
    echo "Press Ctrl+C to stop\n\n";
    
    // In a real application, you would handle this connection properly
    // Here we're just showing how to make the request
    $response = $twitter->get('tweets/sample/stream', [
        'tweet.fields' => 'created_at,entities,public_metrics',
        'expansions' => 'author_id',
        'user.fields' => 'name,username'
    ]);
    
    // This would normally be a streaming connection
    // For demonstration purposes, we're just showing the request
    echo "Sample stream connection made. In a real application, you would process the stream data.\n\n";
} catch (Exception $e) {
    echo "Error with sample stream: " . $e->getMessage() . "\n\n";
}

// Example 4: Volume streams (requires elevated access)
try {
    echo "Connecting to volume stream (requires elevated access)\n";
    echo "===================================================\n\n";
    
    echo "Note: Volume streams require elevated access to the Twitter API.\n";
    echo "This example is for demonstration purposes only.\n\n";
    
    // In a real application with elevated access, you would connect like this:
    /*
    $response = $twitter->get('tweets/sample/stream/v2', [
        'tweet.fields' => 'created_at,entities,public_metrics',
        'expansions' => 'author_id',
        'user.fields' => 'name,username'
    ]);
    */
    
    echo "Volume stream connection would be made here with proper credentials.\n\n";
} catch (Exception $e) {
    echo "Error with volume stream: " . $e->getMessage() . "\n\n";
}

// Example 5: Real-time processing of stream data
echo "Real-time processing of stream data\n";
echo "=================================\n\n";

echo "In a real application, you would process the stream data in real-time like this:\n\n";

echo "```php
// Connect to the stream
\$response = \$twitter->get('tweets/search/stream', [
    'tweet.fields' => 'created_at,entities,public_metrics',
    'expansions' => 'author_id,attachments.media_keys',
    'user.fields' => 'name,username,profile_image_url',
    'media.fields' => 'url,preview_image_url'
]);

// Get the response as a stream
\$stream = \$response->getBody();

// Process the stream data in real-time
while (!feof(\$stream)) {
    \$line = fgets(\$stream);
    
    if (\$line === \"\\r\\n\") {
        // Keep-alive signal
        continue;
    }
    
    // Parse the JSON data
    \$data = json_decode(\$line, true);
    
    if (isset(\$data['data'])) {
        \$tweet = \$data['data'];
        \$user = \$data['includes']['users'][0];
        
        echo \"Tweet from @{\$user['username']}: {\$tweet['text']}\\n\";
        
        // Process the tweet data as needed
        // Store in database, trigger notifications, etc.
    }
}
```\n\n";

echo "This example demonstrates how to connect to Twitter's streaming endpoints and process real-time data.\n";
echo "For production use, consider using a more robust approach with proper error handling and reconnection logic.\n";
