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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 * @todo for backward compatibility only, remove a.s.a.p
 * @internal
 */
final class SetupProjectCommand extends AbstractSetupCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'project';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Setting up the TYPO3 rule sets for a project';

    protected function configure(): void
    {
        parent::configure();

        // @todo remove once symfony 4 support is removed
        $this->setDescription(self::$defaultDescription);
        $this->setHidden(true);
    }

    protected function executeSetup(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>This command is deprecated, please use the <info>setup</info> command instead.</comment>');

        return $this->setup->forProject($this->getForce($input));
    }
}
