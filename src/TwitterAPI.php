<?php
/**
 * Example implementation of Twitter API v2 endpoints.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2;

/**
 * API endpoints for Twitter API v2
 */
class TwitterAPI
{
    /**
     * TwitterOAuthV2 instance
     *
     * @var TwitterOAuthV2
     */
    private TwitterOAuthV2 $client;

    /**
     * Create a new TwitterAPI instance
     *
     * @param TwitterOAuthV2 $client TwitterOAuthV2 instance
     */
    public function __construct(TwitterOAuthV2 $client)
    {
        $this->client = $client;
    }

    /**
     * Get a user by username
     *
     * @param string $username Twitter username
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getUserByUsername(string $username, array $fields = []): Response
    {
        $params = ['usernames' => $username];
        
        if (!empty($fields['user.fields'])) {
            $params['user.fields'] = implode(',', $fields['user.fields']);
        }
        
        return $this->client->get('users/by', $params);
    }

    /**
     * Get a user by ID
     *
     * @param string $id Twitter user ID
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getUserById(string $id, array $fields = []): Response
    {
        $params = [];
        
        if (!empty($fields['user.fields'])) {
            $params['user.fields'] = implode(',', $fields['user.fields']);
        }
        
        return $this->client->get("users/{$id}", $params);
    }

    /**
     * Get the authenticated user
     *
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getMe(array $fields = []): Response
    {
        $params = [];
        
        if (!empty($fields['user.fields'])) {
            $params['user.fields'] = implode(',', $fields['user.fields']);
        }
        
        return $this->client->get('users/me', $params);
    }

    /**
     * Get a tweet by ID
     *
     * @param string $id Tweet ID
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getTweet(string $id, array $fields = []): Response
    {
        $params = [];
        
        if (!empty($fields['tweet.fields'])) {
            $params['tweet.fields'] = implode(',', $fields['tweet.fields']);
        }
        
        if (!empty($fields['expansions'])) {
            $params['expansions'] = implode(',', $fields['expansions']);
        }
        
        if (!empty($fields['media.fields'])) {
            $params['media.fields'] = implode(',', $fields['media.fields']);
        }
        
        if (!empty($fields['user.fields'])) {
            $params['user.fields'] = implode(',', $fields['user.fields']);
        }
        
        return $this->client->get("tweets/{$id}", $params);
    }

    /**
     * Get multiple tweets by IDs
     *
     * @param array $ids Tweet IDs
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getTweets(array $ids, array $fields = []): Response
    {
        $params = ['ids' => implode(',', $ids)];
        
        if (!empty($fields['tweet.fields'])) {
            $params['tweet.fields'] = implode(',', $fields['tweet.fields']);
        }
        
        if (!empty($fields['expansions'])) {
            $params['expansions'] = implode(',', $fields['expansions']);
        }
        
        if (!empty($fields['media.fields'])) {
            $params['media.fields'] = implode(',', $fields['media.fields']);
        }
        
        if (!empty($fields['user.fields'])) {
            $params['user.fields'] = implode(',', $fields['user.fields']);
        }
        
        return $this->client->get('tweets', $params);
    }

    /**
     * Search for tweets
     *
     * @param string $query Search query
     * @param array $options Search options
     * @return Response
     */
    public function searchTweets(string $query, array $options = []): Response
    {
        $params = array_merge(['query' => $query], $options);
        
        return $this->client->get('tweets/search/recent', $params);
    }

    /**
     * Get a user's timeline
     *
     * @param string $userId User ID
     * @param array $options Timeline options
     * @return Response
     */
    public function getUserTimeline(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/tweets", $options);
    }

    /**
     * Post a tweet
     *
     * @param string $text Tweet text
     * @param array $options Tweet options
     * @return Response
     */
    public function postTweet(string $text, array $options = []): Response
    {
        $params = array_merge(['text' => $text], $options);
        
        return $this->client->post('tweets', $params);
    }

    /**
     * Delete a tweet
     *
     * @param string $id Tweet ID
     * @return Response
     */
    public function deleteTweet(string $id): Response
    {
        return $this->client->delete("tweets/{$id}");
    }

    /**
     * Get a user's followers
     *
     * @param string $userId User ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getFollowers(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/followers", $options);
    }

    /**
     * Get users a user is following
     *
     * @param string $userId User ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getFollowing(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/following", $options);
    }

    /**
     * Follow a user
     *
     * @param string $userId User ID of authenticated user
     * @param string $targetUserId User ID to follow
     * @return Response
     */
    public function followUser(string $userId, string $targetUserId): Response
    {
        return $this->client->post("users/{$userId}/following", ['target_user_id' => $targetUserId]);
    }

    /**
     * Unfollow a user
     *
     * @param string $userId User ID of authenticated user
     * @param string $targetUserId User ID to unfollow
     * @return Response
     */
    public function unfollowUser(string $userId, string $targetUserId): Response
    {
        return $this->client->delete("users/{$userId}/following/{$targetUserId}");
    }

