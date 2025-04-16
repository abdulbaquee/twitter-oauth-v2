<?php
/**
 * Basic test file for TwitterOAuthV2 library.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;
use Abdulbaquee\TwitterOAuthV2\TwitterOAuthException;

// Test configuration
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Colors for console output
$green = "\033[32m";
$red = "\033[31m";
$reset = "\033[0m";

// Test counter
$testCount = 0;
$passCount = 0;

/**
 * Run a test and report result
 */
function runTest($name, $test) {
    global $testCount, $passCount, $green, $red, $reset;
    $testCount++;
    
    echo "Test #{$testCount}: {$name}... ";
    
    try {
        $result = $test();
        if ($result) {
            $passCount++;
            echo "{$green}PASS{$reset}\n";
        } else {
            echo "{$red}FAIL{$reset}\n";
        }
    } catch (Exception $e) {
        echo "{$red}ERROR: {$e->getMessage()}{$reset}\n";
    }
}

// Test 1: Class instantiation
runTest('Class instantiation', function() use ($clientId, $clientSecret) {
    $twitter = new TwitterOAuthV2($clientId, $clientSecret);
    return $twitter instanceof TwitterOAuthV2;
});

// Test 2: Config methods
runTest('Config methods', function() use ($clientId, $clientSecret) {
    $twitter = new TwitterOAuthV2($clientId, $clientSecret);
    $twitter->setTimeouts(20, 10);
    $twitter->setUserAgent('PHPUnitTest/1.0');
    $twitter->setSSLVerify(false);
    $twitter->setProxy('http://proxy.example.com');
    $twitter->setHeaders(['X-Custom-Header' => 'Test']);
    $twitter->setFormat('json');
    $twitter->setDecodeJson(true);
    return true;
});

// Test 3: Bearer Token creation
runTest('Bearer Token creation', function() {
    $token = new Abdulbaquee\TwitterOAuthV2\BearerToken('test_token');
    return $token->getToken() === 'test_token' && $token->hasToken() && $token->getAuthorizationHeader() === 'Bearer test_token';
});

// Test 4: OAuth Token creation
runTest('OAuth Token creation', function() {
    $token = new Abdulbaquee\TwitterOAuthV2\OAuthToken('access_token', 'refresh_token', 3600, ['tweet.read']);
    return $token->getAccessToken() === 'access_token' && 
           $token->getRefreshToken() === 'refresh_token' && 
           $token->hasAccessToken() && 
           $token->hasRefreshToken() && 
           !$token->isExpired() && 
           $token->getAuthorizationHeader() === 'Bearer access_token' &&
           $token->getScopes() === ['tweet.read'];
});

// Test 5: Request URL building (internal method test via reflection)
runTest('Request URL building', function() use ($clientId, $clientSecret) {
    $twitter = new TwitterOAuthV2($clientId, $clientSecret);
    $reflection = new ReflectionClass($twitter);
    $method = $reflection->getMethod('buildUrl');
    $method->setAccessible(true);
    
    $result1 = $method->invoke($twitter, 'tweets');
    $result2 = $method->invoke($twitter, '2/tweets');
    $result3 = $method->invoke($twitter, 'https://api.twitter.com/2/tweets');
    
    return $result1 === 'https://api.twitter.com/2/tweets' && 
           $result2 === 'https://api.twitter.com/2/tweets' && 
           $result3 === 'https://api.twitter.com/2/tweets';
});

// Test 6: TwitterAPI class instantiation
runTest('TwitterAPI class instantiation', function() use ($clientId, $clientSecret) {
    $twitter = new TwitterOAuthV2($clientId, $clientSecret);
    $api = new TwitterAPI($twitter);
    return $api instanceof TwitterAPI;
});

// Test 7: Exception handling
runTest('Exception handling', function() {
    $exception = new TwitterOAuthException('Test error', 123, 400, ['errors' => [['code' => 88, 'message' => 'Rate limit exceeded']]]);
    return $exception->getMessage() === 'Test error' && 
           $exception->getCode() === 123 && 
           $exception->getHttpCode() === 400 && 
           $exception->getErrorData()['errors'][0]['code'] === 88;
});

// Test 8: Response handling
runTest('Response handling', function() {
    $headers = [
        'content-type' => 'application/json',
        'x-rate-limit-limit' => '300',
        'x-rate-limit-remaining' => '299',
        'x-rate-limit-reset' => '1619123456'
    ];
    $body = '{"data":{"id":"123","text":"Test tweet"}}';
    
    $response = new Abdulbaquee\TwitterOAuthV2\Response(200, $body, $headers);
    
    return $response->getHttpCode() === 200 && 
           $response->getBody() === $body && 
           $response->isJson() && 
           $response->isSuccess() && 
           $response->getDecodedBody()['data']['id'] === '123' &&
           $response->getRateLimitInfo()['limit'] === 300 &&
           $response->getRateLimitInfo()['remaining'] === 299;
});

// Test 9: Utility classes
runTest('PKCE utility', function() {
    $codeVerifier = Abdulbaquee\TwitterOAuthV2\Util\PKCE::generateCodeVerifier();
    $codeChallenge = Abdulbaquee\TwitterOAuthV2\Util\PKCE::generateCodeChallenge($codeVerifier);
    $state = Abdulbaquee\TwitterOAuthV2\Util\PKCE::generateState();
    
    return strlen($codeVerifier) >= 43 && 
           strlen($codeVerifier) <= 128 && 
           strlen($codeChallenge) > 0 && 
           strlen($state) > 0;
});

runTest('JSON decoder utility', function() {
    $json = '{"test":true}';
    $decoded = Abdulbaquee\TwitterOAuthV2\Util\JsonDecoder::decode($json);
    
    return $decoded->test === true;
});

runTest('General utility functions', function() {
    $encoded = Abdulbaquee\TwitterOAuthV2\Util\Util::urlEncodeRfc3986('a+b=c~d');
    $query = Abdulbaquee\TwitterOAuthV2\Util\Util::buildHttpQuery(['a' => 'b', 'c' => 'd']);
    $parsed = Abdulbaquee\TwitterOAuthV2\Util\Util::parseQueryString('a=b&c=d');
    
    return $encoded === 'a%20b%3Dc~d' && 
           $query === 'a=b&c=d' && 
           $parsed['a'] === 'b' && 
           $parsed['c'] === 'd';
});

// Print summary
echo "\n{$green}Tests passed: {$passCount}/{$testCount}{$reset}\n";

if ($passCount === $testCount) {
    echo "{$green}All tests passed!{$reset}\n";
} else {
    echo "{$red}Some tests failed.{$reset}\n";
}
