<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DirectoryValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Directory) {
            return;
        }

        $path = realpath($value);

        if ($path === false) {
            $this->context->buildViolation($constraint->notFoundMessage)
                ->setParameter('{{ path }}', $value)
                ->addViolation();

            return;
        }

        if (!is_dir($path)) {
            $this->context->buildViolation($constraint->notDirectoryMessage)
                ->setParameter('{{ path }}', $value)
                ->addViolation();
        }
    }
}
