<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Reporting;

use Backdevs\DotenvSniffer\Parser\Variable;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class Reporter
{
    private const LINE_WIDTH = 70; // chars

    private bool $hasErrors = false;

    private string $sectionSeparator;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly array           $envVariables,
        private readonly bool            $warnWithDefault,
    ) {
        $this->sectionSeparator = str_repeat('-', self::LINE_WIDTH);

        $output->getFormatter()->setStyle(
            'error',
            new OutputFormatterStyle('red'),
        );

        $output->getFormatter()->setStyle(
            'warning',
            new OutputFormatterStyle('yellow'),
        );

        $output->getFormatter()->setStyle(
            'line',
            new OutputFormatterStyle('cyan'),
        );

        $output->getFormatter()->setStyle(
            'path',
            new OutputFormatterStyle('magenta'),
        );
    }

    /**
     * @param Variable[] $variables
     */
    public function report(string $filePath, array $variables): void
    {
        /** @var Item[] $items */
        $items = [];

        $errors = 0;
        $warnings = 0;

        $maxLineNumDigits = 0;

        foreach ($variables as $variable) {
            if (in_array($variable->getName(), $this->envVariables, true)) {
                continue;
            }

            $itemType = ItemType::ERROR;

            if ($this->warnWithDefault && $variable->hasDefault()) {
                ++$warnings;
                $itemType = ItemType::WARNING;
            } else {
                ++$errors;
                $this->hasErrors = true;
            }

            $lineNumDigits = strlen((string) $variable->getLine());
            if ($lineNumDigits >= $maxLineNumDigits) {
                $maxLineNumDigits = $lineNumDigits;
            }

            $items[] = new Item($variable, $itemType);
        }

        if (count($items) === 0) {
            return;
        }

        // Header
        $this->writeSection($this->getHeaderFileLine($filePath));

        // Found errors and warnings
        $this->writeSection($this->getHeaderProblemsLine($errors, $warnings));

        // Items
        $lines = [];

        foreach ($items as $item) {
            $lines[] = $this->getItemLine($item, $maxLineNumDigits);
        }
        $this->writeSection($lines);
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    private function getItemLine(Item $item, int $maxLineNumDigits): string
    {
        $lineNum = $item->getVariable()->getLine();
        $lineNumPadding = str_repeat(' ', $maxLineNumDigits - strlen((string) $lineNum));

        $type = $item->getType();


        if ($type === ItemType::ERROR) {
            $coloredType = sprintf('<error>%s</error>', $type->name);
        } else {
            $coloredType = sprintf('<warning>%s</warning>', $type->name);
        }

        return sprintf(
            ' <line>%d</line>%s | %s%s | <info>%s</info> is not declared%s',
            $lineNum,
            $lineNumPadding,
            $coloredType,
            str_repeat(' ', strlen(ItemType::WARNING->name) - strlen($type->name)),
            $item->getVariable()->getName(),
            $type === ItemType::WARNING ? ' but has default value' : '',
        );
    }

    private function getHeaderProblemsLine(int $errors, int $warnings): string
    {
        $errorsPart = '';
        $warningsPart = '';

        if ($errors !== 0) {
            $errorsPart = sprintf('<error>%d ERROR(S)</error>', $errors);
        }

        if ($warnings !== 0) {
            $warningsPart = sprintf('<warning>%d WARNING(S)</warning>', $warnings);
        }

        return sprintf(
            'FOUND %s%s%s',
            $errorsPart,
            ($errors !== 0 && $warnings !== 0) ? ' AND ' : '',
            $warningsPart
        );
    }

    private function getHeaderFileLine(string $path): string
    {
        $pathLength = strlen($path);
        $lineWidth = self::LINE_WIDTH - 6; // 6 being the length of "FILE: "

        $displayPath = $pathLength <= $lineWidth
            ? $path
            : '...' . substr($path, ($pathLength - $lineWidth));

        return sprintf(
            'FILE: <path>%s</path>',
            $displayPath,
        );
    }

    private function writeSection(string|array $lines): void
    {
        $lines = (array) $lines;

        foreach ($lines as $line) {
            $this->output->writeln($line);
        }

        $this->output->writeln($this->sectionSeparator);
    }
}
