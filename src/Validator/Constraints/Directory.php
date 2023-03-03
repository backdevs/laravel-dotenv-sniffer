<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Directory extends Constraint
{
    public string $notFoundMessage = 'The path "{{ path }}" could not be found.';

    public string $notDirectoryMessage = 'The path "{{ path }}" is not a directory.';

    public string $mode = 'strict';
}