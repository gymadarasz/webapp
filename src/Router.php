<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp;

use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use GyMadarasz\WebApp\Service\Invoker;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Controller\ErrorPage;

class Router
{

    /** @var array<mixed> $routes */
    private array $routes;

    /**
     * @param array<mixed> $routes
     */
    public function __construct(array $routes, Invoker $invoker, Globals $globals = null)
    {
        if (!$globals) {
            $globals = new Globals();
        }
        $globals->sessionStart();
        if ($globals->getSession('user', false)) {
            $routes = $routes['protected'] ?? $routes['*'];
        } else {
            $routes = $routes['public'] ?? $routes['*'];
        }
        $routes = $routes[$globals->getMethod()] ?? $routes['*'];
        $routes = $routes[$globals->getGet('q', '')] ?? $routes['*'];
        
        $results = $invoker->invoke($routes);
        // $results = $ctrlr->{$routes[1]}();
        if (is_array($results) || (is_object($results) && !method_exists($results, '__toString'))) {
            $results = json_encode($results);
        }
        echo $results;
    }
}
