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
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
final class UpdateCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('update');
        $this->setDescription('Update the TYPO3 rule sets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = (new Setup($this->getTargetDir($input), new SymfonyStyle($input, $output)))->copyEditorConfig(true);

        return $result ? 0 : 1;
    }
}
