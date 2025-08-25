<?php
namespace Gerald\Framework\Controllers;

use Gerald\Framework\Http\Response;

class ErrorController extends AbstractController
{
    public function notFound(): Response
    {
        return $this->render('404.html.twig', [], 404);
    }

    public function methodNotAllowed(array $allowedMethods = []): Response
    {
        $methods = implode(', ', $allowedMethods);
        return new Response(
            "405 Method Not Allowed. Allowed methods: {$methods}",
            405
        );
    }

    public function internalServerError(): Response
    {
        return new Response('500 Internal Server Error', 500);
    }
}
