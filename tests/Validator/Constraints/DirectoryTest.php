<?php

declare(strict_types=1);

namespace Validator\Constraints;

use Backdevs\DotenvSniffer\Validator\Constraints\Directory;
use Backdevs\DotenvSniffer\Validator\Constraints\DirectoryValidator;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    public function testValidatedByStandardValidator(): void
    {
        $constraint = new Directory();

        self::assertSame(DirectoryValidator::class, $constraint->validatedBy());
    }
}
