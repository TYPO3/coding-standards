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

namespace TYPO3\CodingStandards\EventListener;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Console\Event\Command\Setup\ExecuteEvent as SetupExecuteEvent;
use TYPO3\CodingStandards\Console\Event\Command\Update\ExecuteEvent as UpdateExecuteEvent;
use TYPO3\CodingStandards\Events;
use TYPO3\CodingStandards\Setup;

/**
 * @internal
 */
final class SetupSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Application $application)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::COMMAND_SETUP_EXECUTE => ['onSetupExecute', 127],
            Events::COMMAND_UPDATE_EXECUTE => ['onUpdateExecute', 127],
        ];
    }

    public function onSetupExecute(SetupExecuteEvent $setupExecuteEvent): void
    {
        $setup = $this->createSetup($setupExecuteEvent->getInput(), $setupExecuteEvent->getOutput());

        $result = true;

        if (\in_array(Setup::RULE_SET_EDITORCONFIG, $setupExecuteEvent->getRuleSets(), true)) {
            $result = $setup->copyEditorConfig($setupExecuteEvent->getForce());
        }

        if (\in_array(Setup::RULE_SET_PHP_CS_FIXER, $setupExecuteEvent->getRuleSets(), true)) {
            $result = $setup->copyPhpCsFixerConfig(
                $setupExecuteEvent->getForce(),
                $setupExecuteEvent->getType()
            ) && $result;
        }

        $setupExecuteEvent->setExitCode($result ? 0 : 1);
    }

    public function onUpdateExecute(UpdateExecuteEvent $updateExecuteEvent): void
    {
        $setup = $this->createSetup($updateExecuteEvent->getInput(), $updateExecuteEvent->getOutput());

        $result = $setup->copyEditorConfig(true);

        $updateExecuteEvent->setExitCode($result ? 0 : 1);
    }

    private function createSetup(InputInterface $input, OutputInterface $output): Setup
    {
        return new Setup(
            new SymfonyStyle($input, $output),
            $this->application->getTargetDir($input),
            $this->application->getTemplatesDirs()
        );
    }
}
