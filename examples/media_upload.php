<?php
/**
 * Example of media upload using Twitter API v2.
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

// Path to the image file you want to upload
$imagePath = '/path/to/your/image.jpg';

// Step 1: Initialize media upload
echo "Step 1: Initializing media upload...\n";
$initResponse = $twitter->post('media/upload', [
    'command' => 'INIT',
    'media_type' => 'image/jpeg',
    'total_bytes' => filesize($imagePath)
]);

if (!$initResponse->isSuccess()) {
    die("Failed to initialize media upload: " . print_r($initResponse->getDecodedBody(), true));
}

$mediaId = $initResponse->getDecodedBody()['media_id_string'];
echo "Media ID: $mediaId\n";

// Step 2: Upload media chunks
echo "Step 2: Uploading media chunks...\n";
$mediaData = file_get_contents($imagePath);
$chunkSize = 1024 * 1024; // 1MB chunks
$segmentIndex = 0;

for ($offset = 0; $offset < strlen($mediaData); $offset += $chunkSize) {
    $chunk = substr($mediaData, $offset, $chunkSize);
    
    $appendResponse = $twitter->post('media/upload', [
        'command' => 'APPEND',
        'media_id' => $mediaId,
        'segment_index' => $segmentIndex,
        'media' => base64_encode($chunk)
    ]);
    
    if (!$appendResponse->isSuccess()) {
        die("Failed to append media chunk: " . print_r($appendResponse->getDecodedBody(), true));
    }
    
    echo "Uploaded chunk $segmentIndex\n";
    $segmentIndex++;
}

// Step 3: Finalize media upload
echo "Step 3: Finalizing media upload...\n";
$finalizeResponse = $twitter->post('media/upload', [
    'command' => 'FINALIZE',
    'media_id' => $mediaId
]);

if (!$finalizeResponse->isSuccess()) {
    die("Failed to finalize media upload: " . print_r($finalizeResponse->getDecodedBody(), true));
}

echo "Media upload finalized successfully!\n";

// Step 4: Wait for media processing (optional)
$processingInfo = $finalizeResponse->getDecodedBody()['processing_info'] ?? null;
if ($processingInfo && $processingInfo['state'] !== 'succeeded') {
    echo "Media is being processed...\n";
    
    $checkAfterSecs = $processingInfo['check_after_secs'] ?? 1;
    sleep($checkAfterSecs);
    
    $statusResponse = $twitter->get('media/upload', [
        'command' => 'STATUS',
        'media_id' => $mediaId
    ]);
    
    if (!$statusResponse->isSuccess()) {
        die("Failed to check media status: " . print_r($statusResponse->getDecodedBody(), true));
    }
    
    $processingInfo = $statusResponse->getDecodedBody()['processing_info'];
    echo "Media processing state: " . $processingInfo['state'] . "\n";
    
    if ($processingInfo['state'] !== 'succeeded') {
        die("Media processing did not complete successfully: " . print_r($processingInfo, true));
    }
}

// Step 5: Post a tweet with the uploaded media
echo "Step 5: Posting tweet with media...\n";
$tweetResponse = $api->postTweet("Check out this image! #TwitterAPI", [
    'media' => [
        'media_ids' => [$mediaId]
    ]
]);

if ($tweetResponse->isSuccess()) {
    $tweetData = $tweetResponse->getDecodedBody();
    echo "Tweet posted successfully!\n";
    echo "Tweet ID: " . $tweetData['data']['id'] . "\n";
    echo "Tweet text: " . $tweetData['data']['text'] . "\n";
} else {
    echo "Failed to post tweet: " . print_r($tweetResponse->getDecodedBody(), true) . "\n";
}
