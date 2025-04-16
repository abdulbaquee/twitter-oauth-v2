<?php
/**
 * Main class for interacting with Twitter API v2.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

use Abdulbaquee\TwitterOAuthV2\Util\PKCE;
use Abdulbaquee\TwitterOAuthV2\Util\Util;

/**
 * TwitterOAuthV2 class for interacting with the Twitter API v2
 */
class TwitterOAuthV2 extends Config
{
    /**
     * Client ID (consumer key)
     *
     * @var string
     */
    private $clientId;

    /**
     * Client secret (consumer secret)
     *
     * @var string|null
     */
    private ?string $clientSecret;

    /**
     * Redirect URI for OAuth flow
     *
     * @var string|null
     */
    private ?string $redirectUri;

    /**
     * Bearer token for app-only authentication
     *
     * @var BearerToken|null
     */
    private ?BearerToken $bearerToken = null;

    /**
     * OAuth token for user authentication
     *
     * @var OAuthToken|null
     */
    private ?OAuthToken $oauthToken = null;

    /**
     * HTTP request handler
     *
     * @var Request
     */
    private $request;

    /**
     * Create a new TwitterOAuthV2 instance
     *
     * @param string $clientId Client ID (consumer key)
     * @param string|null $clientSecret Client secret (consumer secret)
     * @param string|null $redirectUri Redirect URI for OAuth flow
     * @param string|null $bearerToken Bearer token for app-only authentication
     * @param OAuthToken|null $oauthToken OAuth token for user authentication
     */
    public function __construct(
        string $clientId,
        ?string $clientSecret = null,
        ?string $redirectUri = null,
        ?string $bearerToken = null,
        ?OAuthToken $oauthToken = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;

        // Initialize request handler
        $this->request = new Request(
            $this->timeout,
            $this->connectionTimeout,
            $this->sslVerify,
            $this->userAgent,
            $this->proxy,
            $this->headers
        );

        // Set bearer token if provided
        if ($bearerToken !== null) {
            $this->bearerToken = new BearerToken($bearerToken);
        }

        // Set OAuth token if provided
        if ($oauthToken !== null) {
            $this->oauthToken = $oauthToken;
        }
    }

    /**
     * Get a bearer token for app-only authentication
     *
     * @return BearerToken
     * @throws TwitterOAuthException
     */
    public function getBearerToken(): BearerToken
    {
        if ($this->clientSecret === null) {
            throw new TwitterOAuthException('Client secret is required to get a bearer token');
        }

        // Prepare request
        $url = self::API_HOST . '/' . self::OAUTH2_TOKEN;
        $auth = base64_encode($this->clientId . ':' . $this->clientSecret);
        $headers = [
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $data = [
            'grant_type' => 'client_credentials',
        ];

        // Make request
        $response = $this->request->post($url, [], $data, $headers);

        // Create bearer token from response
        $this->bearerToken = BearerToken::fromResponse($response);

        return $this->bearerToken;
    }

    /**
     * Set bearer token for app-only authentication
     *
     * @param string|BearerToken $token Bearer token
     * @return self
     */
    public function setBearerToken($token): self
    {
        if ($token instanceof BearerToken) {
            $this->bearerToken = $token;
        } else {
            $this->bearerToken = new BearerToken($token);
        }

        return $this;
    }

    /**
     * Get OAuth 2.0 authorization URL
     *
     * @param array $options Authorization options
     * @return string
     * @throws TwitterOAuthException
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        if ($this->redirectUri === null) {
            throw new TwitterOAuthException('Redirect URI is required for authorization');
        }

        // Generate PKCE code verifier and challenge
        $codeVerifier = PKCE::generateCodeVerifier();
        $codeChallenge = PKCE::generateCodeChallenge($codeVerifier);

        // Generate state parameter for CSRF protection
        $state = PKCE::generateState();

        // Store code verifier and state in session for later verification
        // Note: In a real application, you would need to store these securely
        $_SESSION['oauth2_code_verifier'] = $codeVerifier;
        $_SESSION['oauth2_state'] = $state;

        // Prepare authorization parameters
        $params = array_merge([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], $options);

        // Build authorization URL
        return self::API_HOST . '/' . self::OAUTH2_AUTHORIZE . '?' . Util::buildHttpQuery($params);
    }

    /**
     * Get an access token using an authorization code
     *
     * @param string $code Authorization code
     * @param string|null $codeVerifier PKCE code verifier (if not provided, will use from session)
     * @param string|null $state State parameter (if not provided, will use from session)
     * @return OAuthToken
     * @throws TwitterOAuthException
     */
    public function getAccessToken(string $code, ?string $codeVerifier = null, ?string $state = null): OAuthToken
    {
        if ($this->clientSecret === null) {
            throw new TwitterOAuthException('Client secret is required to get an access token');
        }

        if ($this->redirectUri === null) {
            throw new TwitterOAuthException('Redirect URI is required to get an access token');
        }

        // Get code verifier from session if not provided
        if ($codeVerifier === null) {
            if (!isset($_SESSION['oauth2_code_verifier'])) {
                throw new TwitterOAuthException('Code verifier not found in session');
            }
            $codeVerifier = $_SESSION['oauth2_code_verifier'];
        }

        // Verify state parameter if provided
        if ($state !== null && isset($_SESSION['oauth2_state'])) {
            if ($state !== $_SESSION['oauth2_state']) {
                throw new TwitterOAuthException('Invalid state parameter');
            }
        }

        // Prepare request
        $url = self::API_HOST . '/' . self::OAUTH2_TOKEN;
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code_verifier' => $codeVerifier,
        ];

        // Make request
        $response = $this->request->post($url, [], $data, $headers);

        // Create OAuth token from response
        $this->oauthToken = OAuthToken::fromResponse($response);

        return $this->oauthToken;
    }

