<?php
declare(strict_types = 1);
namespace TYPO3\CodingStandards;

/*
 * This file is part of the TYPO3 project  - inspiring people to share!
 * (c) 2019 Benni Mack
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

class Setup
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
        // copy php-cs-fixer configuration
        if (!$force
            && (file_exists($this->rootPath . '/.php_cs') || file_exists($this->rootPath . '/.php-cs-fixer.php'))
        ) {
            echo "A .php-cs-fixer.php or .php_cs file already exists in your main folder, but the -f option was not set. Nothing copied.\n";
            $errors = true;
        } else {
            copy($this->templatesPath . '/project_php-cs-fixer.dist.php', $this->rootPath . '/.php-cs-fixer.php');
        }
        return $errors ? 1 : 0;
    }

    public function forExtension(bool $force): int
    {
        $errors = $this->copyEditorConfig($force);
        // copy php-cs-fixer configuration
        if (!$force
            && (file_exists($this->rootPath . '/.php_cs') || file_exists($this->rootPath . '/.php-cs-fixer.php'))
        ) {
            echo "A .php-cs-fixer.php or .php_cs file already exists in your main folder, but the -f option was not set. Nothing copied.\n";
            $errors = true;
        } else {
            copy($this->templatesPath . '/extension_php-cs-fixer.dist.php', $this->rootPath . '/.php-cs-fixer.php');
        }
        return $errors ? 1 : 0;
    }

    private function copyEditorConfig(bool $force): bool
    {
        $errors = false;
        // copy editorconfig
        if (!$force && file_exists($this->rootPath . '/.editorconfig')) {
            echo "A .editorconfig file already exists in your main folder, but the -f option was not set. Nothing copied.\n";
            $errors = true;
        } else {
            copy($this->templatesPath . '/editorconfig.dist', $this->rootPath . '/.editorconfig');
        }
        return $errors;
    }
}
