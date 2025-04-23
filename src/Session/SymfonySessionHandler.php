<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SymfonySessionHandler implements SessionHandlerInterface
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function start(): void
    {
        $this->session->start();
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->session->set($key, $value);
    }

    public function remove(string $key): void
    {
        $this->session->remove($key);
    }

    public function clear(): void
    {
        $this->session->clear();
    }

    public function all(): array
    {
        return $this->session->all();
    }
} 