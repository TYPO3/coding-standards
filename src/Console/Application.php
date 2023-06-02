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

namespace TYPO3\CodingStandards\Console;

use Composer\Autoload\ClassLoader;
use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use TYPO3\CodingStandards\Console\Event\Application\CreatedEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitCommandsEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitTemplatesDirsEvent;
use TYPO3\CodingStandards\EventListener\InitSubscriber;
use TYPO3\CodingStandards\EventListener\SetupSubscriber;
use TYPO3\CodingStandards\Events;
use TYPO3\CodingStandards\Plugins;

/**
 * @internal
 */
final class Application extends BaseApplication
{
    /**
     * @var string
     */
    public const VERSION = '0.8.0-DEV';

    private readonly EventDispatcher $eventDispatcher;

    private bool $commandsInitialized = \false;

    /**
     * @var array<int, string>
     */
    private array $templatesDirs = [];

    private ?Plugins $plugins = \null;

    /**
     * @var array<int, string>
     */
    private array $pluginsLoaded = [];

    /**
     * getcwd() equivalent which always returns a string.
     *
     * @throws RuntimeException
     */
    private static function getCwd(bool $allowEmpty = false): string
    {
        $cwd = getcwd();

        // @codeCoverageIgnoreStart
        // fallback to realpath('') just in case this works but odds are it would break as well if we are in a case
        // where getcwd fails
        if ($cwd === false) {
            $cwd = realpath('');
        }

        // crappy state, assume '' and hopefully relative paths allow things to continue
        if ($cwd === false) {
            if ($allowEmpty) {
                return '';
            }

            throw new RuntimeException('Could not determine the current working directory');
        }

        // @codeCoverageIgnoreEnd

        return $cwd;
    }

    public function __construct(private readonly ?ClassLoader $classLoader = null)
    {
        parent::__construct('TYPO3 Coding Standards', self::VERSION);

        $this->eventDispatcher = new EventDispatcher();
        $this->eventDispatcher->addSubscriber(new InitSubscriber());
        $this->eventDispatcher->addSubscriber(new SetupSubscriber($this));

        $this->setDispatcher($this->eventDispatcher);

        if ($this->classLoader instanceof ClassLoader) {
            $this->plugins = new Plugins(
                $this->classLoader,
                $this->eventDispatcher,
            );
            $this->pluginsLoaded = $this->plugins->load();
        }

        $createdEvent = new CreatedEvent($this);
        $this->eventDispatcher->dispatch($createdEvent, Events::APPLICATION_CREATED);
    }

    /**
     * @inheritDoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $this->initCommands();

        return parent::run($input, $output);
    }

    public function initCommands(): void
    {
        if ($this->commandsInitialized) {
            return;
        }

        $initCommandsEvent = new InitCommandsEvent($this);
        $this->eventDispatcher->dispatch($initCommandsEvent, Events::APPLICATION_INIT_COMMANDS);

        $this->commandsInitialized = \true;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getLongVersion(): string
    {
        if ($this->pluginsLoaded !== []) {
            return \sprintf(
                "%s\n\nPlugin(s) loaded:\n  <info>%s</info>",
                parent::getLongVersion(),
                \implode("\n  ", $this->pluginsLoaded)
            );
        }

        return parent::getLongVersion();
    }

    public function getProjectDir(): string
    {
        return self::getCwd(true);
    }

    /**
     * @throws RuntimeException
     */
    public function getTargetDir(InputInterface $input): string
    {
        /** @var string|null $targetDir */
        $targetDir = $input->getParameterOption(['--target-dir', '-d'], null, true);

        if ($targetDir === null) {
            $targetDir = self::getProjectDir();
        }

        if (!(new Filesystem())->isAbsolutePath($targetDir)) {
            $targetDir = self::getProjectDir() . '/' . $targetDir;
        }

        if (!is_dir($targetDir)) {
            throw new RuntimeException(\sprintf('Invalid target directory specified, %s does not exist.', $targetDir));
        }

        return $targetDir;
    }

    /**
     * @return array<int, string>
     */
    public function getTemplatesDirs(): array
    {
        if ($this->templatesDirs === []) {
            $initTemplatesDirsEvent = new InitTemplatesDirsEvent($this, $this->templatesDirs);
            $this->eventDispatcher->dispatch($initTemplatesDirsEvent, Events::APPLICATION_INIT_TEMPLATES_DIRS);
            $this->templatesDirs = $initTemplatesDirsEvent->getTemplatesDirs();
        }

        return $this->templatesDirs;
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $initDefaultInputDefinitionEvent = new InitDefaultInputDefinitionEvent(
            $this,
            parent::getDefaultInputDefinition()
        );
        $this->eventDispatcher->dispatch(
            $initDefaultInputDefinitionEvent,
            Events::APPLICATION_INIT_DEFAULT_INPUT_DEFINITION
        );

        return $initDefaultInputDefinitionEvent->getDefaultInputDefinition();
    }
}
