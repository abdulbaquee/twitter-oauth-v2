<?php
/**
 * The configuration class for the TwitterOAuthV2 library.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

/**
 * Base configuration class for TwitterOAuthV2
 */
class Config
{
    /** @var string Default API URL */
    protected const API_HOST = 'https://api.twitter.com';
    
    /** @var string Default API version */
    protected const API_VERSION = '2';
    
    /** @var string OAuth 2.0 token endpoint */
    protected const OAUTH2_TOKEN = 'oauth2/token';
    
    /** @var string OAuth 2.0 authorization endpoint */
    protected const OAUTH2_AUTHORIZE = 'oauth2/authorize';
    
    /** @var string OAuth 2.0 token refresh endpoint */
    protected const OAUTH2_REFRESH = 'oauth2/refresh';
    
    /** @var string OAuth 2.0 token revoke endpoint */
    protected const OAUTH2_REVOKE = 'oauth2/revoke';
    
    /** @var int Default timeout */
    protected $timeout = 10;
    
    /** @var int Default connection timeout */
    protected $connectionTimeout = 5;
    
    /** @var bool Verify SSL certificate */
    protected $sslVerify = true;
    
    /** @var string Default user agent */
    protected $userAgent = 'TwitterOAuthV2 (+https://github.com/abdulbaquee/twitteroauth-v2)';
    
    /** @var string|null Default proxy */
    protected $proxy = null;
    
    /** @var array Default headers */
    protected $headers = [];
    
    /** @var string Default format */
    protected $format = 'json';
    
    /** @var bool Whether to decode JSON responses */
    protected $decodeJson = true;
    
    /**
     * Set timeout in seconds
     *
     * @param int $timeout
     * @return self
     */
    public function setTimeouts(int $timeout, int $connectionTimeout = 5): self
    {
        $this->timeout = $timeout;
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }
    
    /**
     * Set SSL verify
     *
     * @param bool $sslVerify
     * @return self
     */
    public function setSSLVerify(bool $sslVerify): self
    {
        $this->sslVerify = $sslVerify;
        return $this;
    }
    
    /**
     * Set user agent
     *
     * @param string $userAgent
     * @return self
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }
    
    /**
     * Set proxy
     *
     * @param string|null $proxy
     * @return self
     */
    public function setProxy(?string $proxy): self
    {
        $this->proxy = $proxy;
        return $this;
    }
    
    /**
     * Set additional headers
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }
    
    /**
     * Set response format
     *
     * @param string $format
     * @return self
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }
    
    /**
     * Set whether to decode JSON responses
     *
     * @param bool $decodeJson
     * @return self
     */
    public function setDecodeJson(bool $decodeJson): self
    {
        $this->decodeJson = $decodeJson;
        return $this;
    }
}
