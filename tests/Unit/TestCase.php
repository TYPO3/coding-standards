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

namespace TYPO3\CodingStandards\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

abstract class TestCase extends BaseTestCase
{
    private static string $rootPath;

    /**
     * @var string
     */
    private static $fixturePath;

    private static string $testPath;

    private static string $templatePath;

    private static Filesystem $filesystem;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$rootPath = dirname(__DIR__, 2);
        self::$fixturePath = __DIR__ . '/Fixtures';
        self::$testPath = self::$rootPath . '/../tests';
        self::$templatePath = self::$rootPath . '/templates';

        self::$filesystem = new Filesystem();
        self::$filesystem->mkdir(self::$testPath);
    }

    protected function tearDown(): void
    {
        if (self::$filesystem->exists(self::$testPath)) {
            self::$filesystem->remove(self::$testPath);
        }

        parent::tearDown();
    }

    protected static function getRootPath(): string
    {
        return self::$rootPath;
    }

    /**
     * @param array<string, string>|null $replacePairs
     */
    protected static function getFilename(string $filename, ?array $replacePairs = null): string
    {
        if ($replacePairs !== null) {
            $filename = strtr($filename, $replacePairs);
        }

        [$prefix, $filename] = explode(':', $filename, 2);

        return match ($prefix) {
            'TPL' => self::getTemplateFilename($filename),
            'FIX' => self::getFixtureFilename($filename),
            default => throw new RuntimeException(sprintf('Invalid prefix (%s).', $prefix), 1_636_451_407),
        };
    }

    protected static function getFixturePath(): string
    {
        return self::$fixturePath;
    }

    protected static function getFixtureFilename(string $filename): string
    {
        return self::$fixturePath . '/' . $filename;
    }

    protected static function getTestPath(?string $subFolder = null): string
    {
        $filesystem = self::getFilesystem();

        $testPath = $filesystem->tempnam(self::$testPath, 'test_');

        if ($subFolder !== null) {
            $testPath .= '/' . $subFolder;
        }

        $filesystem->remove($testPath);
        $filesystem->mkdir($testPath);
        \chdir($testPath);

        return $testPath;
    }

    protected static function getTemplatePath(): string
    {
        return self::$templatePath;
    }

    protected static function getTemplateFilename(string $filename): string
    {
        return self::$templatePath . '/' . $filename;
    }

    protected static function getFilesystem(): Filesystem
    {
        return self::$filesystem;
    }

    /**
     * @param array<string, string> $files
     */
    protected static function createFiles(string $testPath, array $files): void
    {
        $filesystem = self::getFilesystem();

        foreach ($files as $target => $source) {
            $filesystem->copy(static::getFilename($source), $testPath . '/' . $target);
        }
    }
}
