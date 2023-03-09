<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Validator\Constraints;

use Stringable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DirectoryValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Directory) {
            throw new UnexpectedTypeException($constraint, Directory::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value) && !$value instanceof Stringable) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!file_exists($value)) {
            $this->context->buildViolation($constraint->notFoundMessage)
                ->setParameter('{{ path }}', $value)
                ->setCode(Directory::NOT_FOUND_ERROR)
                ->addViolation();

            return;
        }

        if (!is_dir($value)) {
            $this->context->buildViolation($constraint->notDirectoryMessage)
                ->setParameter('{{ path }}', $value)
                ->setCode(Directory::NOT_DIRECTORY_ERROR)
                ->addViolation();

            return;
        }

        if (!is_readable($value)) {
            $this->context->buildViolation($constraint->notReadableMessage)
                ->setParameter('{{ path }}', $value)
                ->setCode(Directory::NOT_READABLE_ERROR)
                ->addViolation();
        }
    }
}
