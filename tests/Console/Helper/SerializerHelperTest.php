<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Helper;

use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

/**
 * Serializer helper test class.
 *
 * @package FinalGene\PhoneBook\Console\Helper
 *
 * @covers  \FinalGene\PhoneBook\Console\Helper\SerializerHelper
 */
class SerializerHelperTest extends TestCase
{
    /**
     * Test get name.
     *
     * @return void
     */
    public function testGetName(): void
    {
        $helper = $this->createPartialMock(SerializerHelper::class, []);

        static::assertEquals(
            SerializerHelper::class,
            $helper->getName()
        );
    }

    /**
     * Test get avm serializer.
     *
     * @return void
     */
    public function testGetAvmSerializer(): void
    {
        $helper = $this->createPartialMock(SerializerHelper::class, []);

        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(
            Serializer::class,
            $helper->getAvmSerializer()
        );
    }
}
