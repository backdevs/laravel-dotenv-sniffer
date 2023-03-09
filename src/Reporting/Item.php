<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Reporting;

use Backdevs\DotenvSniffer\Parser\Variable;

class Item
{
    public function __construct(
        private readonly Variable $variable,
        private readonly ItemType $type
    ) {}

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function getType(): ItemType
    {
        return $this->type;
    }
}
