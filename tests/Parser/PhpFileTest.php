<?php

declare(strict_types=1);

namespace Tests\Parser;

use Backdevs\DotenvSniffer\Parser\PhpFile;
use Generator;
use PhpToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PhpFileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->validPhpFilePath = __DIR__ . '/Fixtures/ValidPhpClass.php';
    }

    public function testTokenizesCorrectly()
    {
        $tokens = PhpToken::tokenize(file_get_contents($this->validPhpFilePath));
        $tokensCount = count($tokens);

        $phpFile = new PhpFile($this->validPhpFilePath);

        self::assertEquals($tokens, $phpFile->getTokens());
        self::assertEquals($tokensCount, $phpFile->getTokensCount());
    }

    public function testThrowsRuntimeExceptionWhenFileDoesNotExist(): void
    {
        $path = __DIR__ . '/Fixtures/CocoJambo.php';

        self::expectException(RuntimeException::class);

        new PhpFile($path);
    }

    #[DataProvider(methodName: 'provideFindPreviousTests')]
    public function testFindPrevious(
        int $expectedPos,
        PhpToken $expectedToken,
        array $types,
        int $startPos,
        ?int $endPos,
        bool $exclude
    ): void {
        $phpFile = new PhpFile($this->validPhpFilePath);

        $pos = $phpFile->findPrevious($types, $startPos, $endPos, $exclude);

        self::assertEquals($expectedPos, $pos);
        self::assertEquals($expectedToken, $phpFile->getToken($pos));
    }

    public static function provideFindPreviousTests(): Generator
    {
        yield 'finds last T_CONSTANT_ENCAPSED_STRING' => [
            63,
            new PhpToken(T_CONSTANT_ENCAPSED_STRING, '\'jambo\'', line: 22, pos: 306),
            [T_CONSTANT_ENCAPSED_STRING],
            69, // ( ͡° ͜ʖ ͡°)
            null,
            false,
        ];

        yield 'finds previous T_VARIABLE before given position' => [
            59,
            new PhpToken(T_VARIABLE, '$coco', line: 22, pos: 298),
            [T_VARIABLE],
            63,
            null,
            false,
        ];

        yield 'finds previous T_VARIABLE before given position and after given end position' => [
            59,
            new PhpToken(T_VARIABLE, '$coco', line: 22, pos: 298),
            [T_VARIABLE],
            63,
            58,
            false,
        ];

        yield 'finds previous token before given position and after given end position, excluding given types' => [
            49,
            new PhpToken(44, ',', line: 20, pos: 269),
            PhpFile::IGNORABLE_TOKENS,
            49,
            20,
            true,
        ];
    }

    #[DataProvider(methodName: 'provideFindNextTests')]
    public function testFindNext(
        int $expectedPos,
        PhpToken $expectedToken,
        array $types,
        int $startPos,
        ?int $endPos,
        bool $exclude
    ): void {
        $phpFile = new PhpFile($this->validPhpFilePath);

        $pos = $phpFile->findNext($types, $startPos, $endPos, $exclude);

        self::assertEquals($expectedPos, $pos);
        self::assertEquals($expectedToken, $phpFile->getToken($pos));
    }

    public static function provideFindNextTests(): Generator
    {
        $pos = 0;
        yield 'finds first T_STRING' => [
            4,
            new PhpToken(T_STRING, 'strict_types', line: 3, pos: 15),
            [T_STRING],
            $pos,
            null,
            false,
        ];

        yield 'finds next T_STRING after given position' => [
            17,
            new PhpToken(T_STRING, 'PhpToken', line: 7, pos: 65),
            [T_STRING],
            5,
            null,
            false,
        ];

        yield 'finds next T_STRING after given position and before given end position' => [
            17,
            new PhpToken(T_STRING, 'PhpToken', line: 7, pos: 65),
            [T_STRING],
            5,
            18,
            false,
        ];

        yield 'finds next token by excluding given types' => [
            5,
            new PhpToken(61, '=', line: 3, pos: 27),
            [T_NAMESPACE, T_USE],
            5,
            null,
            true,
        ];

        yield 'finds next token by excluding given types and before given end position' => [
            5,
            new PhpToken(61, '=', line: 3, pos: 27),
            [T_NAMESPACE, T_USE],
            5,
            6,
            true,
        ];
    }
}
