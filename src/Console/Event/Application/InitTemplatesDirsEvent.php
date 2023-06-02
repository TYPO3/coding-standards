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

namespace TYPO3\CodingStandards\Console\Event\Application;

use TYPO3\CodingStandards\Console\Application;

/**
 * Allows to add or remove templates directories.
 */
final class InitTemplatesDirsEvent extends Event
{
    /**
     * @param array<int, string> $templatesDirs
     */
    public function __construct(Application $application, private array $templatesDirs)
    {
        parent::__construct($application);
    }

    /**
     * @return array<int, string>
     */
    public function getTemplatesDirs(): array
    {
        return $this->templatesDirs;
    }

    public function addTemplatesDir(string $templatesDir): self
    {
        if (!\in_array($templatesDir, $this->templatesDirs, true)) {
            $this->templatesDirs[] = $templatesDir;
        }

        return $this;
    }

    public function removeTemplatesDir(string $templatesDir): self
    {
        if (\in_array($templatesDir, $this->templatesDirs, true)) {
            $templatesDirs = \array_flip($this->templatesDirs);
            unset($templatesDirs[$templatesDir]);

            $this->templatesDirs = \array_values(\array_flip($templatesDirs));
        }

        return $this;
    }
}
