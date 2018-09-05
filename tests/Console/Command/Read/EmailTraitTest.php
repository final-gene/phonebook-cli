<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * E mail trait test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\Read\EmailTrait
 */
class EmailTraitTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test normalize email type.
     *
     * @param string $type     Email type
     * @param string $expected Expected result
     *
     * @return void
     *
     * @dataProvider dataForNormalizeEmailTypeTest
     */
    public function testNormalizeEmailType(string $type, string $expected): void
    {
        $trait = $this->getMockForTrait(EmailTrait::class);

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $trait,
                'normalizeEmailType',
                [
                    $type,
                ]
            )
        );
    }

    /**
     * Data for normalize email type test.
     *
     * @return array
     */
    public function dataForNormalizeEmailTypeTest(): array
    {
        return [
            'keep home as is' => [
                'type' => 'home',
                'expected' => 'home',
            ],
            'keep work as is' => [
                'type' => 'work',
                'expected' => 'work',
            ],
            'map something with home to home' => [
                'type' => 'something-with-home',
                'expected' => 'home',
            ],
            'map something with work to work' => [
                'type' => 'something-with-work',
                'expected' => 'work',
            ],
            'remove any thing else' => [
                'type' => 'any thing else',
                'expected' => '',
            ],
        ];
    }
}
