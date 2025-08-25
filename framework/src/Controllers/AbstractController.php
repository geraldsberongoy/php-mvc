<?php
namespace Gerald\Framework\Controllers;

use Gerald\Framework\Database\Connection;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractController
{
    protected Request $request;
    protected Connection $connection;

    // Constructor injection for Request and DB Connection
    public function __construct(Request $request, Connection $connection)
    {
        $this->request    = $request;
        $this->connection = $connection;
    }

    public function render(string $template, ?array $vars = [], int $statusCode = 200): Response
    {
        $templatePath = BASE_PATH . '/views/';
        $loader       = new FilesystemLoader($templatePath);
        $twig         = new Environment($loader);

        $content = $twig->render($template, $vars);

        return new Response($content, $statusCode);
    }
}
