<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2021 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    private $fixturePath;

    /**
     * @var string
     */
    private $testPath;

    /**
     * @var string
     */
    private $templatePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->fixturePath = __DIR__ . '/Fixtures';
        $this->testPath = dirname(__DIR__, 2) . '/var/tests';
        $this->templatePath = dirname(__DIR__, 2) . '/templates';

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->testPath);
    }

    protected function tearDown(): void
    {
        if ($this->filesystem->exists($this->testPath)) {
            $this->filesystem->remove($this->testPath);
        }
    }

    protected function getFilename(string $filename): string
    {
        [$prefix, $filename] = explode(':', $filename, 2);

        switch ($prefix) {
            case 'TPL':
                return $this->getTemplateFilename($filename);

            case 'FIX':
                return $this->getFixtureFilename($filename);

            default:
                throw new \RuntimeException(sprintf('Invalid prefix (%s).', $prefix), 1636451407);
        }
    }

    protected function getFixturePath(): string
    {
        return $this->fixturePath;
    }

    protected function getFixtureFilename(string $filename): string
    {
        return $this->fixturePath . '/' . $filename;
    }

    protected function getTestPath(?string $subFolder = null): string
    {
        if ($subFolder !== null) {
            return $this->testPath . '/' . $subFolder;
        }

        return $this->testPath;
    }

    protected function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    protected function getTemplateFilename(string $filename): string
    {
        return $this->templatePath . '/' . $filename;
    }

    protected function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }
}
