<?php
namespace Gerald\Framework\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Gerald\Framework\Database\Connection;

class Kernel
{
    protected ?Connection $connection = null;

    public function __construct()
    {
        $this->connection = Connection::create();
    }

    public function handle(Request $request): Response
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
            $routes = include BASE_PATH . '/routes/web.php';

            foreach ($routes as $route) {
                $routeCollector->addRoute(...$route);
            }
        });

        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()
        );

        $status = $routeInfo[0];

        switch ($status) {
            case Dispatcher::FOUND:
                [$controller, $method] = $routeInfo[1];
                $vars                  = $routeInfo[2];

                // Pass Request + Connection into controller constructor
                $controllerInstance = new $controller($request, $this->connection);

                // Call controller method with only route parameters
                return call_user_func_array([$controllerInstance, $method], $vars);

            case Dispatcher::NOT_FOUND:
                return new Response('404 Not Found', 404);

            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response(
                    '405 Method Not Allowed. Allowed: ' . implode(', ', $routeInfo[1]),
                    405
                );

            default:
                return new Response('500 Internal Server Error', 500);
        }
    }
}
