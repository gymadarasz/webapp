<?php declare(strict_types = 1);

namespace Madsoft\Test;

interface Test
{
    public function run(Tester $tester): void;
}
