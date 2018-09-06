<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\VCard;

use FinalGene\PhoneBook\Exception\ReadFileException;
use FinalGene\PhoneBook\Utils\TestHelperTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\ParseException;
use Sabre\VObject\Splitter\VCard as VCardList;
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
     * @var array
     */
    private const FILTER_LIST = [
        FilterCommand::NOTE_FILTER_OPTION_NAME => [
            'some note',
        ],
        FilterCommand::HAS_TELEPHONE_FILTER_OPTION_NAME => true,
    ];

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

        $definition = $command->getDefinition();
        static::assertTrue($definition->hasArgument(FilterCommand::INPUT_FILE_ARGUMENT_NAME));
        static::assertTrue($definition->hasOption(FilterCommand::NOTE_FILTER_OPTION_NAME));
        static::assertTrue($definition->hasOption(FilterCommand::HAS_TELEPHONE_FILTER_OPTION_NAME));
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

        foreach (array_keys(self::FILTER_LIST) as $filterOptionName) {
            $input->getOption($filterOptionName)
                ->shouldBeCalled()
                ->willReturn(null);
        }

        $output = $this->prophesize(OutputInterface::class);
        $output->write('vCardContent')
            ->shouldBeCalled();

        $vCard = $this->prophesize(VCard::class);
        $vCard->serialize()
            ->shouldBeCalledTimes(1)
            ->willReturn('vCardContent');

        $vCardList = $this->prophesize(VCardList::class);
        $vCardList->getNext()
            ->shouldBeCalled()
            ->willReturn(
                $vCard->reveal(),
                $vCard->reveal(),
                null
            );

        $command = $this->createPartialMock(
            FilterCommand::class,
            [
                'getVCardListByFile',
                'filter',
            ]
        );

        $command->expects(static::once())
            ->method('getVCardListByFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($vCardList->reveal());

        $command->expects(static::exactly(2))
            ->method('filter')
            ->with(
                static::isInstanceOf(VCard::class),
                static::isType('array')
            )
            ->willReturn(
                true,
                false
            );

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
     * Test command execution will fail on read error.
     *
     * @return void
     */
    public function testCommandExecutionWillFailOnInputError(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(FilterCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(Argument::any())
            ->shouldBeCalled()
            ->willReturn(null);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            FilterCommand::class,
            [
                'getVCardListByFile',
            ]
        );

        $command->expects(static::once())
            ->method('getVCardListByFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willThrowException(new ReadFileException(''));

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            FilterCommand::EXIT_INPUT_ERROR,
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
     * Test command execution will fail on parse error.
     *
     * @return void
     */
    public function testCommandExecutionWillFailOnVCardError(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getArgument(FilterCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(Argument::any())
            ->shouldBeCalled()
            ->willReturn(null);

        $vCardList = $this->prophesize(VCardList::class);
        $vCardList->getNext()
            ->shouldBeCalled()
            ->willThrow(ParseException::class);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            FilterCommand::class,
            [
                'getVCardListByFile',
            ]
        );

        $command->expects(static::once())
            ->method('getVCardListByFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($vCardList->reveal());

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            FilterCommand::EXIT_VCARD_ERROR,
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
     * Test filter will succeed if all filters match.
     *
     * @param bool $match    Flag to simulate mathing filters
     * @param bool $expected Expected result
     *
     * @return void
     * @dataProvider dataForFilterTest
     */
    public function testFilter(bool $match, bool $expected): void
    {
        $filterByMethodList = [];
        foreach (array_keys(self::FILTER_LIST) as $filterOptionName) {
            $filterByMethodList[$filterOptionName] = FilterCommand::getFilterFunctionNameByFilterOptionName(
                $filterOptionName
            );
        }

        $command = $this->createPartialMock(
            FilterCommand::class,
            $filterByMethodList
        );

        foreach ($filterByMethodList as $filterOptionName => $functionName) {
            $command->expects(static::once())
                ->method($functionName)
                ->with(
                    static::isInstanceOf(VCard::class),
                    self::FILTER_LIST[$filterOptionName]
                )
                ->willReturn($match);
        }

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $command,
                'filter',
                [
                    $this->createMock(VCard::class),
                    self::FILTER_LIST,
                ]
            )
        );
    }

    /**
     * Data for filter test.
     *
     * @return array
     */
    public function dataForFilterTest(): array
    {
        return [
            'filter will succeed if all filters match' => [
                'match' => true,
                'expected' => true,
            ],
            'filter will fail if not all filters match' => [
                'match' => false,
                'expected' => false,
            ],
        ];
    }

    /**
     * Test filter by note.
     *
     * @param VCard $vCard           vCard mock
     * @param array $filterValueList List of filter values
     * @param bool  $expected        Expected result
     *
     * @return void
     * @dataProvider dataForFilterByNoteTest
     */
    public function testFilterByNote(VCard $vCard, array $filterValueList, bool $expected): void
    {
        $command = $this->createPartialMock(FilterCommand::class, []);

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $command,
                'filterByNote',
                [
                    $vCard,
                    $filterValueList,
                ]
            )
        );
    }

    /**
     * Data for filter by note test.
     *
     * @return array
     */
    public function dataForFilterByNoteTest(): array
    {
        $vCardWithNote = new VCard(
            [
                'NOTE' => 'some note',
            ]
        );

        return [
            'succeed if no values are given' => [
                'vCard' => $this->createMock(VCard::class),
                'filterValueList' => (array)null,
                'expected' => true,
            ],
            'succeed if list of empty values is given' => [
                'vCard' => $this->createMock(VCard::class),
                'filterValueList' => [
                    '',
                    null,
                ],
                'expected' => true,
            ],
            'fail if values are given and vCard has no note' => [
                'vCard' => $this->createMock(VCard::class),
                'filterValueList' => [
                    'foo',
                ],
                'expected' => false,
            ],
            'fail if values are given but will not match on vCard note' => [
                'vCard' => $vCardWithNote,
                'filterValueList' => [
                    'foo',
                ],
                'expected' => false,
            ],
            'succeed if one value will match on vCard note' => [
                'vCard' => $vCardWithNote,
                'filterValueList' => [
                    'foo',
                    'some',
                ],
                'expected' => true,
            ],
        ];
    }

    /**
     * Test filter by note.
     *
     * @param VCard     $vCard       vCard mock
     * @param bool|null $filterValue Filter value flag
     * @param bool      $expected    Expected result
     *
     * @return void
     * @dataProvider dataForFilterByHasTelephoneTest
     */
    public function testFilterByHasTelephone(VCard $vCard, ?bool $filterValue, bool $expected): void
    {
        $command = $this->createPartialMock(FilterCommand::class, []);

        static::assertEquals(
            $expected,
            static::invokeMethod(
                $command,
                'filterByHasTelephone',
                [
                    $vCard,
                    $filterValue,
                ]
            )
        );
    }

    /**
     * Data for filter by note test.
     *
     * @return array
     */
    public function dataForFilterByHasTelephoneTest(): array
    {
        return [
            'succeed if no value is given' => [
                'vCard' => $this->createMock(VCard::class),
                'filterValue' => null,
                'expected' => true,
            ],
            'succeed if vCard has telephone information' => [
                'vCard' => new VCard(['TEL' => '0123456789']),
                'filterValue' => true,
                'expected' => true,
            ],
            'fail if vCard has no telephone information' => [
                'vCard' => $this->createMock(VCard::class),
                'filterValue' => true,
                'expected' => false,
            ],
        ];
    }

    /**
     * Test get filter name.
     *
     * @param string $optionName Filter option name
     * @param string $expected   Expected function name
     *
     * @return void
     * @dataProvider dataForGetFilterFunctionNameByFilterOptionNameTest
     */
    public function testGetFilterFunctionNameByFilterOptionName(string $optionName, string $expected): void
    {
        static::assertEquals(
            $expected,
            FilterCommand::getFilterFunctionNameByFilterOptionName($optionName)
        );
    }

    /**
     * Data for get filter function name by filter option name test.
     *
     * @return array
     */
    public function dataForGetFilterFunctionNameByFilterOptionNameTest(): array
    {
        return [
            'note filter' => [
                'optionName' => FilterCommand::NOTE_FILTER_OPTION_NAME,
                'expected' => 'filterByNote',
            ],
            'telephone filter' => [
                'optionName' => FilterCommand::HAS_TELEPHONE_FILTER_OPTION_NAME,
                'expected' => 'filterByHasTelephone',
            ],
        ];
    }
}
