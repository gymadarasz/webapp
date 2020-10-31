<?php declare(strict_types = 1);

namespace GyMadarasz\Test;

interface Test
{
    public function run(Tester $tester): void;
}
