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

namespace TYPO3\CodingStandards\Tests\Unit\Smoke;

use Keradus\CliExecutor\CliResult;
use Keradus\CliExecutor\CommandExecutor;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Tests\Unit\TestCase;

/**
 * @internal
 */
abstract class AbstractCliTestCase extends TestCase
{
    /**
     * @var string
     */
    private static $cliCwd;

    /**
     * @var string
     */
    private static $cliName;

    abstract protected static function getCliName(): string;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$cliCwd = self::getRootPath();
        self::$cliName = static::getCliName();

        if (!file_exists(self::$cliCwd . '/' . self::$cliName)) {
            self::fail('No binary available.');
        }
    }

    public function testVersion(): void
    {
        self::assertMatchesRegularExpression(
            '/^TYPO3 Coding Standards ' . Application::VERSION . '$/',
            self::executeCliCommand('--version')->getOutput()
        );
    }

    public function testSetup(): void
    {
        $command = (new Application())->find('setup');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--target-dir' => self::getRootPath()]);

        self::assertSame(
            $commandTester->getDisplay(),
            self::executeCliCommand('setup')->getOutput()
        );
    }

    public function testSetupHelp(): void
    {
        self::assertSame(
            0,
            self::executeCliCommand('setup --help')->getCode()
        );
    }

    private static function executeCliCommand(string $params): CliResult
    {
        return CommandExecutor::create('php ' . self::$cliName . ' ' . $params, self::$cliCwd)->getResult(false);
    }
}
