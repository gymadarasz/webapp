<?php declare(strict_types = 1);

use GyMadarasz\WebApp\Router;
use GyMadarasz\WebApp\Controller\LoginPage;
use GyMadarasz\WebApp\Controller\RegistryPage;
use GyMadarasz\WebApp\Controller\ActivatePage;
use GyMadarasz\WebApp\Controller\PasswordResetPage;
use GyMadarasz\WebApp\Controller\NewPasswordPage;
use GyMadarasz\WebApp\Controller\ResendPage;
use GyMadarasz\WebApp\Controller\ErrorPage;
use GyMadarasz\WebApp\Controller\LoginPagePost;
use GyMadarasz\WebApp\Controller\RegistryPagePost;
use GyMadarasz\WebApp\Controller\PasswordResetPagePost;
use GyMadarasz\WebApp\Controller\NewPasswordPagePost;
use GyMadarasz\WebApp\Controller\IndexPage;
use GyMadarasz\WebApp\Controller\LogoutPage;

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

new Router([
    'public' => [
        'GET' => [
            '' => [LoginPage::class, 'run'],
            'login' => [LoginPage::class, 'run'],
            'registry' => [RegistryPage::class, 'run'],
            'activate' => [ActivatePage::class, 'run'],
            'pwdreset' => [PasswordResetPage::class, 'run'],
            'newpassword' => [NewPasswordPage::class, 'run'],
            'resend' => [ResendPage::class, 'run'],
            '*' => [ErrorPage::class, 'run'],
        ],
        'POST' => [
            '' => [LoginPagePost::class, 'run'],
            'login' => [LoginPagePost::class, 'run'],
            'registry' => [RegistryPagePost::class, 'run'],
            'pwdreset' => [PasswordResetPagePost::class, 'run'],
            'newpassword' => [NewPasswordPagePost::class, 'run'],
            '*' => [ErrorPage::class, 'run'],
        ],
        '*' => [
            '*' => [ErrorPage::class, 'run'],
        ]
    ],
    'protected' => [
        'GET' => [
            '' => [IndexPage::class, 'run'],
            'logout' => [LogoutPage::class, 'run'],
            '*' => [ErrorPage::class, 'run'],
        ],
        'POST' => [
            '*' => [ErrorPage::class, 'run'],
        ],
        '*' => [
            '*' => [ErrorPage::class, 'run'],
        ]
    ],
    '*' => [
        '*' => [
            '*' => [ErrorPage::class, 'run'],
        ]
    ]
]);

