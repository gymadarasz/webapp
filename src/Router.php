<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp;

use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Controller\ErrorPage;

class Router
{

    /** @var array<mixed> $routes */
    private array $routes;

    /** @var array<mixed> $instances */
    private array $instances;

    /**
     * @param array<mixed> $routes
     */
    public function __construct(array $routes, Globals $globals = null)
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
        
        $results = $this->invoke($routes);
        // $results = $ctrlr->{$routes[1]}();
        if (is_array($results) || (is_object($results) && !method_exists($results, '__toString'))) {
            $results = json_encode($results);
        }
        echo $results;
    }

    /**
     * @param array<string> $route
     * @return mixed
     */
    private function invoke(array $route)
    {
        $ctrlr = $this->getInstance($route[0]);
        $method = $ctrlr[1]->getMethod($route[1]);
        $args = $this->getArgs($method, 'Method ' . $route[0] . '::' . $route[1] . ' has an or more non-class typed parameters.');
        return $ctrlr[0]->{$route[1]}(...$args);
    }

    /**
     * @return array<mixed>
     */
    private function getInstance(string $class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }
        if (!class_exists($class)) {
            throw new RuntimeException('Class not exists: "' . $class . '"');
        }
        $refClass = new ReflectionClass($class);
        $constructor = ($refClass)->getConstructor();
        if (!$constructor) {
            return $this->instances[$class] = [new $class, $refClass];
        }
        $args = $this->getArgs($constructor, 'Method ' . $class . '::__constructor() has an or more non-class typed parameters.');
        return $this->instances[$class] = [new $class(...$args), $refClass];
    }

    /**
     * @return array<mixed>
     */
    private function getArgs(ReflectionMethod $method, string $messageOnError = 'A method has a parameter which cannot be instantiated.'): array
    {
        $params = $method->getParameters();
        $args = [];
        foreach ($params as $param) {
            $paramClass = $param->getClass();
            if (!$paramClass) {
                throw new RuntimeException($messageOnError);
            }
            $args[] = $this->getInstance($paramClass->name)[0];
        }
        return $args;
    }
}
