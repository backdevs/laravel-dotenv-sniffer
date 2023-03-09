<?php

namespace Backdevs\DotenvSniffer\Parser;

use PhpToken;
use RuntimeException;

class PhpFile
{
    /** @var PhpToken[] */
    private array $tokens;

    private int $tokensCount;

    public function __construct(string $path)
    {
        $this->tokens = PhpToken::tokenize($this->getFileContents($path));

        $this->tokensCount = count($this->tokens);
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getToken(int $pos): ?PhpToken
    {
        return $this->tokens[$pos] ?? null;
    }

    public function getTokensCount(): int
    {
        return $this->tokensCount;
    }

    public function findPrevious(array $types, int $startPos, ?int $endPos = null, bool $exclude = false): ?int
    {
        if ($endPos === null) {
            $endPos = 0;
        }

        for ($i = $startPos; $i >= $endPos; --$i) {
            $found = $exclude;
            if ($this->tokens[$i]->is($types)) {
                $found = !$exclude;
            }

            if ($found === true) {
                return $i;
            }
        }

        return null;
    }

    public function findNext(array $types, int $startPos, ?int $endPos = null, bool $exclude = false): ?int
    {
        if ($endPos === null || $endPos > $this->tokensCount) {
            $endPos = $this->tokensCount;
        }

        for ($i = $startPos; $i < $endPos; ++$i) {
            $found = $exclude;
            if ($this->tokens[$i]->is($types)) {
                $found = !$exclude;
            }

            if ($found === true) {
                return $i;
            }
        }

        return null;
    }

    private function getFileContents(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('File "%s" does not exist', $path));
        }

        return file_get_contents($path);
    }
}