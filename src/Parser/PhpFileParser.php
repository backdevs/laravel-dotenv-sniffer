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

        $variables = [];

        for ($pos = 0; $pos < $file->getTokensCount(); ++$pos) {
            if (
                $file->getToken($pos)->isIgnorable()
                || !$file->getToken($pos)->is(self::ENV_ACCESSOR_TYPES)
                || !$file->getToken($pos)->is(self::ENV_ACCESSORS)
            ) {
                continue;
            }

            $openBracketPos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $pos + 1, exclude: true);
            if ($openBracketPos === null || !$file->getToken($openBracketPos)->is(self::ENV_ACCESSORS_OPEN_BRACKETS)) {
                continue;
            }

            // Special case for Laravel's Illuminate\Support\Env::get()
            $isLaravelEnvHelperGetCall = false;
            if ($file->getToken($pos)->is('get')) {
                if (!$this->isLaravelEnvHelperGetCall($file, $pos)) {
                    continue;
                }

                $isLaravelEnvHelperGetCall = true;
            }

            $defaultable = false;
            if ($isLaravelEnvHelperGetCall || $file->getToken($pos)->is('env')) {
                $defaultable = true;
            }

            $variable = $this->getVariable($file, $openBracketPos, $defaultable);
            if ($variable === null) {
                continue;
            }

            $variables[] = $variable;
        }

        return $variables;
    }

    private function getVariable(PhpFile $file, int $pos, bool $defaultable): ?Variable
    {
        $pos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $pos + 1, exclude: true);
        if ($pos === null || !$file->getToken($pos)->is(T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }

        $variableName = trim($file->getToken($pos)->text, '\'"');
        $lineNumber = $file->getToken($pos)->line;
        $hasDefault = false;

        if ($defaultable === true) {
            $pos = $file->findNext(self::IGNORABLE_TOKEN_IDS, $pos + 1, exclude: true);
            if ($pos !== null && $file->getToken($pos)->is(',')) {
                $hasDefault = true;
            }
        }

        return new Variable($variableName, $hasDefault, $lineNumber);
    }

    private function isLaravelEnvHelperGetCall(PhpFile $file, int $pos): bool
    {
        $pos = $file->findPrevious(self::IGNORABLE_TOKEN_IDS, $pos - 1, exclude: true);

        if (
            $pos === null
            || !$file->getToken($pos)->is(T_PAAMAYIM_NEKUDOTAYIM)
        ) {
            return false;
        }

        $pos = $file->findPrevious(
            self::IGNORABLE_TOKEN_IDS,
            $pos - 1,
            exclude: true
        );

        if (
            $pos === null
            || !$file->getToken($pos)->is([T_STRING, T_NAME_FULLY_QUALIFIED])
            || !in_array(
                ltrim($file->getToken($pos)->text, '\\'),
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