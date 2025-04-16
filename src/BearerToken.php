<?php
/**
 * BearerToken class for handling app-only authentication.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

use Abdulbaquee\TwitterOAuthV2\Util\JsonDecoder;

/**
 * Handle Bearer Token (app-only) authentication
 */
class BearerToken
{
    /**
     * Bearer token value
     *
     * @var string|null
     */
    private ?string $token = null;

    /**
     * Create a new BearerToken
     *
     * @param string|null $token Bearer token value
     */
    public function __construct(?string $token = null)
    {
        $this->token = $token;
    }

    /**
     * Get the bearer token value
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set the bearer token value
     *
     * @param string $token Bearer token value
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Check if bearer token is set
     *
     * @return bool
     */
    public function hasToken(): bool
    {
        return $this->token !== null && $this->token !== '';
    }

    /**
     * Generate authorization header
     *
     * @return string|null
     */
    public function getAuthorizationHeader(): ?string
    {
        if (!$this->hasToken()) {
            return null;
        }

        return 'Bearer ' . $this->token;
    }

    /**
     * Create a bearer token from a response
     *
     * @param Response $response Response from token endpoint
     * @return self
     * @throws TwitterOAuthException
     */
    public static function fromResponse(Response $response): self
    {
        if (!$response->isSuccess()) {
            throw new TwitterOAuthException(
                'Failed to get bearer token: HTTP ' . $response->getHttpCode(),
                0,
                $response->getHttpCode(),
                $response->getDecodedBody()
            );
        }

        $data = $response->getDecodedBody();

        if (!isset($data['token_type']) || !isset($data['access_token'])) {
            throw new TwitterOAuthException('Invalid bearer token response');
        }

        if (strtolower($data['token_type']) !== 'bearer') {
            throw new TwitterOAuthException('Unexpected token type: ' . $data['token_type']);
        }

        return new self($data['access_token']);
    }
}
