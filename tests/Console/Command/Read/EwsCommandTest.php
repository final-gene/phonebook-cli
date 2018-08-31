<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use jamesiarmes\PhpEws\ArrayType\ArrayOfRealItemsType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfResponseMessagesType;
use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Response\FindItemResponseMessageType;
use jamesiarmes\PhpEws\Response\FindItemResponseType;
use jamesiarmes\PhpEws\Type\ContactItemType;
use jamesiarmes\PhpEws\Type\FindItemParentType;
use jamesiarmes\PhpEws\Type\PhoneNumberDictionaryEntryType;
use jamesiarmes\PhpEws\Type\PhoneNumberDictionaryType;
use libphonenumber\NumberParseException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Exchange webservice command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\Read\EwsCommand
 */
class EwsCommandTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test create reader command configuration.
     *
     * @return void
     */
    public function testCreateReaderCommandConfiguration(): void
    {
        $command = new EwsCommand();

        static::assertSame(EwsCommand::NAME, $command->getName());
        static::assertSame(EwsCommand::DESCRIPTION, $command->getDescription());
        static::assertTrue($command->getDefinition()->hasOption(EwsCommand::HOST_OPTION_NAME));
        static::assertTrue($command->getDefinition()->hasOption(EwsCommand::USER_OPTION_NAME));
        static::assertTrue($command->getDefinition()->hasOption(EwsCommand::ASK_PASSWORD_OPTION_NAME));
    }

    /**
     * Test create reader command initialization.
     *
     * @return void
     */
    public function testCreateReaderCommandInitialization(): void
    {
        $command = $this->createPartialMock(EwsCommand::class, []);

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
     * Test no interaction on missing ask option.
     *
     * @return void
     */
    public function testNoInteractionOnMissingAskOption(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(EwsCommand::ASK_PASSWORD_OPTION_NAME)
            ->willReturn(false);

        $input->setArgument(
            EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME,
            Argument::type('string')
        )
            ->shouldNotBeCalled();

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'getDefinition',
                'getPasswordFromUser',
            ]
        );

        $command->expects(static::never())
            ->method('getDefinition');

        $command->expects(static::never())
            ->method('getPasswordFromUser')
            ->with(static::isInstanceOf(SymfonyStyle::class));

        static::invokeMethod(
            $command,
            'interact',
            [
                $input->reveal(),
                new NullOutput(),
            ]
        );
    }

    /**
     * Test no interaction on missing ask option.
     *
     * @return void
     */
    public function testInteraction(): void
    {
        $inputDefinition = $this->prophesize(InputDefinition::class);
        $inputDefinition->addArguments(Argument::type('array'))
            ->shouldBeCalled();

        $input = $this->prophesize(InputInterface::class);
        $input->getOption(EwsCommand::ASK_PASSWORD_OPTION_NAME)
            ->willReturn(true);

        $input->setArgument(
            EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME,
            Argument::type('string')
        )
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'getDefinition',
                'getPasswordFromUser',
            ]
        );

        $command->expects(static::once())
            ->method('getDefinition')
            ->willReturn($inputDefinition->reveal());

        $command->expects(static::once())
            ->method('getPasswordFromUser')
            ->with(static::isInstanceOf(SymfonyStyle::class))
            ->willReturn('');

        static::setPropertyValue(
            $command,
            'io',
            $this->createMock(SymfonyStyle::class)
        );

        static::invokeMethod(
            $command,
            'interact',
            [
                $input->reveal(),
                new NullOutput(),
            ]
        );
    }

    /**
     * Test command execution will stop on ews error.
     *
     * @return void
     */
    public function testCommandExecutionWillStopOnEwsError(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(EwsCommand::HOST_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::VERSION_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->hasArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(false);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $responseMessage = new FindItemResponseMessageType();
        $responseMessage->ResponseClass = '';

        $response = new FindItemResponseType();
        $response->ResponseMessages = new ArrayOfResponseMessagesType();
        $response->ResponseMessages->FindItemResponseMessage = [
            $responseMessage,
        ];

        $client = $this->prophesize(Client::class);
        $client->FindItem(Argument::type(FindItemType::class))
            ->shouldBeCalled()
            ->willReturn($response);

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createClient',
                'createRequest',
                'createVCard',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($client->reveal());

        $command->expects(static::once())
            ->method('createRequest')
            ->willReturn($this->createMock(FindItemType::class));

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($this->createMock(VCard::class));

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            EwsCommand::EXIT_EWS_ERROR,
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
     * Test command execution will show warning on invalid phone number.
     *
     * @return void
     */
    public function testCommandExecutionWillShowWarningOnInvalidPhoneNumber(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(EwsCommand::HOST_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::VERSION_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->hasArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(false);

        $output = $this->prophesize(OutputInterface::class);
        $output->write('vCardContent')
            ->shouldBeCalled();

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

        $responseMessage = new FindItemResponseMessageType();
        $responseMessage->ResponseClass = ResponseClassType::SUCCESS;
        $responseMessage->RootFolder = new FindItemParentType();
        $responseMessage->RootFolder->TotalItemsInView = 1;
        $responseMessage->RootFolder->Items = new ArrayOfRealItemsType();
        $responseMessage->RootFolder->Items->Contact = [
            new ContactItemType(),
        ];

        $response = new FindItemResponseType();
        $response->ResponseMessages = new ArrayOfResponseMessagesType();
        $response->ResponseMessages->FindItemResponseMessage = [
            $responseMessage,
        ];

        $client = $this->prophesize(Client::class);
        $client->FindItem(Argument::type(FindItemType::class))
            ->shouldBeCalled()
            ->willReturn($response);

        $vCard = $this->prophesize(VCard::class);
        $vCard->add(Argument::type(VCard::class))
            ->shouldNotBeCalled();

        $vCard->serialize()
            ->shouldBeCalled()
            ->willReturn('vCardContent');

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createClient',
                'createRequest',
                'createVCard',
                'createVCardFromEwsItem',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($client->reveal());

        $command->expects(static::once())
            ->method('createRequest')
            ->willReturn($this->createMock(FindItemType::class));

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($vCard->reveal());

        $command->expects(static::once())
            ->method('createVCardFromEwsItem')
            ->with(static::isInstanceOf(ContactItemType::class))
            ->willThrowException(new NumberParseException(0, ''));

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            EwsCommand::EXIT_OK,
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
     * Test command execution will succeed.
     *
     * @param bool $passwordFromEnvironment
     *
     * @return void
     * @dataProvider dataForCommandExecutionWillSucceedTest
     */
    public function testCommandExecutionWillSucceed(bool $passwordFromEnvironment): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(EwsCommand::HOST_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(EwsCommand::VERSION_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        if ($passwordFromEnvironment) {
            $input->hasArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn(false);

            $input->getArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldNotBeCalled();
        } else {
            $input->hasArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn(true);

            $input->getArgument(EwsCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn('');
        }

        $output = $this->prophesize(OutputInterface::class);
        $output->write('vCardContent')
            ->shouldBeCalled();

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->progressStart(1)
            ->shouldBeCalled();

        $io->progressAdvance()
            ->shouldBeCalled();

        $io->progressFinish()
            ->shouldBeCalled();

        $responseMessage = new FindItemResponseMessageType();
        $responseMessage->ResponseClass = ResponseClassType::SUCCESS;
        $responseMessage->RootFolder = new FindItemParentType();
        $responseMessage->RootFolder->TotalItemsInView = 1;
        $responseMessage->RootFolder->Items = new ArrayOfRealItemsType();
        $responseMessage->RootFolder->Items->Contact = [
            new ContactItemType(),
        ];

        $response = new FindItemResponseType();
        $response->ResponseMessages = new ArrayOfResponseMessagesType();
        $response->ResponseMessages->FindItemResponseMessage = [
            $responseMessage,
        ];

        $client = $this->prophesize(Client::class);
        $client->FindItem(Argument::type(FindItemType::class))
            ->shouldBeCalled()
            ->willReturn($response);

        $vCard = $this->prophesize(VCard::class);
        $vCard->add(Argument::type(VCard::class))
            ->shouldBeCalled();

        $vCard->serialize()
            ->shouldBeCalled()
            ->willReturn('vCardContent');

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createClient',
                'createRequest',
                'createVCard',
                'createVCardFromEwsItem',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($client->reveal());

        $command->expects(static::once())
            ->method('createRequest')
            ->willReturn($this->createMock(FindItemType::class));

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($vCard->reveal());

        $command->expects(static::once())
            ->method('createVCardFromEwsItem')
            ->with(static::isInstanceOf(ContactItemType::class))
            ->willReturn($this->createMock(VCard::class));

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            EwsCommand::EXIT_OK,
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
     * Data for command execution will succeed test.
     *
     * @return array
     */
    public function dataForCommandExecutionWillSucceedTest(): array
    {
        return [
            'use password from environment' => [
                'passwordFromEnvironment' => true,
            ],
            'use password given by user' => [
                'passwordFromEnvironment' => false,
            ],
        ];
    }

    /**
     * Test create client.
     *
     * @return void
     */
    public function testCreateClient(): void
    {
        $command = $this->createPartialMock(EwsCommand::class, []);

        static::assertInstanceOf(
            Client::class,
            static::invokeMethod(
                $command,
                'createClient',
                [
                    'host',
                    'user',
                    'password',
                    'version',
                ]
            )
        );
    }

    /**
     * Test create request.
     *
     * @return void
     */
    public function testCreateRequest(): void
    {
        $command = $this->createPartialMock(EwsCommand::class, []);

        static::assertInstanceOf(
            FindItemType::class,
            static::invokeMethod($command, 'createRequest')
        );
    }

    /**
     * Test create vcard from ews item with no phone number.
     *
     * @return void
     */
    public function testCreateVCardFromEwsItemWithNoPhoneNumber(): void
    {
        $item = new ContactItemType();
        $item->DisplayName = '';
        $item->Surname = '';
        $item->GivenName = '';
        $item->Notes = '';

        $vCard = $this->prophesize(VCard::class);
        $vCard->convert(Document::VCARD40)
            ->shouldBeCalled()
            ->willReturn($this->createMock(VCard::class));

        $vCard->add('FN', Argument::type('string'))
            ->shouldBeCalled();

        $vCard->add('N', Argument::type('array'))
            ->shouldBeCalled();

        $vCard->add('NOTE', Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createVCard',
                'addPhoneNumberToVCard',
            ]
        );

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($vCard->reveal());

        $command->expects(static::never())
            ->method('addPhoneNumberToVCard')
            ->with(
                static::isInstanceOf(VCard::class),
                static::isInstanceOf(ContactItemType::class)
            );

        static::invokeMethod(
            $command,
            'createVCardFromEwsItem',
            [
                $item,
            ]
        );
    }

    /**
     * Test create vcard from ews item with single phone number.
     *
     * @return void
     */
    public function testCreateVCardFromEwsItemWithSinglePhoneNumber(): void
    {
        $item = new ContactItemType();
        $item->DisplayName = '';
        $item->Surname = '';
        $item->GivenName = '';
        $item->Notes = '';
        $item->PhoneNumbers = new PhoneNumberDictionaryType();
        $item->PhoneNumbers->Entry = new PhoneNumberDictionaryEntryType();

        $vCard = $this->prophesize(VCard::class);
        $vCard->convert(Document::VCARD40)
            ->shouldBeCalled()
            ->willReturn($this->createMock(VCard::class));

        $vCard->add('FN', Argument::type('string'))
            ->shouldBeCalled();

        $vCard->add('N', Argument::type('array'))
            ->shouldBeCalled();

        $vCard->add('NOTE', Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createVCard',
                'addPhoneNumberToVCard',
            ]
        );

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($vCard->reveal());

        $command->expects(static::once())
            ->method('addPhoneNumberToVCard')
            ->with(
                static::isInstanceOf(VCard::class),
                static::isInstanceOf(PhoneNumberDictionaryEntryType::class)
            );

        static::invokeMethod(
            $command,
            'createVCardFromEwsItem',
            [
                $item,
            ]
        );
    }

    /**
     * Test create vcard from ews item with multiple phone numbers.
     *
     * @return void
     */
    public function testCreateVCardFromEwsItemWithMultiplePhoneNumbers(): void
    {
        $item = new ContactItemType();
        $item->DisplayName = '';
        $item->Surname = '';
        $item->GivenName = '';
        $item->Notes = '';
        $item->PhoneNumbers = new PhoneNumberDictionaryType();
        $item->PhoneNumbers->Entry = [
            new PhoneNumberDictionaryEntryType(),
        ];

        $vCard = $this->prophesize(VCard::class);
        $vCard->convert(Document::VCARD40)
            ->shouldBeCalled()
            ->willReturn($this->createMock(VCard::class));

        $vCard->add('FN', Argument::type('string'))
            ->shouldBeCalled();

        $vCard->add('N', Argument::type('array'))
            ->shouldBeCalled();

        $vCard->add('NOTE', Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'createVCard',
                'addPhoneNumberToVCard',
            ]
        );

        $command->expects(static::once())
            ->method('createVCard')
            ->willReturn($vCard->reveal());

        $command->expects(static::once())
            ->method('addPhoneNumberToVCard')
            ->with(
                static::isInstanceOf(VCard::class),
                static::isInstanceOf(PhoneNumberDictionaryEntryType::class)
            );

        static::invokeMethod(
            $command,
            'createVCardFromEwsItem',
            [
                $item,
            ]
        );
    }

    /**
     * Test add phone number to vcard will return on empty entry.
     *
     * @return void
     */
    public function testAddPhoneNumberToVCardWillReturnOnEmptyEntry(): void
    {
        $vCard = $this->prophesize(VCard::class);
        $vCard->add(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array')
        )
            ->shouldNotBeCalled();

        $command = $this->createPartialMock(EwsCommand::class, []);

        static::invokeMethod(
            $command,
            'addPhoneNumberToVCard',
            [
                $vCard->reveal(),
                $this->createMock(PhoneNumberDictionaryEntryType::class),
            ]
        );
    }

    /**
     * Test add phone number to vcard will add normalized number.
     *
     * @return void
     */
    public function testAddPhoneNumberToVCardWillAddNormalizedNumber(): void
    {
        $vCard = $this->prophesize(VCard::class);
        $vCard->add(
            'TEL',
            Argument::type('string'),
            Argument::type('array')
        )
            ->shouldBeCalled();

        $item = new PhoneNumberDictionaryEntryType();
        $item->_ = '0123456789';
        $item->Key = 'key';

        $command = $this->createPartialMock(
            EwsCommand::class,
            [
                'normalizePhoneNumber',
                'normalizePhoneNumberType',
            ]
        );

        $command->expects(static::once())
            ->method('normalizePhoneNumber')
            ->with(static::isType('string'))
            ->willReturn('');

        $command->expects(static::once())
            ->method('normalizePhoneNumberType')
            ->with(static::isType('string'))
            ->willReturn('');

        static::invokeMethod(
            $command,
            'addPhoneNumberToVCard',
            [
                $vCard->reveal(),
                $item,
            ]
        );
    }
}
