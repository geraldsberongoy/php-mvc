<?php

namespace Gerald\Framework\Http;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $key, $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function destroy(): void
    {
        session_destroy();
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
