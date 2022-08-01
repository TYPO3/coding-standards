<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2022 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Unit;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use TYPO3\CodingStandards\Setup;
use TYPO3\CodingStandards\Tests\Console\Style\SimpleStyle;

/**
 * @covers \TYPO3\CodingStandards\Setup
 */
final class SetupTest extends TestCase
{
    /**
     * @var string
     */
    private const EDITORCONFIG_CREATED = '[OK] .editorconfig created.';

    /**
     * @var string
     */
    private const EDITORCONFIG_EXISTS = '[ERROR] A .editorconfig file already exists, nothing copied. Use the update command or the --force option to overwrite the file.';

    /**
     * @var string
     */
    private const PHPCSFIXER_RENAMED_DEPRECATED = '! [NOTE] Deprecated .php_cs renamed to .php-cs-fixer.dist.php.';

    /**
     * @var string
     */
    private const PHPCSFIXER_RENAMED = '! [NOTE] .php-cs-fixer.php renamed to .php-cs-fixer.dist.php.';

    /**
     * @var string
     */
    private const PHPCSFIXER_CREATED = '[OK] .php-cs-fixer.dist.php created for {$type}.';

    /**
     * @var string
     */
    private const PHPCSFIXER_EXISTS = '[ERROR] A .php-cs-fixer.dist.php file already exists, nothing copied. Use the --force option to overwrite the file.';

    /**
     * @var string
     */
    private const PHPCSFIXER_REMOVED = '! [NOTE] Deprecated .php_cs removed.';

    /**
     * @param array<int, string> $expectedOutput
     */
    private function calculateOutput(array $expectedOutput, string $type): string
    {
        $output = '';

        foreach ($expectedOutput as $singleExpectedOutput) {
            $output .= ' ' . strtr($singleExpectedOutput, ['{$type}' => $type]) . \PHP_EOL;
        }

        return $output;
    }

    /**
     * @param array<string, string> $existingFiles
     * @param array<int, string> $expectedOutput
     * @param array<string, bool|string> $expectedFiles
     */
    private function assertScenario(
        string $testType,
        array $existingFiles,
        bool $force,
        int $expectedResult,
        array $expectedOutput,
        array $expectedFiles
    ): void {
        $testPath = self::getTestPath();

        $arrayInput = new ArrayInput([]);
        // @phpstan-ignore-next-line
        $arrayInput->setStream(fopen('php://memory', 'r+', false));

        $bufferedOutput = new BufferedOutput();
        $simpleStyle = new SimpleStyle($arrayInput, $bufferedOutput);

        $setup = new Setup($testPath, $simpleStyle);

        // create pre existing files
        self::createFiles($testPath, $existingFiles);

        // call the subject's method
        $methodName = 'for' . ucfirst($testType);
        // @phpstan-ignore-next-line
        self::assertSame($expectedResult, $setup->$methodName($force));
        self::assertSame($this->calculateOutput($expectedOutput, $testType), $bufferedOutput->fetch());

        // assert files
        foreach ($expectedFiles as $file => $template) {
            if ($template === false) {
                self::assertFileNotExists($testPath . '/' . $file);
            } elseif (is_string($template)) {
                self::assertFileEquals(self::getFilename($template, ['{$typePrefix}' => $testType]), $testPath . '/' . $file);
            } else {
                self::assertFileExists($testPath . '/' . $file);
            }
        }
    }

    /**
     * @dataProvider scenariosProvider
     *
     * @param array<string, string> $existingFiles
     * @param array<int, string> $expectedOutput
     * @param array<string, bool|string> $expectedFiles
     */
    public function testForProjectScenarios(
        array $existingFiles,
        bool $force,
        int $expectedResult,
        array $expectedOutput,
        array $expectedFiles
    ): void {
        $this->assertScenario(Setup::PROJECT, $existingFiles, $force, $expectedResult, $expectedOutput, $expectedFiles);
    }

    /**
     * @dataProvider scenariosProvider
     *
     * @param array<string, string> $existingFiles
     * @param array<int, string> $expectedOutput
     * @param array<string, bool|string> $expectedFiles
     */
    public function testForExtensionScenarios(
        array $existingFiles,
        bool $force,
        int $expectedResult,
        array $expectedOutput,
        array $expectedFiles
    ): void {
        $this->assertScenario(Setup::EXTENSION, $existingFiles, $force, $expectedResult, $expectedOutput, $expectedFiles);
    }

