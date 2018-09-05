<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\From;

use ArrayIterator;
use FinalGene\PhoneBook\Utils\TestHelperTrait;
use League\Csv\Exception;
use League\Csv\Reader;
use libphonenumber\NumberParseException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sabre\VObject\Component\VCard;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Csv command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\From\CsvCommand
 */
class CsvCommandTest extends TestCase
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
                'test.csv' => <<<CSV
firstname;name;phone;fax;email
John;Doe;0123456789;0987654321;john.doe@example.com
CSV
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
     * Test create reader command configuration.
     *
     * @return void
     */
    public function testCreateReaderCommandConfiguration(): void
    {
        $command = new CsvCommand();

        static::assertSame(CsvCommand::NAME, $command->getName());
        static::assertSame(CsvCommand::DESCRIPTION, $command->getDescription());
        static::assertTrue($command->getDefinition()->hasOption(CsvCommand::DELIMITER_OPTION_NAME));
        static::assertTrue($command->getDefinition()->hasArgument(CsvCommand::INPUT_FILE_ARGUMENT_NAME));
    }

    /**
     * Test create reader command initialization.
     *
     * @return void
     */
    public function testCreateReaderCommandInitialization(): void
    {
        $command = $this->createPartialMock(CsvCommand::class, []);

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
     * Test command execution.
     **
     *
     * @return void
     */
    public function testCommandExecutionWillStopOnCsvException(): void
    {
        $reader = $this->prophesize(Reader::class);
        $reader->setHeaderOffset(0)
            ->shouldBeCalled()
            ->willThrow(Exception::class);

        $reader->setDelimiter(Argument::type('string'))
            ->shouldNotBeCalled();

        $reader->count()
            ->shouldNotBeCalled();

        $reader->getRecords()
            ->shouldNotBeCalled();

        $command = $this->createPartialMock(
            CsvCommand::class,
            [
                'createReader',
                'createVCard',
                'createVCardFromRow',
            ]
        );

        $command->expects(static::once())
            ->method('createReader')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($reader->reveal());

        $command->expects(static::never())
            ->method('createVCard');

        $command->expects(static::never())
            ->method('createVCardFromRow');

        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(CsvCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(vfsStream::url('root/test.csv'));

        $input->getOption(CsvCommand::DELIMITER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn(null);

        $output = $this->createPartialMock(
            NullOutput::class,
            [
                'write',
            ]
        );
        $output->expects(static::never())
            ->method('write');

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $io->text(Argument::type('string'))
            ->shouldNotBeCalled();

        $io->progressStart(Argument::type('int'))
            ->shouldNotBeCalled();

        $io->progressAdvance()
            ->shouldNotBeCalled();

        $io->progressFinish()
            ->shouldNotBeCalled();

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CsvCommand::EXIT_CSV_ERROR,
            static::invokeMethod(
                $command,
                'execute',
                [
                    $input->reveal(),
                    $output,
                ]
            )
        );
    }

    /**
     * Test command execution.
     **
     *
     * @return void
     */
    public function testCommandExecutionWillStopOnInvalidDelimiter(): void
    {
        $reader = $this->prophesize(Reader::class);
        $reader->setHeaderOffset(0)
            ->shouldBeCalled();

        $reader->setDelimiter(Argument::type('string'))
            ->shouldBeCalled()
            ->willThrow(Exception::class);

        $reader->count()
            ->shouldNotBeCalled();

        $reader->getRecords()
            ->shouldNotBeCalled();

        $command = $this->createPartialMock(
            CsvCommand::class,
            [
                'createReader',
                'createVCard',
                'createVCardFromRow',
            ]
        );

        $command->expects(static::once())
            ->method('createReader')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($reader->reveal());

        $command->expects(static::never())
            ->method('createVCard');

        $command->expects(static::never())
            ->method('createVCardFromRow');

        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(CsvCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(vfsStream::url('root/test.csv'));

        $input->getOption(CsvCommand::DELIMITER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn(',;');

        $output = $this->createPartialMock(
            NullOutput::class,
            [
                'write',
            ]
        );
        $output->expects(static::never())
            ->method('write');

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $io->text(Argument::type('string'))
            ->shouldNotBeCalled();

        $io->progressStart(Argument::type('int'))
            ->shouldNotBeCalled();

        $io->progressAdvance()
            ->shouldNotBeCalled();

        $io->progressFinish()
            ->shouldNotBeCalled();

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CsvCommand::EXIT_CSV_ERROR,
            static::invokeMethod(
                $command,
                'execute',
                [
                    $input->reveal(),
                    $output,
                ]
            )
        );
    }

    /**
     * Test command execution.
     *
     * @param null|string $delimiter Delimiter sign
     *
     * @return void
     * @dataProvider dataForCommandExecutionTest
     */
    public function testCommandExecutionWillSucceed(?string $delimiter): void
    {
        $reader = $this->prophesize(Reader::class);
        $reader->setHeaderOffset(0)
            ->shouldBeCalled();

        if (null === $delimiter) {
            $reader->setDelimiter(Argument::type('string'))
                ->shouldNotBeCalled();
        } else {
            $reader->setDelimiter($delimiter)
                ->shouldBeCalled();
        }

        $reader->count()
            ->shouldBeCalled()
            ->willReturn(1);

        $reader->getRecords()
            ->shouldBeCalled()
            ->willReturn(
                new ArrayIterator(
                    [
                        [],
                    ]
                )
            );

        $vCard = $this->prophesize(VCard::class);
        $vCard->serialize()
            ->shouldBeCalled()
            ->willReturn('vCardContent');

        $command = $this->createPartialMock(
            CsvCommand::class,
            [
                'createReader',
                'createVCardFromRow',
            ]
        );

        $command->expects(static::once())
            ->method('createReader')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($reader->reveal());

        $command->expects(static::once())
            ->method('createVCardFromRow')
            ->willReturn($vCard->reveal());

        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(CsvCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(vfsStream::url('root/test.csv'));

        $input->getOption(CsvCommand::DELIMITER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn($delimiter);

        $output = $this->createPartialMock(
            NullOutput::class,
            [
                'write',
            ]
        );
        $output->expects(static::once())
            ->method('write')
            ->with('vCardContent');

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->progressStart(1)
            ->shouldBeCalled();

        $io->progressAdvance()
            ->shouldBeCalled();

        $io->progressFinish()
            ->shouldBeCalled();

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CsvCommand::EXIT_OK,
            static::invokeMethod(
                $command,
                'execute',
                [
                    $input->reveal(),
                    $output,
                ]
            )
        );
    }

    /**
     * Data for command execution test.
     *
     * @return array
     */
    public function dataForCommandExecutionTest(): array
    {
        return [
            'without given delimiter' => [
                'delimiter' => null,
            ],
            'with delimiter' => [
                'delimiter' => ';',
            ],
        ];
    }

    /**
     * Test command execution.
     *
     * @return void
     */
    public function testCommandExecutionWillShowWarningOnInvalidNumbers(): void
    {
        $reader = $this->prophesize(Reader::class);
        $reader->setHeaderOffset(0)
            ->shouldBeCalled();

        $reader->setDelimiter(Argument::type('string'))
            ->shouldNotBeCalled();

        $reader->count()
            ->shouldBeCalled()
            ->willReturn(1);

        $reader->getRecords()
            ->shouldBeCalled()
            ->willReturn(
                new ArrayIterator(
                    [
                        [],
                    ]
                )
            );

        $command = $this->createPartialMock(
            CsvCommand::class,
            [
                'createReader',
                'createVCardFromRow',
            ]
        );

        $command->expects(static::once())
            ->method('createReader')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($reader->reveal());

        $command->expects(static::once())
            ->method('createVCardFromRow')
            ->willThrowException($this->createMock(NumberParseException::class));

        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(CsvCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(vfsStream::url('root/test.csv'));

        $input->getOption(CsvCommand::DELIMITER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn(null);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->warning(Argument::type('string'))
            ->shouldBeCalled();

        $io->progressStart(1)
            ->shouldBeCalled();

        $io->progressAdvance()
            ->shouldNotBeCalled();

        $io->progressFinish()
            ->shouldBeCalled();

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CsvCommand::EXIT_OK,
            static::invokeMethod(
                $command,
                'execute',
                [
                    $input->reveal(),
                    $this->createMock(OutputInterface::class),
                ]
            )
        );
    }

    /**
     * Test create reader.
     *
     * @return void
     */
    public function testCreateReader(): void
    {
        $command = $this->createPartialMock(CsvCommand::class, []);

        static::assertInstanceOf(
            Reader::class,
            static::invokeMethod(
                $command,
                'createReader',
                [
                    new SplFileInfo(vfsStream::url('root/test.csv')),
                ]
            )
        );
    }

    /**
     * Test create vcard from row.
     *
     * @return void
     */
    public function testCreateVCardFromRow(): void
    {
        $command = $this->createPartialMock(
            CsvCommand::class,
            [
                'normalizePhoneNumber',
                'normalizePhoneNumberType',
                'normalizeEmailType',
            ]
        );

        $command->expects(static::exactly(2))
            ->method('normalizePhoneNumber')
            ->with(static::isType('string'))
            ->willReturn('01234');

        $command->expects(static::exactly(2))
            ->method('normalizePhoneNumberType')
            ->with(static::isType('string'))
            ->willReturnOnConsecutiveCalls('home', 'fax');

        $command->expects(static::once())
            ->method('normalizeEmailType')
            ->with(static::isType('string'))
            ->willReturn('work');

        /** @var VCard $vCard */
        $vCard = static::invokeMethod(
            $command,
            'createVCardFromRow',
            [
                [
                    'firstname' => 'John',
                    'name' => 'Doe',
                    'phone' => '0123456789',
                    'fax' => '0987654321',
                    'email' => 'john.doe@example.com',
                ],
            ]
        );

        static::assertInstanceOf(VCard::class, $vCard);
        static::assertEquals('John Doe', $vCard->__get('FN')->getValue());
        static::assertEquals('01234', $vCard->getByType('TEL', 'home')->getValue());
        static::assertEquals('01234', $vCard->getByType('TEL', 'fax')->getValue());
        static::assertEquals('john.doe@example.com', $vCard->getByType('EMAIL', 'work')->getValue());
    }
}
