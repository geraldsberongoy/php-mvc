<?php
namespace Gerald\Framework\Http;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Kernel
{
    public function handle(Request $request): Response
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
            $routeCollector->addRoute('GET', '/', function () {
                $content = '<h1>Hello World!</h1>';

                return new Response($content);
            });

            $routeCollector->addRoute('GET', '/about', function () {
                $content = '<h1>About Page</h1>';

                return new Response($content);
            });

            //Dynamic Route
            $routeCollector->addRoute('GET', '/books/{id:\d+}', function (array $vars) {
                $content = '<h1>Book Details</h1>';
                $content .= '<p>Book ID: ' . htmlspecialchars($vars['id']) . '</p>';

                return new Response($content);
            });
        });
        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()
        );
        
        [$status, $handler, $vars] = $routeInfo;

        return $handler($vars);
    }
}
