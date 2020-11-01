<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

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

class RouteSet
{
    const APP_ROUTES = [
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
            ],
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
            ],
        ],
        '*' => [
            '*' => [
                '*' => [ErrorPage::class, 'run'],
            ],
        ],
    ];

    /** @var array<mixed> */
    private $routes = [];

    /**
     * @param array<mixed> $routes
     * @return RouteSet
     */
    public function apply(array $routes): self
    {
        $this->routes = $this->merge($this->routes, $routes);
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     * @return array<mixed>
     */
    private function merge(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
