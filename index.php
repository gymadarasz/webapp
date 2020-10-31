<?php declare(strict_types = 1);

use Madsoft\App\Router;
use Madsoft\App\Controller\LoginPage;
use Madsoft\App\Controller\RegistryPage;
use Madsoft\App\Controller\ActivatePage;
use Madsoft\App\Controller\PasswordResetPage;
use Madsoft\App\Controller\NewPasswordPage;
use Madsoft\App\Controller\ResendPage;
use Madsoft\App\Controller\ErrorPage;
use Madsoft\App\Controller\LoginPagePost;
use Madsoft\App\Controller\RegistryPagePost;
use Madsoft\App\Controller\PasswordResetPagePost;
use Madsoft\App\Controller\NewPasswordPagePost;
use Madsoft\App\Controller\IndexPage;
use Madsoft\App\Controller\LogoutPage;

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

