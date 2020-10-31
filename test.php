<?php declare(strict_types = 1);

use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Mysql;
use Madsoft\Test\Test;
use Madsoft\Test\Tester;
use Madsoft\Test\AppTest;
use GuzzleHttp\Client;

// TODO: needs coverage info and more tests (cURL requests merged coverage!!)

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// set_error_handler(
//     static function(int $errno, string $errstr, string $errfile = null, int $errline = null, array $errcontext = null) : bool
//     {
//         throw new RuntimeException("An error occured: (code: $errno): $errstr\nIn file $errfile:$errline\n");
//     }
// );

include __DIR__ . '/vendor/autoload.php';

return (new Tester(
    $config = new Config(),
    $logger = new Logger($config),
    new Client([
        'base_uri' => $config->get('baseUrl'), 
        'cookies' => true,
    ]), [
        new AppTest($config, $logger, new Mysql($config)),
    ]
))->stat();