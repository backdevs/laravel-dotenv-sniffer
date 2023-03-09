<?php

declare(strict_types=1);

namespace Some\Name\Space;

use PhpToken;

class ValidPhpClass
{
    public function __construct()
    {
    }

    /**
     * @param int $arg1
     * @param $arg2
     * @return void
     */
    private function privateMethod(int $arg1, $arg2): void
    {
        $coco = 'jambo';
    }
}
