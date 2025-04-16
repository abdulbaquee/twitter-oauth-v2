<?php
/**
 * Example of list management using Twitter API v2.
 *
 * @license MIT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthV2;
use Abdulbaquee\TwitterOAuthV2\TwitterAPI;

// Your Twitter API credentials
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';
$accessToken = 'YOUR_ACCESS_TOKEN';
$refreshToken = 'YOUR_REFRESH_TOKEN';

// Initialize TwitterOAuthV2 with OAuth token
$twitter = new TwitterOAuthV2($clientId, $clientSecret);
$twitter->setOAuthToken([
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken
]);

// Initialize the TwitterAPI wrapper
$api = new TwitterAPI($twitter);

// Your user ID (the authenticated user)
$userId = 'YOUR_USER_ID';

// Example 1: Create a new list
try {
    echo "Creating a new list\n";
    echo "==================\n\n";
    
    $response = $api->createList('My Twitter API List', [
        'description' => 'A list created using the Twitter API v2'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['id'])) {
        $listId = $data['data']['id'];
        echo "Successfully created list!\n";
        echo "List ID: {$listId}\n";
        echo "Name: {$data['data']['name']}\n";
        
        if (isset($data['data']['description'])) {
            echo "Description: {$data['data']['description']}\n";
        }
        
        echo "\n";
    } else {
        echo "Failed to create list: " . print_r($data, true) . "\n\n";
        // Use a sample list ID for the rest of the examples
        $listId = 'SAMPLE_LIST_ID';
    }
} catch (Exception $e) {
    echo "Error creating list: " . $e->getMessage() . "\n\n";
    // Use a sample list ID for the rest of the examples
    $listId = 'SAMPLE_LIST_ID';
}

// Example 2: Get a list by ID
try {
    echo "Getting list details\n";
    echo "===================\n\n";
    
    $response = $api->getList($listId, [
        'list.fields' => ['created_at', 'follower_count', 'member_count', 'private', 'description']
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data'])) {
        $list = $data['data'];
        echo "List ID: {$list['id']}\n";
        echo "Name: {$list['name']}\n";
        
        if (isset($list['description'])) {
            echo "Description: {$list['description']}\n";
        }
        
        echo "Created at: {$list['created_at']}\n";
        echo "Private: " . ($list['private'] ? 'Yes' : 'No') . "\n";
        echo "Follower count: {$list['follower_count']}\n";
        echo "Member count: {$list['member_count']}\n";
        echo "\n";
    } else {
        echo "Failed to get list or list does not exist: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error getting list: " . $e->getMessage() . "\n\n";
}

// Example 3: Get a user's lists
try {
    echo "Getting user's lists\n";
    echo "==================\n\n";
    
    $response = $api->getUserLists($userId, [
        'max_results' => 5,
        'list.fields' => ['created_at', 'follower_count', 'member_count', 'private', 'description']
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $list) {
            echo "List ID: {$list['id']}\n";
            echo "Name: {$list['name']}\n";
            
            if (isset($list['description'])) {
                echo "Description: {$list['description']}\n";
            }
            
            echo "Created at: {$list['created_at']}\n";
            echo "Private: " . ($list['private'] ? 'Yes' : 'No') . "\n";
            echo "Follower count: {$list['follower_count']}\n";
            echo "Member count: {$list['member_count']}\n";
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No lists found or user does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting user lists: " . $e->getMessage() . "\n\n";
}

// Example 4: Update a list
try {
    echo "Updating list\n";
    echo "============\n\n";
    
    $response = $api->updateList($listId, [
        'name' => 'Updated Twitter API List',
        'description' => 'This list was updated using the Twitter API v2',
        'private' => true
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['updated']) && $data['data']['updated'] === true) {
        echo "Successfully updated list!\n\n";
    } else {
        echo "Failed to update list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error updating list: " . $e->getMessage() . "\n\n";
}

// Example 5: Add a member to a list
try {
    echo "Adding a member to the list\n";
    echo "=========================\n\n";
    
    // Target user ID to add to the list
    $targetUserId = 'TARGET_USER_ID';
    
    $response = $api->addListMember($listId, $targetUserId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['is_member']) && $data['data']['is_member'] === true) {
        echo "Successfully added user to the list!\n\n";
    } else {
        echo "Failed to add user to the list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error adding list member: " . $e->getMessage() . "\n\n";
}

// Example 6: Get list members
try {
    echo "Getting list members\n";
    echo "==================\n\n";
    
    $response = $api->getListMembers($listId, [
        'max_results' => 5,
        'user.fields' => 'name,username,profile_image_url,verified'
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $member) {
            echo "Member: {$member['name']} (@{$member['username']})\n";
            echo "ID: {$member['id']}\n";
            echo "Verified: " . ($member['verified'] ? 'Yes' : 'No') . "\n";
            if (isset($member['profile_image_url'])) {
                echo "Profile Image: {$member['profile_image_url']}\n";
            }
            echo "----------------------------\n\n";
        }
        
        // Display pagination token if available
        if (isset($data['meta']['next_token'])) {
            echo "Next pagination token: {$data['meta']['next_token']}\n\n";
        }
    } else {
        echo "No list members found or list does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting list members: " . $e->getMessage() . "\n\n";
}

// Example 7: Remove a member from a list
try {
    echo "Removing a member from the list\n";
    echo "=============================\n\n";
    
    // Target user ID to remove from the list
    $targetUserId = 'TARGET_USER_ID';
    
    $response = $api->removeListMember($listId, $targetUserId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['is_member']) && $data['data']['is_member'] === false) {
        echo "Successfully removed user from the list!\n\n";
    } else {
        echo "Failed to remove user from the list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error removing list member: " . $e->getMessage() . "\n\n";
}

// Example 8: Pin a list
try {
    echo "Pinning list\n";
    echo "===========\n\n";
    
    $response = $api->pinList($userId, $listId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['pinned']) && $data['data']['pinned'] === true) {
        echo "Successfully pinned the list!\n\n";
    } else {
        echo "Failed to pin the list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error pinning list: " . $e->getMessage() . "\n\n";
}

// Example 9: Get pinned lists
try {
    echo "Getting pinned lists\n";
    echo "==================\n\n";
    
    $response = $api->getPinnedLists($userId, [
        'list.fields' => ['created_at', 'follower_count', 'member_count', 'description']
    ]);
    
    $data = $response->getDecodedBody();
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $list) {
            echo "List ID: {$list['id']}\n";
            echo "Name: {$list['name']}\n";
            
            if (isset($list['description'])) {
                echo "Description: {$list['description']}\n";
            }
            
            echo "Created at: {$list['created_at']}\n";
            echo "Follower count: {$list['follower_count']}\n";
            echo "Member count: {$list['member_count']}\n";
            echo "----------------------------\n\n";
        }
    } else {
        echo "No pinned lists found or user does not exist.\n\n";
    }
} catch (Exception $e) {
    echo "Error getting pinned lists: " . $e->getMessage() . "\n\n";
}

// Example 10: Unpin a list
try {
    echo "Unpinning list\n";
    echo "=============\n\n";
    
    $response = $api->unpinList($userId, $listId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['pinned']) && $data['data']['pinned'] === false) {
        echo "Successfully unpinned the list!\n\n";
    } else {
        echo "Failed to unpin the list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error unpinning list: " . $e->getMessage() . "\n\n";
}

// Example 11: Delete a list
try {
    echo "Deleting list\n";
    echo "============\n\n";
    
    $response = $api->deleteList($listId);
    $data = $response->getDecodedBody();
    
    if (isset($data['data']['deleted']) && $data['data']['deleted'] === true) {
        echo "Successfully deleted the list!\n\n";
    } else {
        echo "Failed to delete the list: " . print_r($data, true) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error deleting list: " . $e->getMessage() . "\n\n";
}
