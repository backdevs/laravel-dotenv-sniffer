<?php

declare(strict_types=1);

namespace Backdevs\DotenvSniffer\Parser;

class PhpFileParser
{
    public const IGNORABLE_TOKEN_IDS = [
        T_WHITESPACE,
        T_COMMENT,
        T_DOC_COMMENT,
        T_OPEN_TAG,
    ];

    private const ENV_ACCESSORS = [
        'env',
        'getenv',
        '$_ENV',
        'get',
    ];

    private const ENV_ACCESSOR_TYPES = [
        T_STRING,
        T_VARIABLE,
    ];

    private const ENV_ACCESSORS_OPEN_BRACKETS = [
        '(',
        '[',
    ];

    /**
     * @return Variable[]
     */
    public function parse(string $filePath): array
    {
        $file = new PhpFile($filePath);
        $tokens = $file->getTokens();

        $variables = [];

        for ($pos = 0; $pos < $file->getTokensCount(); ++$pos) {
            if (
                $tokens[$pos]->isIgnorable()
                || !$tokens[$pos]->is(self::ENV_ACCESSOR_TYPES)
                || !in_array($tokens[$pos], self::ENV_ACCESSORS)
            ) {
                continue;
            }

            $openBracketPos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $pos + 1, exclude: true);
            if ($tokens[$openBracketPos] === null || !$tokens[$openBracketPos]->is(self::ENV_ACCESSORS_OPEN_BRACKETS)) {
                continue;
            }

            // Special case for Laravel's Illuminate\Support\Env::get()
            if ($tokens[$pos]->text === 'get' && !$this->isLaravelEnvHelperGetCall($file, $pos)) {
                continue;
            }

            $variable = $this->getVariable($file, $openBracketPos);
            if ($variable === null) {
                continue;
            }

            $variables[] = $variable;
        }

        return $variables;
    }

    private function getVariable(PhpFile $file, int $currentPos): ?Variable
    {
        $currentPos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $currentPos + 1, exclude: true);
        if ($currentPos === null || !$file->getToken($currentPos)->is(T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }

        $variableName = trim($file->getToken($currentPos)->text, '\'"');
        $lineNumber = $file->getToken($currentPos)->line;
        $hasDefault = false;

        $currentPos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $currentPos + 1, exclude: true);
        if ($currentPos === null || !$file->getToken($currentPos)->is(',')) {
            $hasDefault = true;
        }

        return new Variable($variableName, $hasDefault, $lineNumber);
    }

    private function isLaravelEnvHelperGetCall(PhpFile $file, int $currentPos): bool
    {
        $currentPos = $file->findPrevious(self::IGNORABLE_TOKEN_IDS, $currentPos - 1, exclude: true);

        if (
            $currentPos === null
            || !$file->getToken($currentPos)->is(T_PAAMAYIM_NEKUDOTAYIM)
        ) {
            return false;
        }

        $currentPos = $file->findPrevious(
            self::IGNORABLE_TOKEN_IDS,
            $currentPos - 1,
            exclude: true
        );

        if (
            $currentPos === null
            || !$file->getToken($currentPos)->is([T_STRING, T_NAME_FULLY_QUALIFIED])
            || !in_array(
                ltrim($file->getToken($currentPos)->text, '\\'),
                // TODO: Also check in case of "Env" that "Illuminate\Support\Env" is `use`d in the file
                ['Illuminate\Support\Env', 'Env'],
                true
            )
        ) {
            return false;
        }

        return true;
    }
}