    /**
     * Refresh an access token using a refresh token
     *
     * @param string|null $refreshToken Refresh token (if not provided, will use from current token)
     * @return OAuthToken
     * @throws TwitterOAuthException
     */
    public function refreshAccessToken(?string $refreshToken = null): OAuthToken
    {
        if ($this->clientSecret === null) {
            throw new TwitterOAuthException('Client secret is required to refresh an access token');
        }

        // Get refresh token from current token if not provided
        if ($refreshToken === null) {
            if ($this->oauthToken === null || !$this->oauthToken->hasRefreshToken()) {
                throw new TwitterOAuthException('Refresh token not available');
            }
            $refreshToken = $this->oauthToken->getRefreshToken();
        }

        // Prepare request
        $url = self::API_HOST . '/' . self::OAUTH2_TOKEN;
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        // Make request
        $response = $this->request->post($url, [], $data, $headers);

        // Create OAuth token from response
        $this->oauthToken = OAuthToken::fromResponse($response);

        return $this->oauthToken;
    }

    /**
     * Set OAuth token for user authentication
     *
     * @param OAuthToken|array $token OAuth token or token data
     * @return self
     */
    public function setOAuthToken($token): self
    {
        if ($token instanceof OAuthToken) {
            $this->oauthToken = $token;
        } else {
            $this->oauthToken = new OAuthToken(
                $token['access_token'] ?? null,
                $token['refresh_token'] ?? null,
                $token['expires_in'] ?? null,
                $token['scope'] ?? []
            );
        }

        return $this;
    }

    /**
     * Make a GET request to the API
     *
     * @param string $path API endpoint path
     * @param array $parameters Query parameters
     * @return Response
     * @throws TwitterOAuthException
     */
    public function get(string $path, array $parameters = []): Response
    {
        return $this->request($path, 'GET', $parameters);
    }

    /**
     * Make a POST request to the API
     *
     * @param string $path API endpoint path
     * @param array $parameters Request parameters
     * @return Response
     * @throws TwitterOAuthException
     */
    public function post(string $path, array $parameters = []): Response
    {
        return $this->request($path, 'POST', $parameters);
    }

    /**
     * Make a PUT request to the API
     *
     * @param string $path API endpoint path
     * @param array $parameters Request parameters
     * @return Response
     * @throws TwitterOAuthException
     */
    public function put(string $path, array $parameters = []): Response
    {
        return $this->request($path, 'PUT', $parameters);
    }

    /**
     * Make a DELETE request to the API
     *
     * @param string $path API endpoint path
     * @param array $parameters Request parameters
     * @return Response
     * @throws TwitterOAuthException
     */
    public function delete(string $path, array $parameters = []): Response
    {
        return $this->request($path, 'DELETE', $parameters);
    }

    /**
     * Make a request to the API
     *
     * @param string $path API endpoint path
     * @param string $method HTTP method
     * @param array $parameters Request parameters
     * @return Response
     * @throws TwitterOAuthException
     */
    public function request(string $path, string $method, array $parameters = []): Response
    {
        // Build URL
        $url = $this->buildUrl($path);

        // Get authorization header
        $headers = $this->getRequestHeaders();

        // Make request based on method
        switch (strtoupper($method)) {
            case 'GET':
                return $this->request->get($url, $parameters, $headers);
            case 'POST':
                return $this->request->post($url, [], $parameters, $headers);
            case 'PUT':
                return $this->request->put($url, [], $parameters, $headers);
            case 'DELETE':
                return $this->request->delete($url, [], $parameters, $headers);
            default:
                throw new TwitterOAuthException('Unsupported HTTP method: ' . $method);
        }
    }

    /**
     * Build a URL for an API endpoint
     *
     * @param string $path API endpoint path
     * @return string
     */
    private function buildUrl(string $path): string
    {
        // Check if path already contains the API host
        if (strpos($path, 'https://') === 0) {
            return $path;
        }

        // Check if path already contains the API version
        if (strpos($path, self::API_VERSION . '/') === 0) {
            return self::API_HOST . '/' . $path;
        }

        // Add API version if not already included in path
        if (strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        return self::API_HOST . '/' . self::API_VERSION . $path;
    }

    /**
     * Get request headers including authorization
     *
     * @return array
     * @throws TwitterOAuthException
     */
    private function getRequestHeaders(): array
    {
        $headers = [];

        // Add authorization header
        $authHeader = $this->getAuthorizationHeader();
        if ($authHeader !== null) {
            $headers['Authorization'] = $authHeader;
        }

        return $headers;
    }

    /**
     * Get authorization header
     *
     * @return string|null
     * @throws TwitterOAuthException
     */
    private function getAuthorizationHeader(): ?string
    {
        // Use OAuth token if available
        if ($this->oauthToken !== null && $this->oauthToken->hasAccessToken()) {
            // Check if token is expired
            if ($this->oauthToken->isExpired()) {
                // Try to refresh token if possible
                if ($this->oauthToken->hasRefreshToken()) {
                    $this->refreshAccessToken();
                } else {
                    throw new TwitterOAuthException('OAuth token is expired and cannot be refreshed');
                }
            }
            
            return $this->oauthToken->getAuthorizationHeader();
        }

        // Use bearer token if available
        if ($this->bearerToken !== null && $this->bearerToken->hasToken()) {
            return $this->bearerToken->getAuthorizationHeader();
        }

        // No authorization available
        throw new TwitterOAuthException('No valid authentication method available');
    }
}
