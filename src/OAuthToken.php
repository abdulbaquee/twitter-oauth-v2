<?php
/**
 * OAuthToken class for handling user authentication tokens.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

/**
 * Handle OAuth 2.0 tokens for user authentication
 */
class OAuthToken
{
    /**
     * Access token value
     *
     * @var string|null
     */
    private ?string $accessToken = null;

    /**
     * Refresh token value
     *
     * @var string|null
     */
    private ?string $refreshToken = null;

    /**
     * Token expiration timestamp
     *
     * @var int|null
     */
    private ?int $expiresAt = null;

    /**
     * Token scopes
     *
     * @var array
     */
    private $scopes = [];

    /**
     * Create a new OAuthToken
     *
     * @param string|null $accessToken Access token value
     * @param string|null $refreshToken Refresh token value
     * @param int|null $expiresIn Token expiration in seconds
     * @param array $scopes Token scopes
     */
    public function __construct(
        ?string $accessToken = null,
        ?string $refreshToken = null,
        ?int $expiresIn = null,
        array $scopes = []
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->scopes = $scopes;
        
        if ($expiresIn !== null) {
            $this->expiresAt = time() + $expiresIn;
        }
    }

    /**
     * Get the access token value
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Set the access token value
     *
     * @param string $accessToken Access token value
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get the refresh token value
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Set the refresh token value
     *
     * @param string $refreshToken Refresh token value
     * @return self
     */
    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Get the token expiration timestamp
     *
     * @return int|null
     */
    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    /**
     * Set the token expiration timestamp
     *
     * @param int $expiresAt Token expiration timestamp
     * @return self
     */
    public function setExpiresAt(int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    /**
     * Get the token scopes
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Set the token scopes
     *
     * @param array $scopes Token scopes
     * @return self
     */
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * Check if access token is set
     *
     * @return bool
     */
    public function hasAccessToken(): bool
    {
        return $this->accessToken !== null && $this->accessToken !== '';
    }

    /**
     * Check if refresh token is set
     *
     * @return bool
     */
    public function hasRefreshToken(): bool
    {
        return $this->refreshToken !== null && $this->refreshToken !== '';
    }

    /**
     * Check if token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= time();
    }

    /**
     * Generate authorization header
     *
     * @return string|null
     */
    public function getAuthorizationHeader(): ?string
    {
        if (!$this->hasAccessToken()) {
            return null;
        }

        return 'Bearer ' . $this->accessToken;
    }

    /**
     * Create an OAuth token from a response
     *
     * @param Response $response Response from token endpoint
     * @return self
     * @throws TwitterOAuthException
     */
    public static function fromResponse(Response $response): self
    {
        if (!$response->isSuccess()) {
            throw new TwitterOAuthException(
                'Failed to get OAuth token: HTTP ' . $response->getHttpCode(),
                0,
                $response->getHttpCode(),
                $response->getDecodedBody()
            );
        }

        $data = $response->getDecodedBody();

        if (!isset($data['token_type']) || !isset($data['access_token'])) {
            throw new TwitterOAuthException('Invalid OAuth token response');
        }

        if (strtolower($data['token_type']) !== 'bearer') {
            throw new TwitterOAuthException('Unexpected token type: ' . $data['token_type']);
        }

        $scopes = [];
        if (isset($data['scope'])) {
            $scopes = explode(' ', $data['scope']);
        }

        return new self(
            $data['access_token'],
            $data['refresh_token'] ?? null,
            $data['expires_in'] ?? null,
            $scopes
        );
    }
}
