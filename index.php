<?php declare(strict_types = 1);

use GyMadarasz\WebApp\Router;
use GyMadarasz\WebApp\Service\Invoker;
use GyMadarasz\WebApp\Service\RouteSet;

include __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL | E_STRICT);
set_error_handler(
    static function(int $errno, string $errstr, string $errfile = null, int $errline = null, array $errcontext = null) : bool
    {
        throw new RuntimeException("An error occured: (code: $errno): $errstr\nIn file $errfile:$errline\n");
    }
);

new Router(
    (new RouteSet())
        ->apply(RouteSet::APP_ROUTES)
        ->getRoutes(),
    new Invoker()
);

