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

namespace TYPO3\CodingStandards\Build;

use Gilbertsoft\Composer\AbstractReleaseScripts;
use Gilbertsoft\Composer\Release\FileReplaceVersionItem;
use Iterator;

/**
 * @internal
 */
final class ReleaseScripts extends AbstractReleaseScripts
{
    protected static function getFiles(): Iterator
    {
        yield new FileReplaceVersionItem(
            '.ddev/config.yaml',
            '/(- COMPOSER_ROOT_VERSION=)\d+\.\d+\.\d+()/',
            FileReplaceVersionItem::VERSIONS_UP_TO_PATCH
        );
        yield new FileReplaceVersionItem(
            'Documentation/Settings.cfg',
            '/(release[ \t]+= )\d+\.\d+(\.x)/',
            FileReplaceVersionItem::VERSIONS_UP_TO_MINOR
        );
        yield new FileReplaceVersionItem(
            'Documentation/Settings.cfg',
            '/(version[ \t]+= )\d+\.\d+()/',
            FileReplaceVersionItem::VERSIONS_UP_TO_MINOR
        );
        yield new FileReplaceVersionItem(
            'src/Console/Application.php',
            '/(public const VERSION = \').*(\';)/',
            FileReplaceVersionItem::VERSIONS_ALL
        );
        yield new FileReplaceVersionItem(
            'composer.json',
            '/("dev-main": ")\d+\.\d+(.x-dev")/',
            FileReplaceVersionItem::VERSIONS_UP_TO_MINOR
        );
    }
}
