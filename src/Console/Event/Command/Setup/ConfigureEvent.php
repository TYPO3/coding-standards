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

namespace TYPO3\CodingStandards\Console\Event\Command\Setup;

use TYPO3\CodingStandards\Console\Command\Command;

use TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent as BaseConfigureEvent;

/**
 * Allows to configure the SetupCommand.
 */
final class ConfigureEvent extends BaseConfigureEvent
{
    /**
     * @var array<int, string>
     */
    private array $additionalTypes = [];

    /**
     * @var array<int, string>
     */
    private array $additionalRuleSets = [];

    /**
     * @param array<int, string> $defaultRuleSets
     */
    public function __construct(
        Command $command,
        private array $defaultRuleSets,
    ) {
        parent::__construct($command);
    }

    /**
     * @return array<int, string>
     */
    public function getAdditionalTypes(): array
    {
        return $this->additionalTypes;
    }

    /**
     * @param array<int, string> $types
     */
    public function addAdditionalTypes(array $types): self
    {
        $this->additionalTypes = [
            ...$this->additionalTypes,
            ...$types,
        ];

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getDefaultRuleSets(): array
    {
        return $this->defaultRuleSets;
    }

    public function addDefaultRuleSet(string $defaultRuleSet): self
    {
        if (!\in_array($defaultRuleSet, $this->defaultRuleSets, true)) {
            $this->defaultRuleSets[] = $defaultRuleSet;
        }

        return $this;
    }

    public function removeDefaultRuleSet(string $defaultRuleSet): self
    {
        if (\in_array($defaultRuleSet, $this->defaultRuleSets, true)) {
            $defaultRuleSets = \array_flip($this->defaultRuleSets);
            unset($defaultRuleSets[$defaultRuleSet]);

            $this->defaultRuleSets = \array_values(\array_flip($defaultRuleSets));
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getAdditionalRuleSets(): array
    {
        return $this->additionalRuleSets;
    }

    /**
     * @param array<int, string> $ruleSets
     */
    public function addAdditionalRuleSets(array $ruleSets): self
    {
        $this->additionalRuleSets = [
            ...$this->additionalRuleSets,
            ...$ruleSets,
        ];

        return $this;
    }
}
