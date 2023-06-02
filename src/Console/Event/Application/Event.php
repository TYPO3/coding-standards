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

use Symfony\Contracts\EventDispatcher\Event as BaseEvent;
use TYPO3\CodingStandards\Console\Application;

/**
 * Allows to access the Application object.
 */
abstract class Event extends BaseEvent
{
    public function __construct(private readonly Application $application)
    {
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
