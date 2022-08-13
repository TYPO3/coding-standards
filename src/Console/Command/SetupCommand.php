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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
final class SetupCommand extends AbstractSetupCommand
{
    use TypeTrait;

    /**
     * @var string
     */
    protected static $defaultName = 'setup';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Setting up the TYPO3 rule sets for an extension or a project';

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'rule-set',
                'r',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Rule set to set up',
                Setup::VALID_RULE_SETS
            )
        ;
    }

    /**
     * @return array<int, string>
     */
    private function getRuleSets(InputInterface $input): array
    {
        /** @var array<int, string> $ruleSets */
        $ruleSets = $input->getOption('rule-set');

        return $ruleSets;
    }

    protected function executeSetup(InputInterface $input, OutputInterface $output): int
    {
        $result = true;
        $ruleSets = $this->getRuleSets($input);

        if (\in_array(Setup::RULE_SET_EDITORCONFIG, $ruleSets, true)) {
            $result = $this->setup->copyEditorConfig($this->getForce($input));
        }

        if (\in_array(Setup::RULE_SET_PHP_CS_FIXER, $ruleSets, true)) {
            $result = $this->setup->copyPhpCsFixerConfig($this->getForce($input), $this->type) && $result;
        }

        return $result ? 0 : 1;
    }
}
