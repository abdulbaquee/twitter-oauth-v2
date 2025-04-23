<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth\Session;

interface SessionHandlerInterface
{
    /**
     * Start the session
     */
    public function start(): void;

    /**
     * Check if a session key exists
     */
    public function has(string $key): bool;

    /**
     * Get a session value
     */
    public function get(string $key, $default = null);

    /**
     * Set a session value
     */
    public function set(string $key, $value): void;

    /**
     * Remove a session value
     */
    public function remove(string $key): void;

    /**
     * Clear all session data
     */
    public function clear(): void;

    /**
     * Get all session data
     */
    public function all(): array;
} 