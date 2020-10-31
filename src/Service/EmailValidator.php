<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

class EmailValidator
{
    public function isValidEmail(string $email): string
    {
        return (string)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
