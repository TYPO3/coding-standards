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

namespace TYPO3\CodingStandards\Console;

use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use TYPO3\CodingStandards\Console\Command\SetupCommand;
use TYPO3\CodingStandards\Console\Command\SetupExtensionCommand;
use TYPO3\CodingStandards\Console\Command\SetupProjectCommand;
use TYPO3\CodingStandards\Console\Command\UpdateCommand;

/**
 * @internal
 */
final class Application extends BaseApplication
{
    /**
     * @var string
     */
    public const VERSION = '0.6.0-DEV';

    /**
     * getcwd() equivalent which always returns a string
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

    public static function getProjectDir(): string
    {
        return self::getCwd(true);
    }

    /**
     * @throws RuntimeException
     */
    public static function getTargetDir(InputInterface $input): string
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

    public function __construct()
    {
        parent::__construct('TYPO3 Coding Standards', self::VERSION);

        // in alphabetical order
        $this->add(new SetupCommand());
        $this->add(new SetupExtensionCommand());
        $this->add(new SetupProjectCommand());
        $this->add(new UpdateCommand());

        //$this->setDefaultCommand('setup', false);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(new InputOption(
            '--target-dir',
            '-d',
            InputOption::VALUE_REQUIRED,
            'If specified, use the given directory as target directory',
            self::getProjectDir()
        ));

        return $inputDefinition;
    }
}
