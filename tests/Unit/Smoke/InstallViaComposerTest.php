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

namespace TYPO3\CodingStandards\Tests\Unit\Smoke;

use Keradus\CliExecutor\CommandExecutor;
use TYPO3\CodingStandards\Console\Application;
use TYPO3\CodingStandards\Tests\Unit\TestCase;

/**
 * @internal
 *
 * @coversNothing
 * @group covers-nothing
 * @large
 */
final class InstallViaComposerTest extends TestCase
{
    /**
     * @var string[]
     */
    private const STEPS_TO_VERIFY_INSTALLATION = [
        // Confirm we can install.
        'composer install -q',
        // Ensure that autoloader works.
        'composer dump-autoload --optimize',
        'php vendor/autoload.php',
        // Ensure basic commands work.
        'vendor/bin/typo3-coding-standards --version',
        'vendor/bin/typo3-coding-standards setup --help',
        'vendor/bin/t3-cs --version',
    ];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if ('\\' === \DIRECTORY_SEPARATOR) {
            self::markTestIncomplete('This test is broken on Windows');
        }

        try {
            CommandExecutor::create('php --version', __DIR__)->getResult();
        } catch (\RuntimeException $runtimeException) {
            self::markTestIncomplete('Missing `php` env script. Details:' . "\n" . $runtimeException->getMessage());
        }

        try {
            CommandExecutor::create('composer --version', __DIR__)->getResult();
        } catch (\RuntimeException $runtimeException) {
            self::markTestIncomplete('Missing `composer` env script. Details:' . "\n" . $runtimeException->getMessage());
        }

        try {
            CommandExecutor::create('composer check', self::getRootPath())->getResult();
        } catch (\RuntimeException $runtimeException) {
            self::markTestIncomplete('Composer check failed. Details:' . "\n" . $runtimeException->getMessage());
        }
    }

    public function testInstallationViaPathIsPossible(): void
    {
        $filesystem = self::getFilesystem();

        $tmpPath = self::getTestPath();

        $initialComposerFileState = [
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => self::getRootPath(),
                ],
            ],
            'require' => [
                'typo3/coding-standards' => '*@dev',
            ],
        ];

        file_put_contents(
            $tmpPath . '/composer.json',
            json_encode($initialComposerFileState, JSON_PRETTY_PRINT)
        );

        static::assertCommandsWork(self::STEPS_TO_VERIFY_INSTALLATION, $tmpPath);

        $filesystem->remove($tmpPath);
    }

    // test that respects `export-ignore` from `.gitattributes` file
    public function testInstallationViaArtifactIsPossible(): void
    {
        // Composer Artifact Repository requires `zip` extension
        if (!\extension_loaded('zip')) {
            self::markTestIncomplete('No zip extension available.');
        }

        $tmpPath = self::getTestPath();
        $tmpArtifactPath = self::getTestPath();

        $fakeVersion = preg_replace('#\-.+#', '', Application::VERSION, 1) . '-alpha987654321';

        $initialComposerFileState = [
            'repositories' => [
                [
                    'type' => 'artifact',
                    'url' => $tmpArtifactPath,
                ],
            ],
            'require' => [
                'typo3/coding-standards' => $fakeVersion,
            ],
        ];

        file_put_contents(
            $tmpPath . '/composer.json',
            json_encode($initialComposerFileState, JSON_PRETTY_PRINT)
        );

        $cwd = self::getRootPath();

        $stepsToInitializeArtifact = [
            // Clone current version of project to new location, as we are going to modify it.
            // Warning! Only already committed changes will be cloned!
            sprintf('git clone --depth=1 . %s', $tmpArtifactPath),
        ];

        $stepsToPrepareArtifact = [
            // Configure git user for new repo to not use global git user.
            // We need this, as global git user may not be set!
            'git config user.name test && git config user.email test',
            // Adjust cloned project to expose version in `composer.json`.
            // Without that, it would not be possible to use it as Composer Artifact.
            sprintf("composer config version %s && git add . && git commit --no-gpg-sign -m 'provide version'", $fakeVersion),
            // Create repo archive that will serve as Composer Artifact.
            'git archive HEAD --format=zip -o archive.zip',
            // Drop the repo, keep the archive
            'git rm -r . && rm -rf .git',
        ];

        static::assertCommandsWork($stepsToInitializeArtifact, $cwd);
        static::assertCommandsWork($stepsToPrepareArtifact, $tmpArtifactPath);
        static::assertCommandsWork(self::STEPS_TO_VERIFY_INSTALLATION, $tmpPath);
    }

    /**
     * @param array<int, string> $commands
     */
    private static function assertCommandsWork(array $commands, string $cwd): void
    {
        foreach ($commands as $command) {
            self::assertSame(0, CommandExecutor::create($command, $cwd)->getResult()->getCode());
        }
    }
}
