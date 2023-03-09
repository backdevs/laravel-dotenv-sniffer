<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Directory extends Constraint
{
    public const NOT_FOUND_ERROR = '5b4c6d8f-aecf-47cf-a268-63341330991b';
    public const NOT_DIRECTORY_ERROR = '369d9a65-acfc-415c-a620-bf5e18468bd3';
    public const NOT_READABLE_ERROR = 'a1b2d0ed-d6aa-40df-8be9-c98ebe5f8a32';

    public string $notFoundMessage = 'The path "{{ path }}" could not be found.';
    public string $notDirectoryMessage = 'The path "{{ path }}" is not a directory.';
    public string $notReadableMessage = 'The directory "{{ path }}" is not readable.';

    public string $mode = 'strict';
}
