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

use TYPO3\CodingStandards\CsFixerConfig;

class CsFixerConfigTest extends TestCase
{
    public function testCreateReturnsCorrectClass(): void
    {
        /** @var object $config */
        $config = CsFixerConfig::create();
        self::assertInstanceOf(CsFixerConfig::class, $config);
        self::assertTrue($config->getRiskyAllowed());
        self::assertCount(52, $config->getRules());
    }

    public function testAddRules(): void
    {
        $subject = new CsFixerConfig();
        $subject->addRules(['test_config' => 'value']);

        self::assertArrayHasKey('test_config', $subject->getRules());
    }

    public function testSetHeaderSetHeaderOnly(): void
    {
        $subject = new CsFixerConfig();
        $subject->setHeader('test_header');

        self::assertArrayHasKey('header_comment', $subject->getRules());
        self::assertStringContainsString('test_header', $subject->getRules()['header_comment']['header']);
    }
}
