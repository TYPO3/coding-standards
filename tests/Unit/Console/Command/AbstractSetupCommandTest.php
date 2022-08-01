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
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @covers \TYPO3\CodingStandards\Console\Command\AbstractSetupCommand
 */
final class AbstractSetupCommandTest extends SetupCommandTestCase
{
    /**
     * @var AbstractSetupCommandTestImplementation|null
     */
    private $setupCommandTestImplementation;

    protected function getCommand(string $name): Command
    {
        $this->setupCommandTestImplementation = new AbstractSetupCommandTestImplementation();
        $this->setupCommandTestImplementation->setApplication($this->getApplication());

        return $this->setupCommandTestImplementation;
    }

    public function testExecuteSetupIsCalled(): void
    {
        $testPath = self::getTestPath();

        $commandTester = $this->getCommandTester('');

        self::assertSame(255, $commandTester->execute($this->getInput($testPath)));
    }

    public function testThrowsOnInvalidPath(): void
    {
        $testPath = self::getTestPath();

        $commandTester = $this->getCommandTester('');

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessageMatches('#.+(invalid-path).+#');

        $commandTester->execute($this->getInput($testPath . '/invalid-path'));
    }

    public function testGetForce(): void
    {
        self::getTestPath();

        /** @var AbstractSetupCommandTestImplementation $command */
        $command = $this->getCommand('');

        self::assertFalse($command->testGetForce(new ArrayInput(
            ['--force' => false],
            $command->getDefinition()
        )));
        self::assertTrue($command->testGetForce(new ArrayInput(
            ['--force' => true],
            $command->getDefinition()
        )));
    }
}
