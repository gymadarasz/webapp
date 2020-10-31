<?php declare(strict_types = 1);

namespace Madsoft\App;

use RuntimeException;
use ReflectionClass;
use Madsoft\App\Service\Globals;
use Madsoft\App\Controller\ErrorPage;

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
            $routes = $routes['protected'] ?? [];
        } else {
            $routes = $routes['public'] ?? [];
        }
        $routes = $routes[$globals->getMethod()] ?? $routes['*'];
        $routes = $routes[$globals->getGet('q', '')] ?? '*';
        
        $ctrlr = $this->getInstance($routes[0]);
        $results = $ctrlr->{$routes[1]}();
        if (is_array($results) || (is_object($results) && !method_exists($results, '__toString'))) {
            $results = json_encode($results);
        }
        echo $results;
    }

    /**
     * @return mixed
     */
    private function getInstance(string $class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }
        if (!class_exists($class)) {
            throw new RuntimeException('Class not exists: "' . $class . '"');
        }
        $constructor = (new ReflectionClass($class))->getConstructor();
        if (!$constructor) {
            return $this->instances[$class] = new $class;
        }
        $params = $constructor->getParameters();
        $args = [];
        foreach ($params as $param) {
            $paramClass = $param->getClass();
            if (!$paramClass) {
                throw new RuntimeException('Class ' . $class . '::__constructor() has an or more non-class typed parameters.');
            }
            $args[] = $this->getInstance($paramClass->name);
        }
        return $this->instances[$class] = new $class(...$args);
    }
}
