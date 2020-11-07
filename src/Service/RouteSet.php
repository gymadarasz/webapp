<?php declare(strict_types = 1);

/**
 * RouteSet
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp\Service;

use GyMadarasz\WebApp\Controller\LoginPage;
use GyMadarasz\WebApp\Controller\RegistryPage;
use GyMadarasz\WebApp\Controller\ActivatePage;
use GyMadarasz\WebApp\Controller\PasswordResetPage;
use GyMadarasz\WebApp\Controller\NewPasswordPage;
use GyMadarasz\WebApp\Controller\ResendPage;
use GyMadarasz\WebApp\Controller\ErrorPage;
use GyMadarasz\WebApp\Controller\RegistryPagePost;
use GyMadarasz\WebApp\Controller\PasswordResetPagePost;
use GyMadarasz\WebApp\Controller\NewPasswordPagePost;
use GyMadarasz\WebApp\Controller\MainPage;
use GyMadarasz\WebApp\Controller\LogoutPage;
use RuntimeException;

/**
 * RouteSet
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class RouteSet
{
    const NO_ROUTES = [
        '*' => [
            '*' => [
                '*' => [self::class, 'noRoute'],
            ],
        ],
    ];

    const APP_ROUTES = [
        'public' => [
            'GET' => [
                '' => [LoginPage::class, 'viewLogin'],
                'login' => [LoginPage::class, 'viewLogin'],
                'registry' => [RegistryPage::class, 'viewRegistry'],
                'activate' => [ActivatePage::class, 'viewActivate'],
                'pwdreset' => [PasswordResetPage::class, 'viewPasswordReset'],
                'newpassword' => [NewPasswordPage::class, 'viewNewPassword'],
                'resend' => [ResendPage::class, 'viewResend'],
                '*' => [ErrorPage::class, 'viewError'],
            ],
            'POST' => [
                '' => [LoginPage::class, 'doLogin'],
                'login' => [LoginPage::class, 'doLogin'],
                'registry' => [RegistryPagePost::class, 'doRegistry'],
                'pwdreset' => [PasswordResetPagePost::class, 'doPasswordReset'],
                'newpassword' => [NewPasswordPage::class, 'doNewPassword'],
                '*' => [ErrorPage::class, 'viewError'],
            ],
            '*' => [
                '*' => [ErrorPage::class, 'viewError'],
            ],
        ],
        'protected' => [
            'GET' => [
                '' => [MainPage::class, 'viewIndex'],
                'logout' => [LogoutPage::class, 'viewLogout'],
                '*' => [ErrorPage::class, 'viewError'],
            ],
            'POST' => [
                '*' => [ErrorPage::class, 'viewError'],
            ],
            '*' => [
                '*' => [ErrorPage::class, 'viewError'],
            ],
        ],
        '*' => [
            '*' => [
                '*' => [ErrorPage::class, 'viewError'],
            ],
        ],
    ];

    /**
     * Variable routes
     *
     * @var mixed[]
     */
    protected $routes = [];

    /**
     * Method apply
     *
     * @param mixed[] $routes routes
     *
     * @return RouteSet
     */
    public function apply(array $routes): self
    {
        $this->routes = $this->merge($this->routes, $routes);
        return $this;
    }

    /**
     * Method getRoutes
     *
     * @return mixed[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Method merge
     *
     * @param mixed[] $array1 array1
     * @param mixed[] $array2 array2
     *
     * @return mixed[]
     */
    protected function merge(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {
            if (is_array($value)
                && isset($merged[$key]) && is_array($merged[$key])
            ) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Method noRoute
     *
     * @return void
     * @throws RuntimeException
     */
    public function noRoute(): void
    {
        throw new RuntimeException('No route defined');
    }
}
