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
 * Phone number trait test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\Read\PhoneNumberTrait
 */
class PhoneNumberTraitTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test normalize phone number type.
     *
     * @param string $phoneNumber
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider dataForNormalizePhoneNumberTest
     */
    public function testNormalizePhoneNumber(string $phoneNumber, string $expected): void
    {
        $trait = $this->getMockForTrait(PhoneNumberTrait::class);

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $trait,
                'normalizePhoneNumber',
                [
                    $phoneNumber,
                ]
            )
        );
    }

    /**
     * Data for normalize phone number test.
     *
     * @return array
     */
    public function dataForNormalizePhoneNumberTest(): array
    {
        return [
            'simple phone number' => [
                'phoneNumber' => '0123456789',
                'expected' => '+49123456789',
            ],
            'international german phone number' => [
                'phoneNumber' => '0049123456789',
                'expected' => '+49123456789',
            ],
            'international us phone number' => [
                'phoneNumber' => '001123456789',
                'expected' => '+1123456789',
            ],
        ];
    }

    /**
     * Test normalize phone number type.
     *
     * @param string $type
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider dataForNormalizePhoneNumberTypeTest
     */
    public function testNormalizePhoneNumberType(string $type, string $expected): void
    {
        $trait = $this->getMockForTrait(PhoneNumberTrait::class);

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $trait,
                'normalizePhoneNumberType',
                [
                    $type,
                ]
            )
        );
    }

    /**
     * Data for normalize phone number type test.
     *
     * @return array
     */
    public function dataForNormalizePhoneNumberTypeTest(): array
    {
        return [
            'keep text as is' => [
                'type' => 'text',
                'expected' => 'text',
            ],
            'keep fax as is' => [
                'type' => 'fax',
                'expected' => 'fax',
            ],
            'keep cell as is' => [
                'type' => 'cell',
                'expected' => 'cell',
            ],
            'keep voice as is' => [
                'type' => 'voice',
                'expected' => 'voice',
            ],
            'keep video as is' => [
                'type' => 'video',
                'expected' => 'video',
            ],
            'keep pager as is' => [
                'type' => 'pager',
                'expected' => 'pager',
            ],
            'keep home as is' => [
                'type' => 'home',
                'expected' => 'home',
            ],
            'keep work as is' => [
                'type' => 'work',
                'expected' => 'work',
            ],
            'map mobile to cell' => [
                'type' => 'mobile',
                'expected' => 'cell',
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
