<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth;

interface SessionHandlerInterface extends \SessionHandlerInterface
{
    /**
     * Get a value from the session
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a value in the session
     */
    public function set(string $key, mixed $value): void;

    /**
     * Remove a value from the session
     */
    public function remove(string $key): void;

    /**
     * Check if a key exists in the session
     */
    public function has(string $key): bool;
} 