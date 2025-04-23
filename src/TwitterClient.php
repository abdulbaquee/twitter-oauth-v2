<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class TwitterClient
{
    private const TWITTER_API_URL = 'https://api.twitter.com/2';
    private const TWITTER_UPLOAD_URL = 'https://upload.twitter.com/1.1/media/upload.json';

    private string $accessToken;
    private ClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        string $accessToken,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->accessToken = $accessToken;
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Get user details
     */
    public function getUser(string $userId): array
    {
        return $this->request('GET', "/users/{$userId}", [
            'user.fields' => 'id,name,username,profile_image_url'
        ]);
    }

    /**
     * Create a tweet
     */
    public function createTweet(string $text, ?array $mediaIds = null): array
    {
        $data = ['text' => $text];
        
        if ($mediaIds) {
            $data['media'] = ['media_ids' => $mediaIds];
        }

        return $this->request('POST', '/tweets', [], $data);
    }

    /**
     * Upload media file
     */
    public function uploadMedia(string $filePath, string $mediaType = 'image/jpeg'): array
    {
        $initResponse = $this->initMediaUpload($filePath, $mediaType);
        print_r($initResponse); exit;
        $mediaId = $initResponse['media_id_string'];

        $this->appendMediaChunks($mediaId, $filePath);
        return $this->finalizeMediaUpload($mediaId);
    }

    private function initMediaUpload(string $filePath, string $mediaType): array
    {
        $fileSize = filesize($filePath);
        if ($fileSize === false) {
            throw new RuntimeException('Could not determine file size');
        }

        return $this->request('POST', self::TWITTER_UPLOAD_URL, [
            'command' => 'INIT',
            'total_bytes' => $fileSize,
            'media_type' => $mediaType
        ]);
    }

    private function appendMediaChunks(string $mediaId, string $filePath): void
    {
        $chunkSize = 5 * 1024 * 1024; // 5MB chunks
        $file = fopen($filePath, 'rb');
        
        if ($file === false) {
            throw new RuntimeException('Could not open file for reading');
        }

        $segmentIndex = 0;
        while (!feof($file)) {
            $chunk = fread($file, $chunkSize);
            if ($chunk === false) {
                throw new RuntimeException('Error reading file chunk');
            }

            $this->request('POST', self::TWITTER_UPLOAD_URL, [
                'command' => 'APPEND',
                'media_id' => $mediaId,
                'segment_index' => $segmentIndex
            ], [], [
                'media' => $chunk
            ]);

            $segmentIndex++;
        }

        fclose($file);
    }

    private function finalizeMediaUpload(string $mediaId): array
    {
        return $this->request('POST', self::TWITTER_UPLOAD_URL, [
            'command' => 'FINALIZE',
            'media_id' => $mediaId
        ]);
    }

    private function request(
        string $method,
        string $endpoint,
        array $query = [],
        array $json = [],
        array $multipart = []
    ): array {
        $options = [
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}"
            ]
        ];

        if (!empty($query)) {
            $options['query'] = $query;
        }

        if (!empty($json)) {
            $options['json'] = $json;
        }

        if (!empty($multipart)) {
            $options['multipart'] = $multipart;
        }

        try {
            // Determine full URL: if endpoint is absolute, use it directly; otherwise prepend the API base URL
            $url = preg_match('#^https?://#i', $endpoint)
                ? $endpoint
                : self::TWITTER_API_URL . $endpoint;
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('Twitter API request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Twitter API request failed: ' . $e->getMessage());
        }
    }
} 