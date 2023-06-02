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

namespace TYPO3\CodingStandards\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent;
use TYPO3\CodingStandards\Events;

/**
 * @internal
 */
abstract class Command extends BaseCommand
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function getApplication(): Application
    {
        if (!($application = parent::getApplication()) instanceof Application) {
            $applicationClass = $application instanceof \Symfony\Component\Console\Application ? $application::class : '<null>';

            throw new RuntimeException(sprintf(
                "Invalid application class given, expected '%s' but found '%s'.",
                Application::class,
                $applicationClass
            ));
        }

        return $application;
    }

    protected function configure(): void
    {
        $configureEvent = new ConfigureEvent($this);
        $this->eventDispatcher->dispatch($configureEvent, Events::COMMAND_CONFIGURE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $executeEvent = new ExecuteEvent(
            $this,
            $input,
            $output,
            0,
        );
        $this->eventDispatcher->dispatch($executeEvent, Events::COMMAND_EXECUTE);

        return $executeEvent->getExitCode();
    }
}
