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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Console\Event\Command\Update\ConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\Update\ExecuteEvent;
use TYPO3\CodingStandards\Events;

/**
 * @internal
 */
final class UpdateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'update';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Update the TYPO3 rule sets';

    protected function configure(): void
    {
        parent::configure();

        $configureEvent = new ConfigureEvent($this);
        $this->eventDispatcher->dispatch($configureEvent, Events::COMMAND_UPDATE_CONFIGURE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = parent::execute($input, $output);

        $executeEvent = new ExecuteEvent(
            $this,
            $input,
            $output,
            $exitCode
        );
        $this->eventDispatcher->dispatch($executeEvent, Events::COMMAND_UPDATE_EXECUTE);

        return $executeEvent->getExitCode();
    }
}
