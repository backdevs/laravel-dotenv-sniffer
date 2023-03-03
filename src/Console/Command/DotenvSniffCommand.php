<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        // TODO: Implement execute() method.

        return self::SUCCESS;
    }
}
