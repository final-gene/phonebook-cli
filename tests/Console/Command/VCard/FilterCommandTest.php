<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\VCard;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Filter command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\VCard\FilterCommand
 */
class FilterCommandTest extends TestCase
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
     */
    public static function tearDownAfterClass()
    {
        vfsStreamWrapper::unregister();
    }

    /**
     * Test create reader command configuration.
     *
     * @return void
     */
    public function testCreateReaderCommandConfiguration(): void
    {
        $command = new FilterCommand();

        static::assertSame(FilterCommand::NAME, $command->getName());
        static::assertSame(FilterCommand::DESCRIPTION, $command->getDescription());
    }

    /**
     * Test create reader command initialization.
     *
     * @return void
     */
    public function testCreateReaderCommandInitialization(): void
    {
        $command = $this->createPartialMock(FilterCommand::class, []);

        static::invokeMethod(
            $command,
            'initialize',
            [
                $this->createMock(InputInterface::class),
                new NullOutput(),
            ]
        );

        static::assertInstanceOf(
            SymfonyStyle::class,
            static::getPropertyValue($command, 'io')
        );
    }

    /**
     * Test command execution will succeed.
     *
     * @return void
     */
    public function testCommandExecutionWillSucceed(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(FilterCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $output = $this->prophesize(OutputInterface::class);

        $vCard = $this->prophesize(VCard::class);

        $command = $this->createPartialMock(
            FilterCommand::class,
            [
                'getVCardByFile',
            ]
        );

        $command->expects(static::once())
            ->method('getVCardByFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($vCard->reveal());

        static::assertEquals(
            FilterCommand::EXIT_OK,
            static::invokeMethod(
                $command,
                'execute',
                [
                    $input->reveal(),
                    $output->reveal(),
                ]
            )
        );
    }

    /**
     * Test get vcard by file.
     *
     * @return void
     */
    public function testGetVCardByFile(): void
    {
        $command = $this->createPartialMock(FilterCommand::class, []);

        /** @var VCard $result */
        $result = static::invokeMethod(
            $command,
            'getVCardByFile',
            [
                new SplFileInfo(vfsStream::url('root/test.vcf')),
            ]
        );

        static::assertInstanceOf(VCard::class, $result);
        static::assertEquals(Document::VCARD40, $result->getDocumentType());
    }
}
