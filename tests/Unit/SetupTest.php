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

use TYPO3\CodingStandards\Setup;

/**
 * @covers \TYPO3\CodingStandards\Setup
 */
final class SetupTest extends TestCase
{
    /**
     * @var string
     */
    private const EDITORCONFIG_WARNING = "A .editorconfig file already exists in your main folder, but the -f option was not set. Nothing copied.\n";

    /**
     * @var string
     */
    private const PHPCS_FOUND_DEPRECATED_INFORMATION = "Found deprecated .php_cs file and renamed it to .php-cs-fixer.dist.php.\n";

    /**
     * @var string
     */
    private const PHPCS_FOUND_INFORMATION = "Found .php-cs-fixer.php file and renamed it to .php-cs-fixer.dist.php.\n";

    /**
     * @var string
     */
    private const PHPCS_WARNING = "A .php-cs-fixer.dist.php file already exists in your main folder, but the -f option was not set. Nothing copied.\n";

    /**
     * @param array<string, string> $replacePairs
     */
    protected function getFilename(string $filename, ?array $replacePairs = null): string
    {
        if ($replacePairs !== null) {
            $filename = strtr($filename, $replacePairs);
        }

        return parent::getFilename($filename);
    }

    /**
     * @param array<string, string> $existingFiles
     * @param array<string, bool|string> $expectedFiles
     */
    private function assertScenario(
        string $testType,
        array $existingFiles,
        bool $force,
        int $expectedResult,
        string $expectedOutput,
        array $expectedFiles
    ): void {
        $testPath = $this->getTestPath();
        $setup = new Setup($testPath);

        // create pre existing files
        foreach ($existingFiles as $target => $source) {
            copy($this->getFilename($source), $testPath . '/' . $target);
        }

        // call the subject's method
        $methodName = 'for' . ucfirst($testType);
        self::assertSame($expectedResult, $setup->$methodName($force)); // @phpstan-ignore-line
        self::expectOutputString($expectedOutput);

        // assert files
        foreach ($expectedFiles as $file => $template) {
            if ($template === false) {
                self::assertFileNotExists($testPath . '/' . $file);
            } elseif (is_string($template)) {
                self::assertFileEquals($this->getFilename($template, ['{$typePrefix}' => $testType]), $testPath . '/' . $file);
            } else {
                self::assertFileExists($testPath . '/' . $file);
            }
        }
    }

    /**
     * @dataProvider scenariosProvider
     *
     * @param array<string, string> $existingFiles
     * @param array<string, bool|string> $expectedFiles
     */
    public function testForProjectScenarios(
        array $existingFiles,
        bool $force,
        int $expectedResult,
        string $expectedOutput,
        array $expectedFiles
    ): void {
        $this->assertScenario('project', $existingFiles, $force, $expectedResult, $expectedOutput, $expectedFiles);
    }

    /**
     * @dataProvider scenariosProvider
     *
     * @param array<string, string> $existingFiles
     * @param array<string, bool|string> $expectedFiles
     */
    public function testForExtensionScenarios(
        array $existingFiles,
        bool $force,
        int $expectedResult,
        string $expectedOutput,
        array $expectedFiles
    ): void {
        $this->assertScenario('extension', $existingFiles, $force, $expectedResult, $expectedOutput, $expectedFiles);
    }

    /**
     * @return \Generator<string, array<string, array<string, bool|string>|int|string|bool>>
     */
    public function scenariosProvider(): \Generator
    {
        yield 'all files are created' => [
            'existingFiles' => [],
            'force' => false,
            'expectedResult' => 0,
            'expectedOutput' => '',
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
            'expectedOutput' => self::EDITORCONFIG_WARNING . self::PHPCS_WARNING,
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
            'expectedOutput' => self::EDITORCONFIG_WARNING,
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
            'expectedOutput' => self::PHPCS_WARNING,
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
            'expectedOutput' => self::PHPCS_FOUND_INFORMATION . self::PHPCS_WARNING,
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
            'expectedOutput' => self::PHPCS_FOUND_DEPRECATED_INFORMATION . self::PHPCS_WARNING,
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
            'expectedOutput' => '',
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
            'expectedOutput' => '',
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
            'expectedOutput' => '',
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
            'expectedOutput' => '',
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
            'expectedOutput' => '',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:{$typePrefix}_php-cs-fixer.dist.php',
                '.php-cs-fixer.php' => false,
                '.php_cs' => false,
            ],
        ];
    }
}
