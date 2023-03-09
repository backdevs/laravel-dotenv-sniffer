<?php

declare(strict_types=1);

namespace Validator\Constraints;

use Backdevs\DotenvSniffer\Validator\Constraints\Directory;
use Backdevs\DotenvSniffer\Validator\Constraints\DirectoryValidator;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class DirectoryValidatorTest extends ConstraintValidatorTestCase
{
    public function testThrowsExceptionIfConstraintIsNotDirectory(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->validator->validate('some-value', new class extends Constraint{});
    }

    public function testNullValueIsValid(): void
    {
        $this->validator->validate(null, new Directory());

        self::assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Directory());

        self::assertNoViolation();
    }

    public function testThrowsExceptionIfValueIsNotStringCompatible(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $this->validator->validate(new stdClass(), new Directory());
    }

    public function testPathNotFound(): void
    {
        $path = __DIR__ . '/Fixtures/Directory/Path/NonExistent';

        $message = '{{ path }} not found';

        $constraint = new Directory([
            'notFoundMessage' => $message,
        ]);

        $this->validator->validate($path, $constraint);

        self::buildViolation($message)
            ->setParameter('{{ path }}', $path)
            ->setCode(Directory::NOT_FOUND_ERROR)
            ->assertRaised();
    }

    public function testPathNotDirectory(): void
    {
        $path = __DIR__ . '/Fixtures/Directory/Path/foo';

        $message = '{{ path }} not dir';

        $constraint = new Directory([
            'notFoundMessage' => $message,
        ]);

        $this->validator->validate($path, $constraint);

        self::buildViolation($message)
            ->setParameter('{{ path }}', $path)
            ->setCode(Directory::NOT_DIRECTORY_ERROR)
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidator
    {
        return new DirectoryValidator();
    }
}
