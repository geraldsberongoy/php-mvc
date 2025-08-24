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

                $controllerInstance = new $controller();

                // Use reflection to check if the method requires a Request parameter
                $reflectionMethod = new \ReflectionMethod($controllerInstance, $method);
                $parameters       = $reflectionMethod->getParameters();

                $args = [];

                // Check if the first parameter is a Request object
                if (! empty($parameters) && $parameters[0]->getType() && $parameters[0]->getType()->getName() === Request::class) {
                    $args[] = $request;
                }

                // Add route parameters
                $args = array_merge($args, $vars);

                return call_user_func_array([$controllerInstance, $method], $args);

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
