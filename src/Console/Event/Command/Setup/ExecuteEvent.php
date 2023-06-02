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

namespace TYPO3\CodingStandards\Console\Event\Command\Setup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Console\Command\Command;
use TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent as BaseExecuteEvent;

/**
 * Allows to manipulate the execution and the exit code of the SetupCommand
 * before it is executed.
 */
final class ExecuteEvent extends BaseExecuteEvent
{
    /**
     * @param array<int, string> $ruleSets
     */
    public function __construct(
        Command $command,
        InputInterface $input,
        OutputInterface $output,
        int $exitCode,
        private readonly string $type,
        private readonly array $ruleSets,
        private readonly bool $force,
    ) {
        parent::__construct($command, $input, $output, $exitCode);
    }

    public function getForce(): bool
    {
        return $this->force;
    }

    /**
     * @return array<int, string>
     */
    public function getRuleSets(): array
    {
        return $this->ruleSets;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
