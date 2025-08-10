<?php
namespace Gerald\Framework\Http;

class Request
{
    // Request class implementation

    // Single instance Request Class
    private static $instance = null;

    private function __construct(
        private array $server,
        private array $get,
        private array $post,
        private array $files,
        private array $cookies,
        private array $env
    ) {
    }

    public static function create(): static
    {
        if (self::$instance === null) {
            self::$instance = new static(
                $_SERVER,
                $_GET,
                $_POST,
                $_FILES,
                $_COOKIE,
                $_ENV
            );
        }

        return self::$instance;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'];
    }
}
