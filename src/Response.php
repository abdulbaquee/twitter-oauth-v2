<?php
/**
 * Response class for handling API responses.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

use Abdulbaquee\TwitterOAuthV2\Util\JsonDecoder;

/**
 * Handle API responses from Twitter
 */
class Response
{
    /**
     * HTTP status code
     *
     * @var int
     */
    private $httpCode;

    /**
     * Response headers
     *
     * @var array
     */
    private $headers;

    /**
     * Response body
     *
     * @var string
     */
    private $body;

    /**
     * Decoded response body
     *
     * @var mixed
     */
    private $decodedBody;

    /**
     * Rate limit information
     *
     * @var array
     */
    private $rateLimitInfo = [
        'limit' => null,
        'remaining' => null,
        'reset' => null,
    ];

    /**
     * Create a new Response
     *
     * @param int $httpCode HTTP status code
     * @param string $body Response body
     * @param array $headers Response headers
     * @param bool $decodeJson Whether to decode JSON response
     */
    public function __construct(int $httpCode, string $body, array $headers = [], bool $decodeJson = true)
    {
        $this->httpCode = $httpCode;
        $this->body = $body;
        $this->headers = $headers;

        // Extract rate limit information from headers
        $this->extractRateLimitInfo();

        // Decode JSON response if requested
        if ($decodeJson && $this->isJson()) {
            $this->decodedBody = JsonDecoder::decode($body, true);
        }
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get decoded response body
     *
     * @return mixed
     */
    public function getDecodedBody()
    {
        return $this->decodedBody ?? $this->body;
    }

    /**
     * Check if response is JSON
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return isset($this->headers['content-type']) &&
            strpos($this->headers['content-type'], 'application/json') !== false;
    }

    /**
     * Check if response is successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->httpCode >= 200 && $this->httpCode < 300;
    }

    /**
     * Get rate limit information
     *
     * @return array
     */
    public function getRateLimitInfo(): array
    {
        return $this->rateLimitInfo;
    }

    /**
     * Extract rate limit information from headers
     */
    private function extractRateLimitInfo(): void
    {
        $headers = array_change_key_case($this->headers, CASE_LOWER);

        if (isset($headers['x-rate-limit-limit'])) {
            $this->rateLimitInfo['limit'] = (int) $headers['x-rate-limit-limit'];
        }

        if (isset($headers['x-rate-limit-remaining'])) {
            $this->rateLimitInfo['remaining'] = (int) $headers['x-rate-limit-remaining'];
        }

        if (isset($headers['x-rate-limit-reset'])) {
            $this->rateLimitInfo['reset'] = (int) $headers['x-rate-limit-reset'];
        }
    }
}
