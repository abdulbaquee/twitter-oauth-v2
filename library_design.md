# Twitter OAuth v2 PHP Library Design

## Overview

This document outlines the design for a PHP library that implements Twitter's OAuth REST API v2, modeled after the popular abraham/twitteroauth library. The new library will support OAuth 2.0 authentication methods while maintaining a similar structure and interface to the original library.

## Library Name

`TwitterOAuthV2`

## Namespace

`Abdulbaquee\TwitterOAuthV2`

## Requirements

- PHP 8.0 or higher
- Composer for dependency management
- curl extension
- json extension

## Directory Structure

```
twitter-oauth-v2/
├── src/
│   ├── Config.php
│   ├── TwitterOAuthV2.php
│   ├── TwitterOAuthException.php
│   ├── BearerToken.php
│   ├── OAuthToken.php
│   ├── Request.php
│   ├── Response.php
│   ├── Util/
│   │   ├── JsonDecoder.php
│   │   ├── Util.php
│   │   └── PKCE.php
├── tests/
│   ├── Unit/
│   │   └── ...
│   └── Integration/
│       └── ...
├── examples/
│   ├── app_only_auth.php
│   ├── oauth_flow.php
│   └── api_requests.php
├── composer.json
├── README.md
├── LICENSE.md
└── .gitignore
```

## Class Hierarchy

### Main Classes

1. **Config**
   - Base configuration class
   - Stores API endpoints, version information, and default settings

2. **TwitterOAuthV2** (extends Config)
   - Main class for interacting with Twitter API v2
   - Handles authentication and API requests
   - Supports both Bearer Token and OAuth 2.0 Authorization Code Flow

3. **BearerToken**
   - Handles app-only authentication
   - Generates and stores Bearer Tokens

4. **OAuthToken**
   - Handles user authentication tokens
   - Stores access tokens and refresh tokens

5. **Request**
   - Handles HTTP requests to Twitter API
   - Supports different HTTP methods (GET, POST, PUT, DELETE)
   - Handles request parameters and headers

6. **Response**
   - Processes API responses
   - Handles JSON decoding
   - Provides access to response data and metadata

7. **TwitterOAuthException**
   - Custom exception class for library-specific errors
   - Provides detailed error information

### Utility Classes

1. **JsonDecoder**
   - Handles JSON decoding with error handling
   - Supports different response formats

2. **PKCE**
   - Implements PKCE (Proof Key for Code Exchange) for OAuth 2.0
   - Generates code verifiers and code challenges

3. **Util**
   - General utility functions
   - URL encoding/decoding
   - Parameter formatting

## Authentication Flows

### 1. Bearer Token (App-Only) Authentication

```php
// Initialize with consumer key and secret
$twitter = new TwitterOAuthV2($consumerKey, $consumerSecret);

// Get Bearer Token
$bearerToken = $twitter->getBearerToken();

// Use Bearer Token for subsequent requests
$twitter->setBearerToken($bearerToken);

// Make API request
$response = $twitter->get("tweets/search/recent", ["query" => "Twitter API"]);
```

### 2. OAuth 2.0 Authorization Code Flow with PKCE

```php
// Initialize with client ID, client secret, and redirect URI
$twitter = new TwitterOAuthV2($clientId, $clientSecret, $redirectUri);

// Generate authorization URL with PKCE
$authUrl = $twitter->getAuthorizationUrl(['scope' => 'tweet.read users.read']);

// After user authorization, exchange code for access token
$accessToken = $twitter->getAccessToken($code);

// Use access token for subsequent requests
$twitter->setAccessToken($accessToken);

// Make API request
$response = $twitter->get("users/me");
```

## API Request Methods

The library will provide methods for all HTTP verbs used by the Twitter API v2:

- `get($path, $parameters = [])`
- `post($path, $parameters = [])`
- `put($path, $parameters = [])`
- `delete($path, $parameters = [])`

## Error Handling

The library will use exceptions for error handling:

- `TwitterOAuthException` for library-specific errors
- HTTP error codes will be accessible via the Response object
- Detailed error messages from Twitter API will be parsed and made available

## Rate Limiting

The library will support Twitter API v2 rate limiting:

- Rate limit information will be accessible via the Response object
- Methods to check remaining rate limits
- Optional automatic retry with exponential backoff for rate limit errors

## Dependencies

- **GuzzleHttp/Guzzle**: For HTTP requests
- **Composer/CA-Bundle**: For SSL verification
- **PHPUnit**: For testing (dev dependency)

## Compatibility

The library will aim to maintain a similar interface to abraham/twitteroauth where possible, while adapting to the differences in OAuth 2.0 and Twitter API v2. This will make migration easier for existing users of abraham/twitteroauth.

## Implementation Plan

1. Set up project structure and composer.json
2. Implement core classes (Config, TwitterOAuthV2)
3. Implement Bearer Token authentication
4. Implement OAuth 2.0 Authorization Code Flow with PKCE
5. Implement API request methods
6. Add error handling and rate limiting support
7. Create examples and documentation
8. Write tests
9. Package for distribution
