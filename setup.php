#!/usr/bin/env php
<?php
declare(strict_types = 1);

$setup = function($scriptPath, string $type = null, $forceOption = null) {
    $rootPath = getcwd();

    $dir = \dirname(__DIR__);
    while (!file_exists($dir . '/autoload.php')) {
        if ($dir === $rootPath) {
            exit(1);
        }
        $dir = \dirname($dir);
    }
    require $dir . '/autoload.php';

    $obj = new \TYPO3\CodingStandards\Setup($rootPath);
    switch ($type) {
        case 'extension':
            return $obj->forExtension($forceOption === '-f');
        case 'project':
            return $obj->forProject($forceOption === '-f');
        default:
            echo "You need to specify at least one option. Use 'extension' or 'project'.\n";
            exit(1);
    }
};
$setup(...$_SERVER['argv']);
