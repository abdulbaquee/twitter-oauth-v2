<?php
/**
 * Utility class for PKCE (Proof Key for Code Exchange) implementation.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuthV2\Util;

/**
 * PKCE utility class for OAuth 2.0 Authorization Code Flow
 */
class PKCE
{
    /**
     * Generate a code verifier for PKCE
     * 
     * @param int $length Length of the code verifier (between 43 and 128)
     * @return string Code verifier
     */
    public static function generateCodeVerifier(int $length = 64): string
    {
        // Ensure length is between 43 and 128 as per RFC 7636
        $length = max(43, min(128, $length));
        
        // Use random_bytes to generate cryptographically secure random bytes
        $random = random_bytes($length);
        
        // Base64 encode and make URL safe
        $codeVerifier = rtrim(strtr(base64_encode($random), '+/', '-_'), '=');
        
        // Ensure the length is correct after encoding
        return substr($codeVerifier, 0, $length);
    }
    
    /**
     * Generate a code challenge from a code verifier using S256 method
     * 
     * @param string $codeVerifier The code verifier
     * @return string Code challenge
     */
    public static function generateCodeChallenge(string $codeVerifier): string
    {
        // SHA-256 hash the code verifier
        $hash = hash('sha256', $codeVerifier, true);
        
        // Base64 encode and make URL safe
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
    
    /**
     * Generate a state parameter for CSRF protection
     * 
     * @param int $length Length of the state parameter
     * @return string State parameter
     */
    public static function generateState(int $length = 32): string
    {
        // Use random_bytes to generate cryptographically secure random bytes
        $random = random_bytes($length);
        
        // Base64 encode and make URL safe
        return rtrim(strtr(base64_encode($random), '+/', '-_'), '=');
    }
}
