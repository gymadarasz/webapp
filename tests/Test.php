<?php

namespace Madsoft\Test;

interface Test
{
    public function run(Tester $tester): void;
}
