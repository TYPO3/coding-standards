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

namespace TYPO3\CodingStandards\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use TYPO3\CodingStandards\Console\Application;

/**
 * @codeCoverageIgnore
 * @internal
 */
abstract class Command extends BaseCommand
{
    public function getProjectDir(): string
    {
        return Application::getProjectDir();
    }

    public function getTargetDir(InputInterface $input): string
    {
        return Application::getTargetDir($input);
    }
}
