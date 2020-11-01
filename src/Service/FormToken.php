<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

class FormToken
{
    private static int $token = 0;

    private Globals $globals;

    public function __construct(Globals $globals)
    {
        $this->globals = $globals;
    }

    public function get(): string
    {
        if (!self::$token) {
            self::$token = rand(100000000, 999999999);
            $this->globals->setSession('token', self::$token);
        }
        return '<input type="hidden" name="token" value="' . self::$token . '">';
    }

    public function check(): bool
    {
        return $this->globals->getSession('token') == $this->globals->getPost('token');
    }
}
