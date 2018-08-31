<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;

/**
 * V card trait test class.
 *
 * @package FinalGene\PhoneBook\Console
 *
 * @covers  \FinalGene\PhoneBook\Console\VCardTrait
 */
class VCardTraitTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test create vcard.
     *
     * @return void
     */
    public function testCreateVCard(): void
    {
        $trait = $this->getMockForTrait(VCardTrait::class);

        static::assertInstanceOf(
            VCard::class,
            static::invokeMethod(
                $trait,
                'createVCard'
            )
        );
    }
}
