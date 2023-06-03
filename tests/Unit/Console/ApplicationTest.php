<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2023 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Unit\Console;

use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\ApplicationTester;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Tests\Unit\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupCommand::class)]
final class ApplicationTest extends TestCase
{
    public function testApplication(): void
    {
        $application = new Application();
        $application->setAutoExit(false);

        $applicationTester = new ApplicationTester($application);

        self::assertSame(0, $applicationTester->run([]));
        self::assertStringContainsString(
            'TYPO3 Coding Standards ' . Application::VERSION,
            $applicationTester->getDisplay()
        );
    }

    public function testMajorVersion(): void
    {
        self::assertSame(0, (int)explode('.', Application::VERSION)[0]);
    }

    public function testGetTargetDir(): void
    {
        $testPath = self::getTestPath();
        \mkdir($testPath . '/test-target');

        $input = new ArrayInput([]);
        self::assertSame($testPath, Application::getTargetDir($input));

        $input = new ArrayInput(['--target-dir' => 'test-target']);
        self::assertSame($testPath . '/test-target', Application::getTargetDir($input));

        $input = new ArrayInput(['-d' => 'test-target']);
        self::assertSame($testPath . '/test-target', Application::getTargetDir($input));
    }

    public function testGetTargetDirThrowsOnInvalidPath(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessageMatches('#Invalid target directory specified, /.*/invalid-target does not exist.#');

        self::getTestPath();
        Application::getTargetDir(new ArrayInput(['--target-dir' => 'invalid-target']));
    }
}
