<?php
/**
 * Request class for handling API requests.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Abdulbaquee\TwitterOAuthV2\Util\Util;

/**
 * Handle API requests to Twitter
 */
class Request
{
    /**
     * HTTP client
     *
     * @var Client
     */
    private $client;

    /**
     * Request options
     *
     * @var array
     */
    private $options;

    /**
     * Create a new Request
     *
     * @param int $timeout Request timeout
     * @param int $connectionTimeout Connection timeout
     * @param bool $sslVerify Verify SSL certificate
     * @param string $userAgent User agent
     * @param string|null $proxy Proxy
     * @param array $headers Additional headers
     */
    public function __construct(
        int $timeout = 10,
        int $connectionTimeout = 5,
        bool $sslVerify = true,
        string $userAgent = '',
        ?string $proxy = null,
        array $headers = []
    ) {
        // Set up Guzzle client
        $this->client = new Client();
        
        // Set up default request options
        $this->options = [
            'timeout' => $timeout,
            'connect_timeout' => $connectionTimeout,
            'verify' => $sslVerify,
            'headers' => array_merge([
                'User-Agent' => $userAgent,
                'Accept' => 'application/json',
            ], $headers),
        ];
        
        // Add proxy if provided
        if ($proxy !== null) {
            $this->options['proxy'] = $proxy;
        }
    }

    /**
     * Make a GET request
     *
     * @param string $url Request URL
     * @param array $params Query parameters
     * @param array $headers Additional headers
     * @return Response
     * @throws TwitterOAuthException
     */
    public function get(string $url, array $params = [], array $headers = []): Response
    {
        return $this->request('GET', $url, $params, [], $headers);
    }

    /**
     * Make a POST request
     *
     * @param string $url Request URL
     * @param array $params Query parameters
     * @param array $data POST data
     * @param array $headers Additional headers
     * @return Response
     * @throws TwitterOAuthException
     */
    public function post(string $url, array $params = [], array $data = [], array $headers = []): Response
    {
        return $this->request('POST', $url, $params, $data, $headers);
    }

    /**
     * Make a PUT request
     *
     * @param string $url Request URL
     * @param array $params Query parameters
     * @param array $data PUT data
     * @param array $headers Additional headers
     * @return Response
     * @throws TwitterOAuthException
     */
    public function put(string $url, array $params = [], array $data = [], array $headers = []): Response
    {
        return $this->request('PUT', $url, $params, $data, $headers);
    }

    /**
     * Make a DELETE request
     *
     * @param string $url Request URL
     * @param array $params Query parameters
     * @param array $data DELETE data
     * @param array $headers Additional headers
     * @return Response
     * @throws TwitterOAuthException
     */
    public function delete(string $url, array $params = [], array $data = [], array $headers = []): Response
    {
        return $this->request('DELETE', $url, $params, $data, $headers);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $params Query parameters
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return Response
     * @throws TwitterOAuthException
     */
    public function request(string $method, string $url, array $params = [], array $data = [], array $headers = []): Response
    {
        // Add query parameters to URL if provided
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . Util::buildHttpQuery($params);
        }
        
        // Set up request options
        $options = $this->options;
        
        // Add headers if provided
        if (!empty($headers)) {
            $options['headers'] = array_merge($options['headers'], $headers);
        }
        
        // Add request data if provided
        if (!empty($data)) {
            if (isset($options['headers']['Content-Type']) && $options['headers']['Content-Type'] === 'application/json') {
                $options['json'] = $data;
            } else {
                $options['form_params'] = $data;
            }
        }
        
        try {
            // Make the request
            $response = $this->client->request($method, $url, $options);
            
            // Get response data
            $body = (string) $response->getBody();
            $statusCode = $response->getStatusCode();
            $headers = $response->getHeaders();
            
            // Normalize header keys
            $normalizedHeaders = [];
            foreach ($headers as $key => $value) {
                $normalizedHeaders[strtolower($key)] = is_array($value) ? implode(', ', $value) : $value;
            }
            
            // Return response
            return new Response($statusCode, $body, $normalizedHeaders);
        } catch (GuzzleException $e) {
            // Handle Guzzle exceptions
            throw new TwitterOAuthException(
                'Request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e->hasResponse() ? $e->getResponse()->getStatusCode() : null
            );
        }
    }
}
