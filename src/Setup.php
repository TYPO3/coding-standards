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

final class Setup
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $templatesPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->templatesPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
    }

    public function forProject(bool $force): int
    {
        $errors = $this->copyEditorConfig($force);
        $errors = $this->copyPhpCsFixerConfig($force, 'project') || $errors;

        return $errors ? 1 : 0;
    }

    public function forExtension(bool $force): int
    {
        $errors = $this->copyEditorConfig($force);
        $errors = $this->copyPhpCsFixerConfig($force, 'extension') || $errors;

        return $errors ? 1 : 0;
    }

    private function copyPhpCsFixerConfig(bool $force, string $typePrefix): bool
    {
        $errors = false;

        if (!$force && file_exists($this->rootPath . '/.php_cs') && !file_exists($this->rootPath . '/.php-cs-fixer.dist.php')) {
            rename($this->rootPath . '/.php_cs', $this->rootPath . '/.php-cs-fixer.dist.php');
            echo "Found deprecated .php_cs file and renamed it to .php-cs-fixer.dist.php.\n";
        }

        if (!$force && file_exists($this->rootPath . '/.php-cs-fixer.php') && !file_exists($this->rootPath . '/.php-cs-fixer.dist.php')) {
            rename($this->rootPath . '/.php-cs-fixer.php', $this->rootPath . '/.php-cs-fixer.dist.php');
            echo "Found .php-cs-fixer.php file and renamed it to .php-cs-fixer.dist.php.\n";
        }

        if (
            !$force
            && (file_exists($this->rootPath . '/.php_cs') || file_exists($this->rootPath . '/.php-cs-fixer.dist.php'))
        ) {
            echo "A .php-cs-fixer.dist.php file already exists in your main folder, but the -f option was not set. Nothing copied.\n";
            $errors = true;
        } else {
            copy($this->templatesPath . '/' . $typePrefix . '_php-cs-fixer.dist.php', $this->rootPath . '/.php-cs-fixer.dist.php');

            if (file_exists($this->rootPath . '/.php_cs')) {
                unlink($this->rootPath . '/.php_cs');
            }
        }

        return $errors;
    }

    private function copyEditorConfig(bool $force): bool
    {
        $errors = false;

        if (!$force && file_exists($this->rootPath . '/.editorconfig')) {
            echo "A .editorconfig file already exists in your main folder, but the -f option was not set. Nothing copied.\n";
            $errors = true;
        } else {
            copy($this->templatesPath . '/editorconfig.dist', $this->rootPath . '/.editorconfig');
        }

        return $errors;
    }
}
