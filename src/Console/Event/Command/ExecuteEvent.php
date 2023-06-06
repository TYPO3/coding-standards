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

namespace TYPO3\CodingStandards\Console\Event\Command;

use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Console\Command\Command;

/**
 * Allows to manipulate the execution and the exit code of a command
 * before it is executed.
 */
class ExecuteEvent extends ConsoleEvent
{
    private int $exitCode;

    public function __construct(
        Command $command,
        InputInterface $input,
        OutputInterface $output,
        int $exitCode,
    ) {
        parent::__construct($command, $input, $output);

        $this->setExitCode($exitCode);
    }

    public function getCommand(): Command
    {
        /** @var Command $command */
        $command = parent::getCommand();

        return $command;
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
