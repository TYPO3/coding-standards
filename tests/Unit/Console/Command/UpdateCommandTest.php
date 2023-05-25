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

use TYPO3\CodingStandards\Console\Command\UpdateCommand;

#[\PHPUnit\Framework\Attributes\CoversClass(UpdateCommand::class)]
final class UpdateCommandTest extends UpdateCommandTestCase
{
    public function testExecute(): void
    {
        $this->assertExecuteScenario(self::getTestPath(), 'update');
    }
}
