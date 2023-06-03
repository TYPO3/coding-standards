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

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
trait TypeTrait
{
    /**
     * @var string
     */
    private $type = '';

    protected function configureBefore(): void
    {
        $this->addArgument('type', InputArgument::OPTIONAL, sprintf(
            'Type to setup, valid types are <comment>["%s"]</comment>. If not set, the detection is automatic',
            implode('","', Setup::VALID_TYPES)
        ));
    }

    /**
     * @throws RuntimeException
     */
    private function getType(InputInterface $input): string
    {
        if ($this->type === '') {
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
                    $this->type = Setup::EXTENSION;
                } else {
                    $this->type = Setup::PROJECT;
                }
            } else {
                $this->type = $type;
            }
        }

        return $this->type;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->type = $this->getType($input);

        return parent::execute($input, $output);
    }
}
