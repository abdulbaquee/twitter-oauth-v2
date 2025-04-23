<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth\Examples;

use Abdulbaquee\TwitterOAuth\OAuthClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class BaseExample
{
    protected OAuthClient $client;
    protected Client $httpClient;
    protected array $config;

    public function __construct(
        OAuthClient $client,
        array $config = []
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->httpClient = new Client();
    }

    /**
     * Make a GET request to Twitter API
     */
    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->httpClient->get($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'query' => $params,
            ]);

            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new RuntimeException('GET request failed: ' . $e->getMessage());
        }
    }

    /**
     * Make a POST request to Twitter API
     */
    protected function post(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new RuntimeException('POST request failed: ' . $e->getMessage());
        }
    }

    /**
     * Make a DELETE request to Twitter API
     */
    protected function delete(string $endpoint): array
    {
        try {
            $response = $this->httpClient->delete($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
            ]);

            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new RuntimeException('DELETE request failed: ' . $e->getMessage());
        }
    }

    /**
     * Get access token from session
     */
    protected function getAccessToken(): string
    {
        $tokens = $this->client->getStoredTokens();
        if (!$tokens) {
            throw new RuntimeException('No access token found');
        }

        return $tokens['access_token'];
    }

    /**
     * Process API response
     */
    protected function processResponse($response): array
    {
        $data = json_decode((string)$response->getBody(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON response');
        }

        return $data;
    }

    /**
     * Get user information
     */
    public function getUserInfo(): array
    {
        return $this->get('https://api.twitter.com/2/users/me', [
            'user.fields' => 'id,name,username,profile_image_url',
        ]);
    }

    /**
     * Create a tweet
     */
    public function createTweet(string $text): array
    {
        return $this->post('https://api.twitter.com/2/tweets', [
            'text' => $text,
        ]);
    }

    /**
     * Delete a tweet
     */
    public function deleteTweet(string $tweetId): array
    {
        return $this->delete("https://api.twitter.com/2/tweets/{$tweetId}");
    }

    /**
     * Upload media
     */
    public function uploadMedia(string $filePath, string $mimeType): array
    {
        try {
            $response = $this->httpClient->post('https://upload.twitter.com/1.1/media/upload.json', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'multipart' => [
                    [
                        'name' => 'media',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                ],
            ]);

            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Media upload failed: ' . $e->getMessage());
        }
    }
} 