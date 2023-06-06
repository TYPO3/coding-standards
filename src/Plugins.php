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

namespace TYPO3\CodingStandards;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TYPO3\CodingStandards\Plugin\PluginInterface;

/**
 * @internal
 */
final class Plugins
{
    /**
     * @var string
     */
    private const PLUGIN_TYPE = 'coding-standards-plugin';

    /**
     * @var array<string, PluginInterface>
     */
    private array $registered = [];

    public function __construct(
        private readonly ClassLoader $classLoader,
        private readonly EventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function load(): array
    {
        $plugins = InstalledVersions::getInstalledPackagesByType(self::PLUGIN_TYPE);

        if ($plugins === []) {
            return [];
        }

        $pluginsRegistered = [];
        $classMap = $this->classLoader->getClassMap();

        foreach ($plugins as $plugin) {
            $installPath = InstalledVersions::getInstallPath($plugin);

            if ($installPath === \null) {
                continue;
            }

            $installPath = \realpath($installPath);

            if ($installPath === \false) {
                continue;
            }

            foreach ($classMap as $className => $path) {
                if (isset($this->registered[$className])) {
                    continue;
                }

                $path = \realpath($path);

                if ($path === \false) {
                    continue;
                }

                if (!\str_starts_with($path, $installPath)) {
                    continue;
                }

                if (!\class_exists($className)) {
                    continue;
                }

                if (!\in_array(PluginInterface::class, \class_implements($className), \true)) {
                    continue;
                }

                $subscriber = $className::create();

                if (!$subscriber instanceof PluginInterface) {
                    continue;
                }

                $this->eventDispatcher->addSubscriber($subscriber);

                $this->registered[$className] = $subscriber;

                if (!\in_array($plugin, $pluginsRegistered, true)) {
                    $pluginsRegistered[] = $plugin;
                }
            }
        }

        return $pluginsRegistered;
    }
}
