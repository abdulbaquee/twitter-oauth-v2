<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Abdulbaquee\TwitterOAuth\Session\SessionHandlerInterface;

class OAuthClient
{
    private const TWITTER_AUTH_URL = 'https://twitter.com/i/oauth2/authorize';
    private const TWITTER_TOKEN_URL = 'https://api.twitter.com/2/oauth2/token';
    private const TWITTER_REVOKE_URL = 'https://api.twitter.com/2/oauth2/revoke';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private array $scopes;
    private ClientInterface $httpClient;
    private string $codeVerifier;
    private string $codeChallenge;
    private SessionHandlerInterface $sessionHandler;
    private ?string $state = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        array $scopes = [],
        ?ClientInterface $httpClient = null,
        ?SessionHandlerInterface $sessionHandler = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes;
        $this->httpClient = $httpClient ?? new Client();
        $this->sessionHandler = $sessionHandler ?? new \Abdulbaquee\TwitterOAuth\Session\NativeSessionHandler();
    }

    /**
     * Generate authorization URL with PKCE support
     */
    public function getAuthorizationUrl(?string $state = null): string
    {
        // Generate new code verifier and challenge for each authorization request
        $this->generateCodeVerifier();
        $this->generateCodeChallenge();

        // Store the code verifier in the session
        $this->sessionHandler->set('twitter_code_verifier', $this->codeVerifier);

        // Generate state if not provided
        $this->state = $state ?? bin2hex(random_bytes(16));

        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'state' => $this->state,
            'code_challenge' => $this->codeChallenge,
            'code_challenge_method' => 'S256'
        ];

        return self::TWITTER_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        if (!$this->sessionHandler->has('twitter_code_verifier')) {
            throw new RuntimeException('Code verifier not found in session');
        }

        $response = $this->httpClient->post(self::TWITTER_TOKEN_URL, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUri,
                'code_verifier' => $this->sessionHandler->get('twitter_code_verifier')
            ],
            'auth' => [$this->clientId, $this->clientSecret],
            'http_errors' => false
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode((string)$response->getBody(), true);
        
        if ($statusCode >= 400) {
            throw new RuntimeException(
                "Failed to obtain access token: " . 
                ($data['error_description'] ?? $data['error'] ?? 'Unknown error')
            );
        }

        if (isset($data['access_token'])) {
            $this->storeTokens($data);
            // Clear the code verifier from session after successful token exchange
            $this->sessionHandler->remove('twitter_code_verifier');
            return $data;
        }

        throw new RuntimeException('Failed to obtain access token: Invalid response format');
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = $this->httpClient->post(self::TWITTER_TOKEN_URL, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId
            ],
            'auth' => [$this->clientId, $this->clientSecret]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        
        if (isset($data['access_token'])) {
            $this->storeTokens($data);
            return $data;
        }

        throw new RuntimeException('Failed to refresh access token: ' . ($data['error'] ?? 'Unknown error'));
    }

    /**
     * Revoke access token
     */
    public function revokeAccessToken(string $token): bool
    {
        $response = $this->httpClient->post(self::TWITTER_REVOKE_URL, [
            'form_params' => [
                'token' => $token,
                'client_id' => $this->clientId
            ],
            'auth' => [$this->clientId, $this->clientSecret]
        ]);

        return $response->getStatusCode() === 200;
    }

    private function generateCodeVerifier(): void
    {
        $this->codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(): void
    {
        $this->codeChallenge = rtrim(
            strtr(
                base64_encode(
                    hash('sha256', $this->codeVerifier, true)
                ),
                '+/',
                '-_'
            ),
            '='
        );
    }

    private function storeTokens(array $tokens): void
    {
        $this->sessionHandler->set('twitter_oauth_tokens', $tokens);
    }

    public function getStoredTokens(): ?array
    {
        $tokens = $this->sessionHandler->get('twitter_oauth_tokens');
        if (!$tokens) {
            return null;
        }

        // For Twitter API v2, we need to use the Bearer Token
        return [
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer'
        ];
    }

    public function getState(): ?string
    {
        return $this->state;
    }
} 