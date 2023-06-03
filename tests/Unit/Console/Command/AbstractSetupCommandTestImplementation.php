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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Console\Command\AbstractSetupCommand;

/**
 * @internal
 */
final class AbstractSetupCommandTestImplementation extends AbstractSetupCommand
{
    protected function executeSetup(InputInterface $input, OutputInterface $output): int
    {
        //throw new \LogicException('Call to executeSetup()', 1637417318);
        return 255;
    }

    public function testGetForce(InputInterface $input): bool
    {
        return $this->getForce($input);
    }
}
