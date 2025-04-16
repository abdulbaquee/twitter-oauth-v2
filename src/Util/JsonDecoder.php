<?php
/**
 * Utility class for JSON decoding with error handling.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2\Util;

use Abdulbaquee\TwitterOAuthV2\TwitterOAuthException;

/**
 * JSON decoder utility class
 */
class JsonDecoder
{
    /**
     * Decode a JSON string to an object or array
     *
     * @param string $json The JSON string to decode
     * @param bool $asArray Whether to return as array instead of object
     * @return mixed The decoded JSON
     * @throws TwitterOAuthException If JSON cannot be decoded
     */
    public static function decode(string $json, bool $asArray = false)
    {
        $data = json_decode($json, $asArray);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TwitterOAuthException(
                'Failed to decode JSON: ' . json_last_error_msg(),
                json_last_error()
            );
        }
        
        return $data;
    }
}
