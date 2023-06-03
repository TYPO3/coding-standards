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

use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\AbstractSetupCommand;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\TypeTrait;
use TYPO3\CodingStandards\Setup;

#[\PHPUnit\Framework\Attributes\CoversClass(AbstractSetupCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(TypeTrait::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Setup::class)]
final class AbstractSetupCommandTest extends SetupCommandTestCase
{
    private ?AbstractSetupCommandTestImplementation $setupCommandTestImplementation = null;

    protected function getCommand(string $name): BaseCommand
    {
        if (!$this->setupCommandTestImplementation instanceof AbstractSetupCommandTestImplementation) {
            $this->setupCommandTestImplementation = new AbstractSetupCommandTestImplementation();
            $this->setupCommandTestImplementation->setApplication($this->getApplication());
        }

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

        self::expectException(RuntimeException::class);
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
