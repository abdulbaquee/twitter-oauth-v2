<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth\Session;

class NativeSessionHandler implements SessionHandlerInterface
{
    private bool $started = false;

    public function start(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
        }
    }

    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    public function get(string $key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }

    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }
}