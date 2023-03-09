<?php

declare(strict_types=1);

namespace Tests\Parser;

use Backdevs\DotenvSniffer\Parser\PhpFileParser;
use Backdevs\DotenvSniffer\Parser\Variable;
use PHPUnit\Framework\TestCase;

class PhpFileParserTest extends TestCase
{
    private PhpFileParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new PhpFileParser();
    }

    public function testParsesCorrectly(): void
    {
        $path = __DIR__ . '/Fixtures/environment-calls.php';

        $variables = $this->parser->parse($path);

        $expectedVariables = $this->getExpectedVariables();

        self::assertCount(19, $variables);

        foreach ($variables as $variable) {
            self::assertArrayHasKey($variable->getName(), $expectedVariables);
            self::assertEquals($expectedVariables[$variable->getName()], $variable);
        }
    }

    private function getExpectedVariables(): array
    {
        return [
            'ENV' => new Variable('ENV', hasDefault: false, line: 10),
            'ENV_DOUBLE_QUOTES' => new Variable('ENV_DOUBLE_QUOTES', hasDefault: false, line:11),
            'ENV_DEFAULT' => new Variable('ENV_DEFAULT', hasDefault: true, line: 12),

            'EnvGet' => new Variable('EnvGet', hasDefault: false, line: 14),
            'EnvGetDoubleQuotes' => new Variable('EnvGetDoubleQuotes', hasDefault: false, line: 15),
            'EnvGetDefault' => new Variable('EnvGetDefault', hasDefault: true, line: 16),

            'Illuminate\Support\Env' => new Variable('Illuminate\Support\Env', hasDefault: false, line: 18),
            'Illuminate\Support\EnvDoubleQuotes' => new Variable('Illuminate\Support\EnvDoubleQuotes', hasDefault: false, line: 19),
            'Illuminate\Support\EnvDefault' => new Variable('Illuminate\Support\EnvDefault', hasDefault: true, line: 20),

            '\Illuminate\Support\Env' => new Variable('\Illuminate\Support\Env', hasDefault: false, line: 22),
            '\Illuminate\Support\EnvDoubleQuotes' => new Variable('\Illuminate\Support\EnvDoubleQuotes', hasDefault: false, line: 23),
            '\Illuminate\Support\EnvDefault' => new Variable('\Illuminate\Support\EnvDefault', hasDefault: true, line: 24),

            'GETENV' => new Variable('GETENV', hasDefault: false, line: 31),
            'GETENV_DOUBLE_QUOTES' => new Variable('GETENV_DOUBLE_QUOTES', hasDefault: false, line: 32),
            'GETENV_DEFAULT' => new Variable('GETENV_DEFAULT', hasDefault: false, line: 33),

            '$_ENV' => new Variable('$_ENV', hasDefault: false, line: 35),
            '\$_ENV_DOUBLE_QUOTES' => new Variable('\$_ENV_DOUBLE_QUOTES', hasDefault: false, line: 36),
            '$_ENV_DEFAULT' => new Variable('$_ENV_DEFAULT', hasDefault: false, line: 37),
            // Not supported yet
            '$_ENV_NULL_COALESCING_DEFAULT' => new Variable('$_ENV_NULL_COALESCING_DEFAULT', hasDefault: false, line: 38),
        ];
    }
}
