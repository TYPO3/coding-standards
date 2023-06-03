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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
abstract class AbstractSetupCommand extends Command
{
    /**
     * @var Setup
     */
    protected $setup;

    protected function configureBefore(): void
    {
    }

    protected function configure(): void
    {
        $this->configureBefore();

        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Replace existing files')
        ;
    }

    protected function getForce(InputInterface $input): bool
    {
        return (bool)$input->getOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setup = new Setup($this->getTargetDir($input), new SymfonyStyle($input, $output));

        return $this->executeSetup($input, $output);
    }

    abstract protected function executeSetup(InputInterface $input, OutputInterface $output): int;
}
