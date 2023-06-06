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

namespace TYPO3\CodingStandards\Console\Event\Command\Update;

use TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent as BaseExecuteEvent;

/**
 * Allows to manipulate the execution and the exit code of the UpdateCommand
 * before it is executed.
 */
final class ExecuteEvent extends BaseExecuteEvent
{
}
