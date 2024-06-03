<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2024 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Unit\Console\Command;

use Generator;
use RuntimeException;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\UpdateCommand;
use TYPO3\CodingStandards\Setup;

#[\PHPUnit\Framework\Attributes\CoversClass(SetupCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(UpdateCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Setup::class)]
final class SetupCommandTest extends SetupCommandTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('typeDataProvider')]
    public function testTypeArgument(string $type): void
    {
        $this->assertExecuteScenario(self::getTestPath(), 'setup', ['type' => $type]);
    }

    /**
     * @return Generator<string, array<string, string>>
     */
    public static function typeDataProvider(): Generator
    {
        foreach (Setup::VALID_TYPES as $type) {
            yield $type => [
                'type' => $type,
            ];
        }
    }

    public function testMissingTypeThrows(): void
    {
        $testPath = self::getTestPath();

        $commandTester = $this->getCommandTester('setup');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessageMatches('#.+(type).+#');

        $commandTester->execute($this->getInput($testPath));
    }

    public function testInvalidComposerManifestThrows(): void
    {
        $testPath = self::getTestPath();

        self::createFiles($testPath, ['composer.json' => 'FIX:invalid_composer.json']);

        $commandTester = $this->getCommandTester('setup');

        self::expectException(RuntimeException::class);
        self::expectExceptionMessageMatches('#.+(type).+#');

        $commandTester->execute($this->getInput($testPath));
    }

    /**
     * @param array<string, string>                    $existingFiles
     * @param array<string, array<int, string>|string> $input
     * @param array<string, bool|string>               $expectedFiles
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('setupDataProvider')]
    public function testSetup(
        array $existingFiles,
        string $targetDir,
        bool $force,
        array $input,
        string $expectedOutput,
        array $expectedFiles
    ): void {
        $testPath = self::getTestPath();
        $targetPath = \rtrim($testPath . '/' . $targetDir, '/');
        self::getFilesystem()->mkdir($targetPath);

        self::createFiles($testPath, $existingFiles);

        $commandTester = $this->getCommandTester('setup');
        self::assertSame(0, $commandTester->execute($this->getInput($targetPath, $force, $input)));
        self::assertStringContainsString($expectedOutput, $commandTester->getDisplay());

        foreach ($expectedFiles as $file => $template) {
            if ($template === false) {
                self::assertFileDoesNotExist($testPath . '/' . $file);
            } elseif (is_string($template)) {
                self::assertFileEquals(self::getFilename($template), $testPath . '/' . $file);
            } else {
                self::assertFileExists($testPath . '/' . $file);
            }
        }
    }

    /**
     * @return Generator<string, array{
     *   existingFiles: array<string, string>,
     *   targetDir: string,
     *   force: bool,
     *   input: array<string, array<int, string>|string>,
     *   expectedOutput: string,
     *   expectedFiles: array<string, bool|string>
     * }>
     */
    public static function setupDataProvider(): Generator
    {
        yield 'auto-detect extension from type' => [
            'existingFiles' => [
                'composer.json' => 'FIX:extension-type_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => [],
            'expectedOutput' => 'for extension',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:extension_php-cs-fixer.dist.php',
            ],
        ];
        yield 'auto-detect extension from key' => [
            'existingFiles' => [
                'composer.json' => 'FIX:extension-key_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => [],
            'expectedOutput' => 'for extension',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:extension_php-cs-fixer.dist.php',
            ],
        ];
        yield 'auto-detect project' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => [],
            'expectedOutput' => 'for project',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:project_php-cs-fixer.dist.php',
            ],
        ];
        yield 'editorconfig only' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => ['--rule-set' => ['editorconfig']],
            'expectedOutput' => '.editorconfig',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => false,
            ],
        ];
        yield 'php-cs-fixer only' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => ['--rule-set' => ['php-cs-fixer']],
            'expectedOutput' => 'for project',
            'expectedFiles' => [
                '.editorconfig' => false,
                '.php-cs-fixer.dist.php' => 'TPL:project_php-cs-fixer.dist.php',
            ],
        ];
        yield 'all rule sets' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
            ],
            'targetDir' => '',
            'force' => false,
            'input' => ['--rule-set' => ['editorconfig', 'php-cs-fixer']],
            'expectedOutput' => 'for project',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:project_php-cs-fixer.dist.php',
            ],
        ];
        yield 'target-dir' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
            ],
            'targetDir' => 'subfolder',
            'force' => false,
            'input' => [],
            'expectedOutput' => 'for project',
            'expectedFiles' => [
                '.editorconfig' => false,
                '.php-cs-fixer.dist.php' => false,
                'subfolder/.editorconfig' => 'TPL:editorconfig.dist',
                'subfolder/.php-cs-fixer.dist.php' => 'TPL:project_php-cs-fixer.dist.php',
            ],
        ];
        yield 'force' => [
            'existingFiles' => [
                'composer.json' => 'FIX:project_composer.json',
                '.editorconfig' => 'FIX:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'FIX:php-cs-fixer.dist.php',
            ],
            'targetDir' => '',
            'force' => true,
            'input' => [],
            'expectedOutput' => 'for project',
            'expectedFiles' => [
                '.editorconfig' => 'TPL:editorconfig.dist',
                '.php-cs-fixer.dist.php' => 'TPL:project_php-cs-fixer.dist.php',
            ],
        ];
    }
}
