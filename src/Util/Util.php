<?php
/**
 * Utility class for general utility functions.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2\Util;

/**
 * General utility functions
 */
class Util
{
    /**
     * URL encode a string according to RFC 3986
     *
     * @param string $value The string to encode
     * @return string The encoded string
     */
    public static function urlEncodeRfc3986(string $value): string
    {
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($value)));
    }

    /**
     * Build a query string from an array of parameters
     *
     * @param array $params The parameters
     * @return string The query string
     */
    public static function buildHttpQuery(array $params): string
    {
        if (empty($params)) {
            return '';
        }

        // Sort parameters by key
        ksort($params);

        $pairs = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                // Handle arrays
                sort($value);
                foreach ($value as $item) {
                    $pairs[] = self::urlEncodeRfc3986($key) . '=' . self::urlEncodeRfc3986((string)$item);
                }
            } else {
                $pairs[] = self::urlEncodeRfc3986($key) . '=' . self::urlEncodeRfc3986((string)$value);
            }
        }

        return implode('&', $pairs);
    }

    /**
     * Parse a query string into an array
     *
     * @param string $query The query string
     * @return array The parsed parameters
     */
    public static function parseQueryString(string $query): array
    {
        $params = [];
        
        if (empty($query)) {
            return $params;
        }
        
        foreach (explode('&', $query) as $pair) {
            $parts = explode('=', $pair, 2);
            $key = urldecode($parts[0]);
            $value = isset($parts[1]) ? urldecode($parts[1]) : '';
            
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = [$params[$key], $value];
                }
            } else {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
}
