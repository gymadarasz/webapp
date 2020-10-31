<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

class PasswordValidator
{
    public function getPasswordError(string $password): string
    {
        $passwordErr = '';
        if (strlen($password) < 8) {
            $passwordErr = "Your Password Must Contain At Least 8 Characters!";
        } elseif (!preg_match("#[0-9]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Number!";
        } elseif (!preg_match("#[A-Z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Capital Letter!";
        } elseif (!preg_match("#[a-z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Lowercase Letter!";
        }
        return $passwordErr;
    }
}
