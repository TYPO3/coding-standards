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

class UpdateCommandTestCase extends CommandTestCase
{
    /**
     * @param array<string, string|string[]|bool|null> $input
     * @return array<string, string|string[]|bool|null>
     */
    protected function getInput(string $testPath, array $input = []): array
    {
        return [
            '--target-dir' => $testPath,
            ...$input,
        ];
    }

    /**
     * @param array<string, string|string[]|bool|null> $input
     */
    protected function assertExecuteScenario(string $testPath, string $commandName, array $input = []): void
    {
        $commandTester = $this->getCommandTester($commandName);

        // test default path argument
        self::assertSame(0, $commandTester->execute($this->getInput($testPath, $input)));
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('[OK]', $output);

        // test force option false
        self::assertSame(0, $commandTester->execute($this->getInput($testPath, $input)));
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('[OK]', $output);
    }
}
