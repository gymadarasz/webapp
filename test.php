<?php declare(strict_types = 1);

use GuzzleHttp\Client;
use GyMadarasz\Test\AppTest;
use GyMadarasz\Test\Assertor;
use GyMadarasz\Test\Inspector;
use GyMadarasz\Test\Tester;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Invoker;
use GyMadarasz\WebApp\Service\Logger;

include __DIR__ . '/vendor/autoload.php';

// TODO: needs coverage info and more tests (cURL requests merged coverage!!)

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL | E_STRICT);
// set_error_handler(
//     static function(int $errno, string $errstr, string $errfile = null, int $errline = null, array $errcontext = null) : bool
//     {
//         throw new RuntimeException("An error occured: (code: $errno): $errstr\nIn file $errfile:$errline\n");
//     }
// );

return (new Tester())->test(
    new Invoker(),
    $config = new Config(),
    $logger = new Logger($config),
    new Assertor(),
    new Inspector(),
    $client = new Client([
        'base_uri' => $config->get('baseUrl'), 
        'cookies' => true,
    ]), [
        AppTest::class, //new AppTest($config, $logger, new Mysql($config)),
    ]
)->stat();