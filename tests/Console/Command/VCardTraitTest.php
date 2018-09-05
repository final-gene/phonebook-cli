<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Splitter\VCard as VCardList;
use SplFileInfo;

/**
 * vCard trait test class.
 *
 * @package FinalGene\PhoneBook\Console
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\VCardTrait
 */
class VCardTraitTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Set up before class
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        vfsStream::setup(
            'root',
            0444,
            [
                'test.vcf' => <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:Dr. Erika Mustermann
END:VCARD
VCF
                ,
            ]
        );
    }

    /**
     * Tear down after class
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        vfsStreamWrapper::unregister();
    }

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

    /**
     * Test get vcard list by file.
     *
     * @return void
     */
    public function testGetVCardListByFile(): void
    {
        $trait = $this->getMockForTrait(VCardTrait::class);

        /** @var VCard $result */
        $result = static::invokeMethod(
            $trait,
            'getVCardListByFile',
            [
                new SplFileInfo(vfsStream::url('root/test.vcf')),
            ]
        );

        static::assertInstanceOf(VCardList::class, $result);
    }
}
