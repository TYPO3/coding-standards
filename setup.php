#!/usr/bin/env php
<?php
declare(strict_types = 1);

$setup = function($scriptPath, string $type = null, $forceOption = null) {
    $rootPath = getcwd();
    require $rootPath . '/vendor/autoload.php';
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
