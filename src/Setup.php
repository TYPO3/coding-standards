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

namespace TYPO3\CodingStandards;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Setup
{
    /**
     * @var string
     */
    public const EXTENSION = 'extension';

    /**
     * @var string
     */
    public const PROJECT = 'project';

    /**
     * @var string[]
     */
    public const VALID_TYPES = [self::EXTENSION, self::PROJECT];

    /**
     * @var string
     */
    public const RULE_SET_EDITORCONFIG = 'editorconfig';

    /**
     * @var string
     */
    public const RULE_SET_PHP_CS_FIXER = 'php-cs-fixer';

    /**
     * @var string[]
     */
    public const VALID_RULE_SETS = [self::RULE_SET_EDITORCONFIG, self::RULE_SET_PHP_CS_FIXER];

    private readonly string $targetDir;

    private readonly string $templatesPath;

    private readonly StyleInterface $style;

    public function __construct(string $targetDir, StyleInterface $style = null)
    {
        if ($targetDir === '') {
            $targetDir = '.'; // @codeCoverageIgnore
        }

        if (!\is_dir($targetDir)) {
            throw new \RuntimeException(sprintf("Target directory '%s' does not exist.", $targetDir));
        }

        // Normalize separators on Windows
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $targetDir = \str_replace('\\', '/', $targetDir); // @codeCoverageIgnore
        }

        $this->targetDir = \rtrim($targetDir, '/');
        $this->templatesPath = \dirname(__DIR__) . '/' . 'templates';

        if (!$style instanceof StyleInterface) {
            $arrayInput = new ArrayInput([]);
            $arrayInput->setInteractive(false);
            $nullOutput = new NullOutput();
            $style = new SymfonyStyle($arrayInput, $nullOutput);
        }

        $this->style = $style;
    }

    /**
     * @deprecated
     */
    public function forProject(bool $force): int
    {
        $result = $this->copyEditorConfig($force);
        $result = $this->copyPhpCsFixerConfig($force, self::PROJECT) && $result;

        return $result ? 0 : 1;
    }

    /**
     * @deprecated
     */
    public function forExtension(bool $force): int
    {
        $result = $this->copyEditorConfig($force);
        $result = $this->copyPhpCsFixerConfig($force, self::EXTENSION) && $result;

        return $result ? 0 : 1;
    }

    public function copyPhpCsFixerConfig(bool $force, string $type): bool
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \RuntimeException(sprintf('Invalid type (%s) specified.', $type));
        }

        $targetFile = '.php-cs-fixer.dist.php';
        $targetFilepath = $this->targetDir . '/' . $targetFile;

        if (!$force) {
            if (!file_exists($targetFilepath)) {
                if (file_exists($this->targetDir . '/.php_cs')) {
                    rename(
                        $this->targetDir . '/.php_cs',
                        $targetFilepath
                    );
                    $this->style->note('Deprecated .php_cs renamed to .php-cs-fixer.dist.php.');
                }

                if (file_exists($this->targetDir . '/.php-cs-fixer.php')) {
                    rename(
                        $this->targetDir . '/.php-cs-fixer.php',
                        $targetFilepath
                    );
                    $this->style->note('.php-cs-fixer.php renamed to .php-cs-fixer.dist.php.');
                }
            }

            if (file_exists($targetFilepath)) {
                $this->style->error(\sprintf(
                    'A %s file already exists, nothing copied. Use the --force option to overwrite the file.',
                    $targetFile
                ));
                return false;
            }
        }

        if (file_exists($this->targetDir . '/.php_cs')) {
            unlink($this->targetDir . '/.php_cs');
            $this->style->note('Deprecated .php_cs removed.');
        }

        copy(
            $this->templatesPath . '/' . $type . '_php-cs-fixer.dist.php',
            $targetFilepath
        );
        $this->style->success(sprintf('%s created for %s.', $targetFile, $type));

        return true;
    }

    public function copyEditorConfig(bool $force): bool
    {
        $targetFile = '.editorconfig';
        $targetFilepath = $this->targetDir . '/' . $targetFile;

        if (!$force && file_exists($targetFilepath)) {
            $this->style->error(\sprintf(
                'A %s file already exists, nothing copied. Use the update command or the --force option to overwrite the file.',
                $targetFile
            ));
            return false;
        }

        copy(
            $this->templatesPath . '/editorconfig.dist',
            $targetFilepath
        );
        $this->style->success(\sprintf('%s created.', $targetFile));

        return true;
    }
}
