<?php

return [
    // Twitter API Credentials
    'twitter' => [
        'client_id' => '', // Your Twitter Client ID
        'client_secret' => '', // Your Twitter Client Secret
        'redirect_uri' => '', // Your callback URL (e.g., http://localhost:8000/callback.php)
        'scopes' => [
            'tweet.read',
            'tweet.write',
            'users.read'
        ]
    ],

    // Session Configuration
    'session' => [
        'lifetime' => 7200, // Session lifetime in seconds (2 hours)
        'path' => '/',
        'domain' => null,
        'secure' => false, // Set to true in production
        'httponly' => true,
        'samesite' => 'lax'
    ]
]; 