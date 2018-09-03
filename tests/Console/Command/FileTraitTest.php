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
     */
    public static function tearDownAfterClass()
    {
        vfsStreamWrapper::unregister();
    }
    
    /**
     * Test read file.
     *
     * @return void
     */
    public function testReadFile(): void
    {
        $trait = $this->getMockForTrait(FileTrait::class);

        static::assertInternalType(
            'string',
            static::invokeMethod(
                $trait,
                'readFile',
                [
                    new SplFileInfo(vfsStream::url('root/test.file')),
                ]
            )
        );
    }

    /**
     * Test read file should throw exception if file not exists.
     *
     * @return void
     * @expectedException \FinalGene\PhoneBook\Exception\ReadFileException
     */
    public function testReadFileShouldThrowExceptionIfFileNotExists(): void
    {
        $trait = $this->getMockForTrait(FileTrait::class);

        static::invokeMethod(
            $trait,
            'readFile',
            [
                new SplFileInfo('non-exiting.file'),
            ]
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
        $trait = $this->getMockForTrait(FileTrait::class);

        static::invokeMethod(
            $trait,
            'readFile',
            [
                new SplFileInfo(vfsStream::url('root/empty.file')),
            ]
        );
    }
}
