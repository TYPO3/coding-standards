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

use Symfony\Component\Console\Input\InputDefinition;
use TYPO3\CodingStandards\Console\Application;

/**
 * Allows to modify the default input definition.
 */
final class InitDefaultInputDefinitionEvent extends Event
{
    public function __construct(
        Application $application,
        private readonly InputDefinition $inputDefinition
    ) {
        parent::__construct($application);
    }

    public function getDefaultInputDefinition(): InputDefinition
    {
        return $this->inputDefinition;
    }
}
