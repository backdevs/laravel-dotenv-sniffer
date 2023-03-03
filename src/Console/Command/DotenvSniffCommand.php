<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Console\Command;

use Backdevs\DotenvSniffer\Validator\Constraints as DotenvSnifferAssert;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

#[AsCommand(
    name: 'desniff',
    description: 'A code sniffer for environment variables not defined in the .env file.',
)]
class DotenvSniffCommand extends Command
{
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDefinition(new InputDefinition([
            new InputArgument('env-file', InputArgument::REQUIRED,
                description: 'The .env file to check against',
                suggestedValues: ['.env', '.env.example', '.env.dev'],
            ),
            new InputArgument('paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                description: 'One or more files and/or directories to check'
            ),
        ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        print json_encode($input->getArguments(), JSON_PRETTY_PRINT);

        $this->validateInputArguments($input);

        // TODO: Implement execute() method.

        return self::SUCCESS;
    }

    private function validateInputArguments(InputInterface $input): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate(
            $input->getArguments(),
            new Assert\Collection([
                'env-file' => [
                    new Assert\Required(),
                    new Assert\File(),
                ],
                'paths' => new Assert\All([
                    new Assert\AtLeastOneOf([
                        new Assert\File(),
                        new DotenvSnifferAssert\Directory(),
                    ]),
                ]),
            ]),
        );

        if ($violations->count() > 0) {
            throw new ValidationFailedException('Invalid config file', $violations);
        }
    }
}
