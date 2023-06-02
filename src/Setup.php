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

use RuntimeException;
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
     * @var array<int, string>
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
     * @var array<int, string>
     */
    public const VALID_RULE_SETS = [self::RULE_SET_EDITORCONFIG, self::RULE_SET_PHP_CS_FIXER];

    private readonly StyleInterface $style;

    private readonly string $targetDir;

    /**
     * @var array<int, string>
     */
    private readonly array $templatesDirs;

    /**
     * @param array<int, string> $templatesDirs
     *
     * @throws RuntimeException
     */
    public function __construct(
        StyleInterface $style = null,
        string $targetDir = '',
        array $templatesDirs = [],
    ) {
        // Setup StyleInterface
        if (!$style instanceof StyleInterface) {
            $arrayInput = new ArrayInput([]);
            $arrayInput->setInteractive(false);
            $nullOutput = new NullOutput();
            $style = new SymfonyStyle($arrayInput, $nullOutput);
        }

        $this->style = $style;

        // Setup targetDir
        if ($targetDir === '') {
            $targetDir = '.';
        }

        if (!\is_dir($targetDir)) {
            throw new RuntimeException(sprintf("Target directory '%s' does not exist.", $targetDir));
        }

        // Normalize separators on Windows
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $targetDir = \str_replace('\\', '/', $targetDir); // @codeCoverageIgnore
        }

        $this->targetDir = \rtrim($targetDir, '/');

        // Setup templatesDirs
        $templatesDirs = [
            \dirname(__DIR__) . '/' . 'templates',
            ...$templatesDirs,
        ];

        foreach ($templatesDirs as &$templateDir) {
            if (!\is_dir($templateDir)) {
                throw new RuntimeException(sprintf("Templates directory '%s' does not exist.", $templateDir));
            }

            // Normalize separators on Windows
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $templateDir = \str_replace('\\', '/', $templateDir); // @codeCoverageIgnore
            }

            $templateDir = \rtrim($templateDir, '/');
        }

        $this->templatesDirs = $templatesDirs;
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

    /**
     * @throws RuntimeException
     */
    public function copyPhpCsFixerConfig(bool $force, string $type): bool
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new RuntimeException(sprintf('Invalid type (%s) specified.', $type));
        }

        $targetFileName = '.php-cs-fixer.dist.php';
        $targetFilePath = $this->targetDir . '/' . $targetFileName;

        if (!$force) {
            if (!file_exists($targetFilePath)) {
                if (file_exists($this->targetDir . '/.php_cs')) {
                    rename(
                        $this->targetDir . '/.php_cs',
                        $targetFilePath
                    );
                    $this->style->note('Deprecated .php_cs renamed to .php-cs-fixer.dist.php.');
                }

                if (file_exists($this->targetDir . '/.php-cs-fixer.php')) {
                    rename(
                        $this->targetDir . '/.php-cs-fixer.php',
                        $targetFilePath
                    );
                    $this->style->note('.php-cs-fixer.php renamed to .php-cs-fixer.dist.php.');
                }
            }

            if (file_exists($targetFilePath)) {
                $this->style->error(\sprintf(
                    'A %s file already exists, nothing copied. Use the --force option to overwrite the file.',
                    $targetFileName
                ));
                return false;
            }
        }

        if (file_exists($this->targetDir . '/.php_cs')) {
            unlink($this->targetDir . '/.php_cs');
            $this->style->note('Deprecated .php_cs removed.');
        }

        copy(
            $this->getTemplateFilePath($type . '_php-cs-fixer.dist.php'),
            $targetFilePath
        );
        $this->style->success(sprintf('%s created for %s.', $targetFileName, $type));

        return true;
    }

    public function copyEditorConfig(bool $force): bool
    {
        $targetFileName = '.editorconfig';
        $targetFilePath = $this->targetDir . '/' . $targetFileName;

        if (!$force && file_exists($targetFilePath)) {
            $this->style->error(\sprintf(
                'A %s file already exists, nothing copied. Use the update command or the --force option to overwrite the file.',
                $targetFileName
            ));
            return false;
        }

        copy(
            $this->getTemplateFilePath('editorconfig.dist'),
            $targetFilePath
        );
        $this->style->success(\sprintf('%s created.', $targetFileName));

        return true;
    }

    /**
     * @throws RuntimeException
     */
    public function getTemplateFilePath(string $filename): string
    {
        foreach (\array_reverse($this->templatesDirs) as $path) {
            if (\file_exists($path . '/' . $filename)) {
                return $path . '/' . $filename;
            }
        }

        throw new RuntimeException(\sprintf(
            "Template '%s' could not be found in the directories %s.",
            $filename,
            \implode(', ', $this->templatesDirs)
        ));
    }
}
