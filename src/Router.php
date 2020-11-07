<?php declare(strict_types = 1);

/**
 * Router
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp;

use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Invoker;
use function json_encode;

/**
 * Router
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Router
{
    /**
     * Method __construct
     *
     * @param mixed[] $routes  routes
     * @param Invoker $invoker invoker
     * @param Globals $globals globals
     */
    public function __construct(
        array $routes,
        Invoker $invoker,
        Globals $globals = null
    ) {
        if (!$globals) {
            $globals = new Globals();
        }
        $globals->sessionStart();
        $routes = $globals->getSession('user', false) ?
            $routes = $routes['protected'] ?? $routes['*'] :
            $routes = $routes['public'] ?? $routes['*'];
        $routesPerMethod = $routes[$globals->getMethod()] ?? $routes['*'];
        $routesPerQuery = $routesPerMethod[$globals->getGet('q', '')] ??
                $routesPerMethod['*'];
        
        $results = $invoker->invoke($routesPerQuery);
        
        if (is_array($results)
            || (is_object($results) && !method_exists($results, '__toString'))
        ) {
            $results = json_encode($results);
        }
        echo $results;
    }
}
