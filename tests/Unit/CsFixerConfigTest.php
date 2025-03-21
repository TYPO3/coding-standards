<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 project.
 *
 * (c) 2019-2024 Benni Mack
 *               Simon Gilli
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CodingStandards\Tests\Unit;

use TYPO3\CodingStandards\CsFixerConfig;

#[\PHPUnit\Framework\Attributes\CoversClass(CsFixerConfig::class)]
final class CsFixerConfigTest extends TestCase
{
    public function testCreateReturnsCorrectClass(): void
    {
        $csFixerConfig = CsFixerConfig::create();
        self::assertTrue($csFixerConfig->getRiskyAllowed());
        self::assertCount(54, $csFixerConfig->getRules());
    }

    public function testAddRules(): void
    {
        $csFixerConfig = new CsFixerConfig();
        $csFixerConfig->addRules(['test_config' => 'value']);

        self::assertArrayHasKey('test_config', $csFixerConfig->getRules());
    }

    public function testSetHeaderSetHeaderOnly(): void
    {
        $csFixerConfig = new CsFixerConfig();
        $csFixerConfig->setHeader('test_header');

        self::assertArrayHasKey('header_comment', $csFixerConfig->getRules());
        self::assertIsArray($csFixerConfig->getRules()['header_comment']);
        self::assertArrayHasKey('header', $csFixerConfig->getRules()['header_comment']);
        self::assertIsString($csFixerConfig->getRules()['header_comment']['header']);
        self::assertStringContainsString('test_header', $csFixerConfig->getRules()['header_comment']['header']);
    }
}
