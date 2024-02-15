<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2024 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
final class SetupCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('setup')
            ->setDescription('Setting up the TYPO3 rule sets for an extension or a project')
            ->addArgument('type', InputArgument::OPTIONAL, sprintf(
                'Type to setup, valid types are <comment>["%s"]</comment>. If not set, the detection is automatic',
                implode('","', Setup::VALID_TYPES)
            ))
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Replace existing files'
            )
            ->addOption(
                'rule-set',
                'r',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Rule set to set up',
                Setup::VALID_RULE_SETS
            )
        ;
    }

    private function getForce(InputInterface $input): bool
    {
        return (bool)$input->getOption('force');
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

    /**
     * @throws RuntimeException
     */
    private function getType(InputInterface $input): string
    {
        $type = $input->getArgument('type');

        if (!is_string($type) || $type === '') {
            $composerManifestError = 'Cannot auto-detect type, composer.json cannot be %s. Use the type argument instead.';

            $composerManifest = $this->getProjectDir() . '/composer.json';
            if (!file_exists($composerManifest)) {
                throw new RuntimeException(sprintf($composerManifestError, 'found'));
            }

            $composerManifest = \file_get_contents($composerManifest);
            if ($composerManifest === false) {
                throw new RuntimeException(sprintf($composerManifestError, 'read')); // @codeCoverageIgnore
            }

            $composerManifest = \json_decode($composerManifest, true, 512, 0);
            if ($composerManifest === false || !is_array($composerManifest)) {
                throw new RuntimeException(sprintf($composerManifestError, 'decoded'));
            }

            if (
                ($composerManifest['type'] ?? '') === 'typo3-cms-extension' ||
                ($composerManifest['extra']['typo3/cms']['extension-key'] ?? '') !== ''
            ) {
                $type = Setup::EXTENSION;
            } else {
                $type = Setup::PROJECT;
            }
        }

        return $type;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $this->getForce($input);
        $ruleSets = $this->getRuleSets($input);
        $type = $this->getType($input);

        $setup = new Setup($this->getTargetDir($input), new SymfonyStyle($input, $output));

        $result = true;

        if (\in_array(Setup::RULE_SET_EDITORCONFIG, $ruleSets, true)) {
            $result = $setup->copyEditorConfig($force);
        }

        if (\in_array(Setup::RULE_SET_PHP_CS_FIXER, $ruleSets, true)) {
            $result = $setup->copyPhpCsFixerConfig($force, $type) && $result;
        }

        return $result ? 0 : 1;
    }
}
