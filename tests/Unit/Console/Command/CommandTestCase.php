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

namespace TYPO3\CodingStandards\Tests\Unit\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Tests\Unit\TestCase;

class CommandTestCase extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
    }

    protected function getApplication(): Application
    {
        return $this->application;
    }

    protected function getCommand(string $name): Command
    {
        return $this->application->find($name);
    }

    protected function getCommandTester(string $commandName): CommandTester
    {
        return new CommandTester($this->getCommand($commandName));
    }
}
