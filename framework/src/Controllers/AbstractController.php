<?php

namespace Gerald\Framework\Controllers;

use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AbstractController
{
    protected Request $request;

    public function render(string $template, ?array $vars = []): Response
    {
        $templatePath = BASE_PATH . '/views/';
        $loader       = new FilesystemLoader($templatePath);
        $twig         = new Environment($loader);

        $content = $twig->render($template, $vars);

        $response = new Response($content);
        return $response;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
