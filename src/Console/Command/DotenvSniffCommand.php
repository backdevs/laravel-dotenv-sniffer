<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Console\Command;

use ArrayIterator;
use Backdevs\DotenvSniffer\Parser\PhpFileParser;
use Backdevs\DotenvSniffer\Reporting\Reporter;
use Backdevs\DotenvSniffer\Validator\Constraints as DotenvSnifferAssert;
use Dotenv\Dotenv;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;

#[AsCommand(
    name: 'desniff',
    description: 'A code sniffer for environment variables not declared in .env files',
)]
class DotenvSniffCommand extends Command
{
    public const OPTION_NO_FAIL = 'no-fail';
    public const OPTION_WARN_WITH_DEFAULT = 'warn-with-default';
    public const OPTION_FAIL_CODE = 'fail-code';
    public const ARGUMENT_ENV_FILE = 'env-file';
    public const ARGUMENT_PATHS = 'paths';

    private Stopwatch $stopwatch;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->stopwatch = new Stopwatch();
    }

    protected function configure(): void
    {
        $this->setDefinition(new InputDefinition([
            new InputOption(self::OPTION_NO_FAIL,
                mode: InputOption::VALUE_NONE,
                description: 'Don\'t fail if errors are found',
            ),
            new InputOption(self::OPTION_WARN_WITH_DEFAULT, 'w',
                mode: InputOption::VALUE_NONE,
                description: 'Treat variables with default values passed to Laravel\'s env helper as warnings',
            ),
            new InputArgument(self::ARGUMENT_ENV_FILE,
                mode: InputArgument::REQUIRED,
                description: 'The .env file to check against',
                suggestedValues: ['.env', '.env.example', '.env.dev'],
            ),
            new InputArgument(self::ARGUMENT_PATHS,
                mode: InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                description: 'One or more files and/or directories to check',
            ),
            new InputOption(self::OPTION_FAIL_CODE, 'c',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Custom integer fail code, useful in CI/CD pipelines',
                default: self::FAILURE,
            ),
        ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatchEventName = 'execute';
        $this->stopwatch->start($stopwatchEventName);

        $this->validateInputArguments($input);

        $fileContents = file_get_contents($input->getArgument(self::ARGUMENT_ENV_FILE));
        $envVariables = array_keys(Dotenv::parse($fileContents));

        $parser = new PhpFileParser();

        $reporter = new Reporter(
            $output,
            $envVariables,
            (bool) $input->getOption(self::OPTION_WARN_WITH_DEFAULT),
        );

        foreach ($input->getArgument(self::ARGUMENT_PATHS) as $path) {
            if (is_dir($path)) {
                $dirIterator = new RecursiveDirectoryIterator(
                    $path,
                    FilesystemIterator::SKIP_DOTS,
                );

                $iterator = new RecursiveIteratorIterator($dirIterator);
            } else {
                $iterator = new ArrayIterator([new SplFileInfo($path)]);
            }

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $filePath = $file->getRealPath();
                $reporter->report(
                    $filePath,
                    $parser->parse($filePath),
                );
            }
        }

        $stopwatchEvent = $this->stopwatch->stop($stopwatchEventName);

        $output->writeln(sprintf(
            'Duration: %.2f secs; Memory: %.2f MB',
            $stopwatchEvent->getDuration() / 1000,
            $stopwatchEvent->getMemory() / 1024 / 1024,
        ));

        if ($reporter->hasErrors() && !$input->getOption(self::OPTION_NO_FAIL)) {
            return (int) $input->getOption(self::OPTION_FAIL_CODE);
        }

        return self::SUCCESS;
    }

    private function validateInputArguments(InputInterface $input): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate(
            $input->getArguments(),
            new Assert\Collection([
                self::ARGUMENT_ENV_FILE => [
                    new Assert\Required(),
                    new Assert\File(),
                ],
                self::ARGUMENT_PATHS => new Assert\All([
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
