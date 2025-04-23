<?php

declare(strict_types=1);

namespace Abdulbaquee\TwitterOAuth\Session;

use Illuminate\Session\Store;

class LaravelSessionHandler implements SessionHandlerInterface
{
    private Store $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function start(): void
    {
        // Laravel handles session start automatically
    }

    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->store->put($key, $value);
    }

    public function remove(string $key): void
    {
        $this->store->forget($key);
    }

    public function clear(): void
    {
        $this->store->flush();
    }

    public function all(): array
    {
        return $this->store->all();
    }
} 