<?php
namespace Gerald\Framework\Http;

class Request
{
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

    /* ---------------- Core Info ---------------- */

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD']);
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'];
    }

    /* ---------------- Accessors ---------------- */

    public function getPost(string $key, $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function getQuery(string $key, $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getCookie(string $key, $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /* ---------------- Laravel-like DX ---------------- */

    // POST has higher priority than GET
    public function input(string $key, $default = null): mixed
    {
        return $this->post[$key]
            ?? $this->get[$key]
            ?? $default;
    }

    // Get all input data (GET + POST merged)
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    // Return raw JSON body (for APIs)
    public function getJson(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    /* ---------------- Helpers ---------------- */

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }
}
