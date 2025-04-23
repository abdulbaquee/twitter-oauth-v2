<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class TwitterApi
{
    private const BASE_URL = 'https://api.twitter.com/2';
    private Client $httpClient;
    private string $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->httpClient = new Client();
    }

    /**
     * Make a GET request to Twitter API
     */
    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $response = $this->httpClient->get(self::BASE_URL . $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => $params,
                'http_errors' => false // Don't throw exceptions for 4xx/5xx responses
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                $errorBody = json_decode((string)$response->getBody(), true);
                throw new RuntimeException(
                    "API request failed with status {$statusCode}: " . 
                    ($errorBody['detail'] ?? 'Unknown error')
                );
            }

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
            $response = $this->httpClient->post(self::BASE_URL . $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
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
            $response = $this->httpClient->delete(self::BASE_URL . $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new RuntimeException('DELETE request failed: ' . $e->getMessage());
        }
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

    // GET Operations

    /**
     * Get user profile
     */
    public function getUserProfile(string $userId = 'me', array $fields = ['id', 'name', 'username', 'profile_image_url']): array
    {
        return $this->get("/users/{$userId}", [
            'user.fields' => implode(',', $fields)
        ]);
    }

    /**
     * Get user tweets
     */
    public function getUserTweets(string $userId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'tweet.fields' => 'created_at,public_metrics,entities',
            'expansions' => 'author_id'
        ];

        return $this->get("/users/{$userId}/tweets", array_merge($defaultParams, $params));
    }

    /**
     * Get tweet details
     */
    public function getTweet(string $tweetId, array $fields = ['created_at', 'public_metrics', 'entities']): array
    {
        return $this->get("/tweets/{$tweetId}", [
            'tweet.fields' => implode(',', $fields),
            'expansions' => 'author_id'
        ]);
    }

    /**
     * Get user followers
     */
    public function getUserFollowers(string $userId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'user.fields' => 'id,name,username,profile_image_url'
        ];

        return $this->get("/users/{$userId}/followers", array_merge($defaultParams, $params));
    }

    /**
     * Get user following
     */
    public function getUserFollowing(string $userId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'user.fields' => 'id,name,username,profile_image_url'
        ];

        return $this->get("/users/{$userId}/following", array_merge($defaultParams, $params));
    }

    /**
     * Get user mentions
     */
    public function getUserMentions(string $userId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'tweet.fields' => 'created_at,public_metrics,entities',
            'expansions' => 'author_id'
        ];

        return $this->get("/users/{$userId}/mentions", array_merge($defaultParams, $params));
    }

    /**
     * Get user likes
     */
    public function getUserLikes(string $userId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'tweet.fields' => 'created_at,public_metrics,entities',
            'expansions' => 'author_id'
        ];

        return $this->get("/users/{$userId}/liked_tweets", array_merge($defaultParams, $params));
    }

    /**
     * Get user lists
     */
    public function getLists(string $userId): array
    {
        $endpoint = "/users/{$userId}/owned_lists";
        $params = [
            'max_results' => 10,
            'list.fields' => 'created_at,description,member_count'
        ];
        return $this->get($endpoint, $params);
    }

    /**
     * Get list members
     */
    public function getListMembers(string $listId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'user.fields' => 'id,name,username,profile_image_url'
        ];

        return $this->get("/lists/{$listId}/members", array_merge($defaultParams, $params));
    }

    /**
     * Get list tweets
     */
    public function getListTweets(string $listId, array $params = []): array
    {
        $defaultParams = [
            'max_results' => 10,
            'tweet.fields' => 'created_at,public_metrics,entities',
            'expansions' => 'author_id'
        ];

        return $this->get("/lists/{$listId}/tweets", array_merge($defaultParams, $params));
    }

    /**
     * Get user relationships (followers and following)
     */
    public function getUserRelationships(string $userId): array
    {
        $endpoint = "/users/{$userId}/following";
        $params = [
            'user.fields' => 'profile_image_url,description',
            'max_results' => 100
        ];
        return $this->get($endpoint, $params);
    }

    // POST Operations

    /**
     * Create tweet
     */
    public function createTweet(string $text, ?array $mediaIds = null): array
    {
        $data = ['text' => $text];
        if ($mediaIds) {
            $data['media'] = ['media_ids' => $mediaIds];
        }

        return $this->post('/tweets', $data);
    }

    /**
     * Like tweet
     */
    public function likeTweet(string $userId, string $tweetId): array
    {
        $endpoint = "/users/{$userId}/likes";
        $data = [
            'tweet_id' => $tweetId
        ];
        return $this->post($endpoint, $data);
    }

    /**
     * Retweet
     */
    public function retweet(string $userId, string $tweetId): array
    {
        $endpoint = "/users/{$userId}/retweets";
        $data = [
            'tweet_id' => $tweetId
        ];
        return $this->post($endpoint, $data);
    }

    /**
     * Reply to tweet
     */
    public function replyToTweet(string $text, string $tweetId): array
    {
        return $this->post('/tweets', [
            'text' => $text,
            'reply' => [
                'in_reply_to_tweet_id' => $tweetId
            ]
        ]);
    }

    /**
     * Create list
     */
    public function createList(string $name, string $description = '', bool $private = false): array
    {
        return $this->post('/lists', [
            'name' => $name,
            'description' => $description,
            'private' => $private
        ]);
    }

    /**
     * Add member to list
     */
    public function addListMember(string $listId, string $userId): array
    {
        return $this->post("/lists/{$listId}/members", [
            'user_id' => $userId
        ]);
    }

    /**
     * Follow user
     */
    public function followUser(string $sourceUserId, string $targetUserId): array
    {
        $endpoint = "/users/{$sourceUserId}/following";
        $data = [
            'target_user_id' => $targetUserId
        ];
        return $this->post($endpoint, $data);
    }

    /**
     * Block user
     */
    public function blockUser(string $sourceUserId, string $targetUserId): array
    {
        $endpoint = "/users/{$sourceUserId}/blocking";
        $data = [
            'target_user_id' => $targetUserId
        ];
        return $this->post($endpoint, $data);
    }

    /**
     * Mute user
     */
    public function muteUser(string $sourceUserId, string $targetUserId): array
    {
        return $this->post("/users/{$sourceUserId}/muting", [
            'target_user_id' => $targetUserId
        ]);
    }

    // DELETE Operations

    /**
     * Delete tweet
     */
    public function deleteTweet(string $tweetId): array
    {
        return $this->delete("/tweets/{$tweetId}");
    }

    /**
     * Unlike tweet
     */
    public function unlikeTweet(string $userId, string $tweetId): array
    {
        $endpoint = "/users/{$userId}/likes/{$tweetId}";
        return $this->delete($endpoint);
    }

    /**
     * Remove retweet
     */
    public function removeRetweet(string $userId, string $tweetId): array
    {
        return $this->delete("/users/{$userId}/retweets/{$tweetId}");
    }

    /**
     * Delete list
     */
    public function deleteList(string $listId): array
    {
        return $this->delete("/lists/{$listId}");
    }

    /**
     * Remove list member
     */
    public function removeListMember(string $listId, string $userId): array
    {
        return $this->delete("/lists/{$listId}/members/{$userId}");
    }

    /**
     * Unfollow user
     */
    public function unfollowUser(string $sourceUserId, string $targetUserId): array
    {
        $endpoint = "/users/{$sourceUserId}/following/{$targetUserId}";
        return $this->delete($endpoint);
    }

    /**
     * Unblock user
     */
    public function unblockUser(string $sourceUserId, string $targetUserId): array
    {
        $endpoint = "/users/{$sourceUserId}/blocking/{$targetUserId}";
        return $this->delete($endpoint);
    }

    /**
     * Unmute user
     */
    public function unmuteUser(string $sourceUserId, string $targetUserId): array
    {
        return $this->delete("/users/{$sourceUserId}/muting/{$targetUserId}");
    }

    /**
     * Get user interactions (likes and retweets)
     */
    public function getUserInteractions(string $userId): array
    {
        $endpoint = "/users/{$userId}/tweets";
        $params = [
            'tweet.fields' => 'created_at,public_metrics,entities',
            'expansions' => 'author_id',
            'max_results' => 10
        ];
        return $this->get($endpoint, $params);
    }
} 