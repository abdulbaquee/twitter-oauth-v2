<?php
// Ensure no output is sent before headers
ob_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\OAuthClient;
use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Retrieve state from session and query string
$storedState = $sessionHandler->get('oauth_state');
$receivedState = $_GET['state'] ?? '';

if ($storedState !== $receivedState) {
    ob_end_clean();
    header('Location: /error.php?message=Invalid+state+parameter');
    exit;
}

// Clear the state from session
$sessionHandler->remove('oauth_state');

try {
    if (!isset($_GET['code'])) {
        throw new \RuntimeException('Authorization code not found');
    }

    // Exchange code for tokens
    $oauthClient = new OAuthClient(
        $config['twitter']['client_id'],
        $config['twitter']['client_secret'],
        $config['twitter']['redirect_uri'],
        $config['twitter']['scopes'],
        null,
        null,
        null,
        $sessionHandler
    );

    $tokens = $oauthClient->getAccessToken($_GET['code']);
    
    // Store tokens in session
    $sessionHandler->set('twitter_tokens', $tokens);
    
    // Clear any output buffer
    ob_end_clean();
    
    // Redirect to success page
    header('Location: /success.php');
    exit;
} catch (\Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    
    // Redirect to error page
    header('Location: /error.php?message=' . urlencode($e->getMessage()));
    exit;
}