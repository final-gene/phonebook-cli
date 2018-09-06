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
use SplFileInfo;

/**
 * File trait test class.
 *
 * @package FinalGene\PhoneBook\Console
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\FileTrait
 */
class FileTraitTest extends TestCase
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
                'test.file' => 'test',
                'empty.file' => '',
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
     * Test get resource for file.
     *
     * @return void
     */
    public function testGetResourceForFile(): void
    {
        $trait = $this->getMockForTrait(FileTrait::class);

        static::assertInternalType(
            'resource',
            static::invokeMethod(
                $trait,
                'getResourceForFile',
                [
                    new SplFileInfo(vfsStream::url('root/test.file')),
                ]
            )
        );
    }

    /**
     * Test get resource for file should throw exception if file not exists.
     *
     * @return void
     * @expectedException \FinalGene\PhoneBook\Exception\ReadFileException
     */
    public function testGetResourceForFileShouldThrowExceptionIfFileNotExists(): void
    {
        $trait = $this->getMockForTrait(FileTrait::class);

        static::invokeMethod(
            $trait,
            'getResourceForFile',
            [
                new SplFileInfo('non-exiting.file'),
            ]
        );
    }

    /**
     * Test read file.
     *
     * @return void
     */
    public function testReadFile(): void
    {
        $trait = $this->getMockForTrait(
            FileTrait::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getResourceForFile',
            ]
        );

        $trait->expects(static::once())
            ->method('getResourceForFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn(fopen(vfsStream::url('root/test.file'), 'rb'));

        static::assertInternalType(
            'string',
            static::invokeMethod(
                $trait,
                'readFile',
                [
                    $this->createMock(SplFileInfo::class),
                ]
            )
        );
    }

    /**
     * Test read file should throw exception if file has no content.
     *
     * @return void
     * @expectedException \FinalGene\PhoneBook\Exception\EmptyFileException
     */
    public function testReadFileShouldThrowExceptionIfFileHasNoContent(): void
    {
        $trait = $this->getMockForTrait(
            FileTrait::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getResourceForFile',
            ]
        );

        $trait->expects(static::once())
            ->method('getResourceForFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn(fopen(vfsStream::url('root/empty.file'), 'rb'));

        static::invokeMethod(
            $trait,
            'readFile',
            [
                $this->createMock(SplFileInfo::class),
            ]
        );
    }
}
