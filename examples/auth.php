<?php
// Ensure no output is sent before headers
ob_start();

error_reporting(-1);

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuth\OAuthClient;
use Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler;

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize session handler
$sessionHandler = new NativeSessionHandler();

// Initialize OAuth client
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

// Get authorization URL
$authUrl = $oauthClient->getAuthorizationUrl();

// Store state in session
$sessionHandler->set('oauth_state', $oauthClient->getState());

// Clear any output buffer
ob_end_clean();

// Redirect to Twitter
header('Location: ' . $authUrl);
exit;