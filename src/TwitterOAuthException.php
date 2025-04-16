<?php
/**
 * Custom exception class for TwitterOAuthV2 library.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

use Exception;

/**
 * Exception thrown when an error occurs in TwitterOAuthV2
 */
class TwitterOAuthException extends Exception
{
    /**
     * HTTP status code
     *
     * @var int|null
     */
    protected $httpCode = null;

    /**
     * Error data from Twitter API
     *
     * @var array|null
     */
    protected $errorData = null;

    /**
     * Create a new TwitterOAuthException
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param int|null $httpCode HTTP status code
     * @param array|null $errorData Error data from Twitter API
     */
    public function __construct(string $message, int $code = 0, ?int $httpCode = null, ?array $errorData = null)
    {
        parent::__construct($message, $code);
        $this->httpCode = $httpCode;
        $this->errorData = $errorData;
    }

    /**
     * Get HTTP status code
     *
     * @return int|null
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    /**
     * Get error data from Twitter API
     *
     * @return array|null
     */
    public function getErrorData(): ?array
    {
        return $this->errorData;
    }
}
