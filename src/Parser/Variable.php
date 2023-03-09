<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Parser;

class Variable
{
    public function __construct(
        private readonly string $name,
        private readonly bool $hasDefault,
        private readonly int $line,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }
}
