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

use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\UpdateCommand;
use TYPO3\CodingStandards\Console\Event\Application\Event;
use TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitTemplatesDirsEvent;
use TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent;
use TYPO3\CodingStandards\Console\Event\Command\Setup\ConfigureEvent as SetupConfigureEvent;
use TYPO3\CodingStandards\EventListener\InitSubscriber;
use TYPO3\CodingStandards\EventListener\SetupSubscriber;
use TYPO3\CodingStandards\Setup;

#[\PHPUnit\Framework\Attributes\CoversClass(UpdateCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Event::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitDefaultInputDefinitionEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitTemplatesDirsEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(ConfigureEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(ExecuteEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupConfigureEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitSubscriber::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupSubscriber::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Setup::class)]
final class UpdateCommandTest extends UpdateCommandTestCase
{
    public function testExecute(): void
    {
        $this->assertExecuteScenario(self::getTestPath(), 'update');
    }
}
