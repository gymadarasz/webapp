<?php

use Madsoft\App\Mailer;
use Madsoft\App\Logger;
use Madsoft\App\Globals;
use Madsoft\App\Mysql;
use Madsoft\App\User;
use Madsoft\App\Template;
use Madsoft\App\Config;
use Madsoft\App\App;

include __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_error_handler(
    static function(int $errno, string $errstr, string $errfile = null, int $errline = null, array $errcontext = null) : bool
    {
        throw new RuntimeException("An error occured: (code: $errno): $errstr\nIn file $errfile:$errline\n");
    }
);

    
echo new App(
    $logger = new Logger(),
    $globals = new Globals(),
    $mysql = new Mysql(),
    new User($globals, $mysql),
    new Mailer($logger)
);
