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

namespace TYPO3\CodingStandards\EventListener;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\UpdateCommand;
use TYPO3\CodingStandards\Console\Event\Application\InitCommandsEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent;
use TYPO3\CodingStandards\Events;

/**
 * @internal
 */
final class InitSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::APPLICATION_INIT_COMMANDS => ['onInitCommands', 127],
            Events::APPLICATION_INIT_DEFAULT_INPUT_DEFINITION => ['onInitDefaultInputDefinition', 127],
        ];
    }

    public function onInitCommands(InitCommandsEvent $initCommandsEvent): void
    {
        $application = $initCommandsEvent->getApplication();
        $eventDispatcher = $application->getEventDispatcher();

        // in alphabetical order
        $application->add(new SetupCommand($eventDispatcher));
        $application->add(new UpdateCommand($eventDispatcher));

        //$application->setDefaultCommand('setup', false);
    }

    public function onInitDefaultInputDefinition(InitDefaultInputDefinitionEvent $initDefaultInputDefinitionEvent): void
    {
        $initDefaultInputDefinitionEvent->getDefaultInputDefinition()
            ->addOption(new InputOption(
                '--target-dir',
                '-d',
                InputOption::VALUE_REQUIRED,
                'If specified, use the given directory as target directory',
                $initDefaultInputDefinitionEvent->getApplication()->getProjectDir()
            ))
        ;
    }
}
