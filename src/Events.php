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

use TYPO3\CodingStandards\Console\Event\Application\CreatedEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitCommandsEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent;
use TYPO3\CodingStandards\Console\Event\Application\InitTemplatesDirsEvent;
use TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent;
use TYPO3\CodingStandards\Console\Event\Command\Setup\ConfigureEvent as SetupConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\Setup\ExecuteEvent as SetupExecuteEvent;
use TYPO3\CodingStandards\Console\Event\Command\Update\ConfigureEvent as UpdateConfigureEvent;
use TYPO3\CodingStandards\Console\Event\Command\Update\ExecuteEvent as UpdateExecuteEvent;

/**
 * Contains all events dispatched by the Application.
 */
final class Events
{
    /**
     * @var string
     */
    private const PREFIX = 'typo3.coding_standards.';

    /**
     * The APPLICATION_CREATED event allows you to modify the Application object.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Application\CreatedEvent")
     *
     * @var string
     */
    public const APPLICATION_CREATED = self::PREFIX . 'application.created';

    /**
     * The APPLICATION_INIT_COMMANDS event allows you to add more commands to
     * the application.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Application\InitCommandsEvent")
     *
     * @var string
     */
    public const APPLICATION_INIT_COMMANDS = self::PREFIX . 'application.init.commands';

    /**
     * The APPLICATION_INIT_DEFAULT_INPUT_DEFINITION event allows you to modify
     * the default input definition of the application.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Application\InitDefaultInputDefinitionEvent")
     *
     * @var string
     */
    public const APPLICATION_INIT_DEFAULT_INPUT_DEFINITION =
        self::PREFIX . 'application.init.default_input_definition';

    /**
     * The APPLICATION_INIT_TEMPLATES_DIRS event allows you to add or remove
     * templates directories.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Application\InitTemplatesDirsEvent")
     *
     * @var string
     */
    public const APPLICATION_INIT_TEMPLATES_DIRS = self::PREFIX . 'application.init.templates_dirs';

    /**
     * The COMMAND_CONFIGURE event allows you to modify the command while it's
     * created.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\ConfigureEvent")
     *
     * @var string
     */
    public const COMMAND_CONFIGURE = self::PREFIX . 'command.configure';

    /**
     * The COMMAND_EXECUTE event allows you to enhance the command execution.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\ExecuteEvent")
     *
     * @var string
     */
    public const COMMAND_EXECUTE = self::PREFIX . 'command.execute';

    /**
     * The COMMAND_SETUP_CONFIGURE event allows you to modify the SetupCommand
     * while it's created.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\Setup\ConfigureEvent")
     *
     * @var string
     */
    public const COMMAND_SETUP_CONFIGURE = self::PREFIX . 'command.setup.configure';

    /**
     * The COMMAND_SETUP_EXECUTE event allows you to enhance the SetupCommand
     * execution.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\Setup\ExecuteEvent")
     *
     * @var string
     */
    public const COMMAND_SETUP_EXECUTE = self::PREFIX . 'command.setup.execute';

    /**
     * The COMMAND_UPDATE_CONFIGURE event allows you to modify the UpdateCommand
     * while it's created.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\Update\ConfigureEvent")
     *
     * @var string
     */
    public const COMMAND_UPDATE_CONFIGURE = self::PREFIX . 'command.update.configure';

    /**
     * The COMMAND_UPDATE_EXECUTE event allows you to enhance the UpdateCommand
     * execution.
     *
     * @Event("TYPO3\CodingStandards\Console\Event\Command\Update\ExecuteEvent")
     *
     * @var string
     */
    public const COMMAND_UPDATE_EXECUTE = self::PREFIX . 'command.update.execute';

    /**
     * Event aliases.
     *
     * These aliases can be consumed by RegisterListenersPass.
     *
     * @var array<string, string>
     */
    public const ALIASES = [
        // Application specific events
        CreatedEvent::class => self::APPLICATION_CREATED,
        InitCommandsEvent::class => self::APPLICATION_INIT_COMMANDS,
        InitDefaultInputDefinitionEvent::class => self::APPLICATION_INIT_DEFAULT_INPUT_DEFINITION,
        InitTemplatesDirsEvent::class => self::APPLICATION_INIT_TEMPLATES_DIRS,

        // Command specific events
        ConfigureEvent::class => self::COMMAND_CONFIGURE,
        ExecuteEvent::class => self::COMMAND_EXECUTE,
        SetupConfigureEvent::class => self::COMMAND_SETUP_CONFIGURE,
        SetupExecuteEvent::class => self::COMMAND_SETUP_EXECUTE,
        UpdateConfigureEvent::class => self::COMMAND_UPDATE_CONFIGURE,
        UpdateExecuteEvent::class => self::COMMAND_UPDATE_EXECUTE,
    ];
}