    /**
     * @return \Generator<string, array{
     *   existingFiles: array<string, string>,
     *   force: bool,
     *   expectedResult: int,
     *   expectedOutput: array<int, string>,
     *   expectedFiles: array<string, bool|string>
     * }>
     */
    public function scenariosProvider(): \Generator
    {
        yield 'all files are created' => [
            'existingFiles' => [],
            'force' => false,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'files are not overwritten' => [
            'existingFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
                '.php_cs' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => false,
            'expectedResult' => 1,
            'expectedOutput' => [self::EDITORCONFIG_EXISTS, self::PHPCSFIXER_EXISTS],
            'expectedFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
                '.php_cs' => 'FIX:php-cs-fixer.dist.php',
            ],
        ];
        yield 'editorconfig is not overwritten' => [
            'existingFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
            ],
            'force' => false,
            'expectedResult' => 1,
            'expectedOutput' => [self::EDITORCONFIG_EXISTS, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs-fixer.dist.php is not overwritten' => [
            'existingFiles' => [
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => false,
            'expectedResult' => 1,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_EXISTS],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs-fixer.php is not overwritten' => [
            'existingFiles' => [
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => false,
            'expectedResult' => 1,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_RENAMED, self::PHPCSFIXER_EXISTS],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs is not overwritten' => [
            'existingFiles' => [
                '.php_cs' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => false,
            'expectedResult' => 1,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_RENAMED_DEPRECATED, self::PHPCSFIXER_EXISTS],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'all files are overwritten' => [
            'existingFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
                '.php_cs' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => true,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_REMOVED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
                '.php_cs' => false,
            ],
        ];
        yield '.editorconfig is overwritten' => [
            'existingFiles' => [
                '.editorconfig' => 'FIX:editorconfig.dist',
            ],
            'force' => true,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs-fixer.dist.php is overwritten' => [
            'existingFiles' => [
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => true,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs-fixer.php is preserved' => [
            'existingFiles' => [
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => true,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => 'FIX:php-cs-fixer.dist.php',
                '.php_cs' => false,
            ],
        ];
        yield 'php-cs is overwritten' => [
            'existingFiles' => [
                '.php_cs' => 'FIX:php-cs-fixer.dist.php',
            ],
            'force' => true,
            'expectedResult' => 0,
            'expectedOutput' => [self::EDITORCONFIG_CREATED, self::PHPCSFIXER_REMOVED, self::PHPCSFIXER_CREATED],
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
    }

    public function testCopyEditorConfig(): void
    {
        $setup = new Setup(self::getTestPath());

        self::assertTrue($setup->copyEditorConfig(false));
    }

    /**
     * @dataProvider typeDataProvider
     */
    public function testCopyPhpCsFixerConfig(string $type): void
    {
        $setup = new Setup(self::getTestPath());

        self::assertTrue($setup->copyPhpCsFixerConfig(false, $type));
    }

    /**
     * @return \Generator<string, array<string, string>>
     */
    public function typeDataProvider(): \Generator
    {
        foreach (Setup::VALID_TYPES as $type) {
            yield $type => [
                'type' => $type,
            ];
        }
    }

    public function testInvalidPathThrows(): void
    {
        $testPath = self::getTestPath() . '/invalid-path';

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('#.+(invalid-path).+#');

        new Setup($testPath);
    }

    public function testIoIsCreated(): void
    {
        $arrayInput = new ArrayInput([]);
        // @phpstan-ignore-next-line
        $arrayInput->setStream(fopen('php://memory', 'r+', false));

        $bufferedOutput = new BufferedOutput();
        $simpleStyle = new SimpleStyle($arrayInput, $bufferedOutput);

        $setup = new Setup(self::getTestPath(), $simpleStyle);

        self::assertTrue($setup->copyEditorConfig(false));
        self::assertStringContainsString('[OK]', $bufferedOutput->fetch());
    }

    public function testInvalidTypeThrows(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('#.+(type).+#');

        $setup = new Setup(self::getTestPath());
        $setup->copyPhpCsFixerConfig(false, 'invalid-type');
    }
}