    /**
     * Like a tweet
     *
     * @param string $userId User ID
     * @param string $tweetId Tweet ID
     * @return Response
     */
    public function likeTweet(string $userId, string $tweetId): Response
    {
        return $this->client->post("users/{$userId}/likes", ['tweet_id' => $tweetId]);
    }

    /**
     * Unlike a tweet
     *
     * @param string $userId User ID
     * @param string $tweetId Tweet ID
     * @return Response
     */
    public function unlikeTweet(string $userId, string $tweetId): Response
    {
        return $this->client->delete("users/{$userId}/likes/{$tweetId}");
    }

    /**
     * Retweet a tweet
     *
     * @param string $userId User ID
     * @param string $tweetId Tweet ID
     * @return Response
     */
    public function retweet(string $userId, string $tweetId): Response
    {
        return $this->client->post("users/{$userId}/retweets", ['tweet_id' => $tweetId]);
    }

    /**
     * Undo a retweet
     *
     * @param string $userId User ID
     * @param string $tweetId Tweet ID
     * @return Response
     */
    public function unretweet(string $userId, string $tweetId): Response
    {
        return $this->client->delete("users/{$userId}/retweets/{$tweetId}");
    }

    /**
     * Get a user's liked tweets
     *
     * @param string $userId User ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getLikedTweets(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/liked_tweets", $options);
    }

    /**
     * Get users who liked a tweet
     *
     * @param string $tweetId Tweet ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getLikingUsers(string $tweetId, array $options = []): Response
    {
        return $this->client->get("tweets/{$tweetId}/liking_users", $options);
    }

    /**
     * Get users who retweeted a tweet
     *
     * @param string $tweetId Tweet ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getRetweetedBy(string $tweetId, array $options = []): Response
    {
        return $this->client->get("tweets/{$tweetId}/retweeted_by", $options);
    }

    /**
     * Get tweet counts for a search query
     *
     * @param string $query Search query
     * @param array $options Count options
     * @return Response
     */
    public function getTweetCounts(string $query, array $options = []): Response
    {
        $params = array_merge(['query' => $query], $options);
        
        return $this->client->get('tweets/counts/recent', $params);
    }

    /**
     * Get a list by ID
     *
     * @param string $id List ID
     * @param array $fields Optional fields to return
     * @return Response
     */
    public function getList(string $id, array $fields = []): Response
    {
        $params = [];
        
        if (!empty($fields['list.fields'])) {
            $params['list.fields'] = implode(',', $fields['list.fields']);
        }
        
        return $this->client->get("lists/{$id}", $params);
    }

    /**
     * Get a user's lists
     *
     * @param string $userId User ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getUserLists(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/owned_lists", $options);
    }

    /**
     * Create a list
     *
     * @param string $name List name
     * @param array $options List options
     * @return Response
     */
    public function createList(string $name, array $options = []): Response
    {
        $params = array_merge(['name' => $name], $options);
        
        return $this->client->post('lists', $params);
    }

    /**
     * Update a list
     *
     * @param string $id List ID
     * @param array $options List options to update
     * @return Response
     */
    public function updateList(string $id, array $options): Response
    {
        return $this->client->put("lists/{$id}", $options);
    }

    /**
     * Delete a list
     *
     * @param string $id List ID
     * @return Response
     */
    public function deleteList(string $id): Response
    {
        return $this->client->delete("lists/{$id}");
    }

    /**
     * Get list members
     *
     * @param string $id List ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getListMembers(string $id, array $options = []): Response
    {
        return $this->client->get("lists/{$id}/members", $options);
    }

    /**
     * Add a member to a list
     *
     * @param string $listId List ID
     * @param string $userId User ID to add
     * @return Response
     */
    public function addListMember(string $listId, string $userId): Response
    {
        return $this->client->post("lists/{$listId}/members", ['user_id' => $userId]);
    }

    /**
     * Remove a member from a list
     *
     * @param string $listId List ID
     * @param string $userId User ID to remove
     * @return Response
     */
    public function removeListMember(string $listId, string $userId): Response
    {
        return $this->client->delete("lists/{$listId}/members/{$userId}");
    }

    /**
     * Get a user's pinned lists
     *
     * @param string $userId User ID
     * @param array $options Pagination options
     * @return Response
     */
    public function getPinnedLists(string $userId, array $options = []): Response
    {
        return $this->client->get("users/{$userId}/pinned_lists", $options);
    }

    /**
     * Pin a list
     *
     * @param string $userId User ID
     * @param string $listId List ID
     * @return Response
     */
    public function pinList(string $userId, string $listId): Response
    {
        return $this->client->post("users/{$userId}/pinned_lists", ['list_id' => $listId]);
    }

    /**
     * Unpin a list
     *
     * @param string $userId User ID
     * @param string $listId List ID
     * @return Response
     */
    public function unpinList(string $userId, string $listId): Response
    {
        return $this->client->delete("users/{$userId}/pinned_lists/{$listId}");
    }
}
