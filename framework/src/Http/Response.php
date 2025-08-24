<?php
namespace Gerald\Framework\Http;

class Response
{
    public function __construct(
        private ?string $content = '',
        private int $status = 200,
        private array $headers = [],
    ) {
        http_response_code($status);
    }

    public function send(): void
    {
        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value), true);
        }

        // ensure status code set
        http_response_code($this->status);

        echo $this->content;
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }
}
