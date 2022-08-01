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

/**
 * @param string $file
 * @return mixed
 */
function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }

    return false;
}

if (($loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) === false) {
    die('You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL);
}
