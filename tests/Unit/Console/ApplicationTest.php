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
use Symfony\Component\EventDispatcher\EventDispatcher;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\UpdateCommand;
use TYPO3\CodingStandards\Console\Event\Application\Event;
use TYPO3\CodingStandards\Console\Event\Application\InitCommandsEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitTemplatesDirsEvent;
use TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\Setup\ConfigureEvent as SetupConfigureEvent;
use TYPO3\CodingStandards\EventListener\InitSubscriber;
use TYPO3\CodingStandards\EventListener\SetupSubscriber;
use TYPO3\CodingStandards\Events;
use TYPO3\CodingStandards\Tests\Unit\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(Application::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Command::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(UpdateCommand::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(Event::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitDefaultInputDefinitionEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitTemplatesDirsEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(ConfigureEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupConfigureEvent::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(InitSubscriber::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(SetupSubscriber::class)]
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

    public function testInitCommandsIsCalledOnlyOnce(): void
    {
        $calls = 0;

        $application = new Application();
        $application->getEventDispatcher()->addListener(
            Events::APPLICATION_INIT_COMMANDS,
            static function (InitCommandsEvent $initCommandsEvent) use (&$calls): void {
                ++$calls;
            }
        );

        $application->initCommands();
        $application->initCommands();

        self::assertSame(1, $calls);
    }

    public function testGetEventDispatcher(): void
    {
        $application = new Application();

        /** @var object $eventDispatcher */
        $eventDispatcher = $application->getEventDispatcher();
        self::assertInstanceOf(EventDispatcher::class, $eventDispatcher);
    }

    public function testGetTargetDir(): void
    {
        $application = new Application();

        $testPath = self::getTestPath();
        \mkdir($testPath . '/test-target');

        $input = new ArrayInput([]);
        self::assertSame($testPath, $application->getTargetDir($input));

        $input = new ArrayInput(['--target-dir' => 'test-target']);
        self::assertSame($testPath . '/test-target', $application->getTargetDir($input));

        $input = new ArrayInput(['-d' => 'test-target']);
        self::assertSame($testPath . '/test-target', $application->getTargetDir($input));
    }

    public function testGetTargetDirThrowsOnInvalidPath(): void
    {
        $application = new Application();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessageMatches('#Invalid target directory specified, /.*/invalid-target does not exist.#');

        self::getTestPath();
        $application->getTargetDir(new ArrayInput(['--target-dir' => 'invalid-target']));
    }

    public function testGetTemplatesDirs(): void
    {
        $application = new Application();

        self::assertSame([], $application->getTemplatesDirs());

        // Test custom templates dir
        $testPath = self::getTestPath();
        \mkdir($testPath . '/test-templates');

        $application->getEventDispatcher()->addListener(
            Events::APPLICATION_INIT_TEMPLATES_DIRS,
            static function (InitTemplatesDirsEvent $initTemplatesDirsEvent) use ($testPath): void {
                $initTemplatesDirsEvent->addTemplatesDir($testPath . '/test-templates');
            }
        );

        self::assertSame([$testPath . '/test-templates'], $application->getTemplatesDirs());
    }
}
