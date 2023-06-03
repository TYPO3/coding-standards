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

namespace TYPO3\CodingStandards\Tests\Unit\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Command\SetupCommand;

#[\PHPUnit\Framework\Attributes\CoversClass(Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupCommand::class)]
final class CommandTest extends CommandTestCase
{
    private ?CommandTestImplementation $commandTestImplementation = null;

    protected function getCommand(string $name): BaseCommand
    {
        if (!$this->commandTestImplementation instanceof CommandTestImplementation) {
            $this->commandTestImplementation = new CommandTestImplementation();
            $this->commandTestImplementation->setApplication($this->getApplication());
        }

        return $this->commandTestImplementation;
    }

    public function testGetProjectDir(): void
    {
        $testPath = self::getTestPath();

        /** @var CommandTestImplementation $baseCommand */
        $baseCommand = $this->getCommand('');

        self::assertSame($testPath, $baseCommand->getProjectDir());
    }

    public function testGetTargetDir(): void
    {
        $testPath = self::getTestPath();
        \mkdir($testPath . '/test-target');

        /** @var CommandTestImplementation $baseCommand */
        $baseCommand = $this->getCommand('');

        self::assertSame(
            $testPath,
            $baseCommand->getTargetDir(new ArrayInput([]))
        );
        self::assertSame(
            $testPath . '/test-target',
            $baseCommand->getTargetDir(new ArrayInput(['--target-dir' => 'test-target']))
        );
    }
}
