<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\To;

use FinalGene\PhoneBook\AVM\Contact;
use FinalGene\PhoneBook\AVM\Number;
use FinalGene\PhoneBook\AVM\Person;
use FinalGene\PhoneBook\AVM\Phonebook;
use FinalGene\PhoneBook\AVM\Phonebooks;
use FinalGene\PhoneBook\Console\Helper\SerializerHelper;
use FinalGene\PhoneBook\Exception\ReadFileException;
use FinalGene\PhoneBook\Utils\TestHelperTrait;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\ParseException;
use Sabre\VObject\Property;
use Sabre\VObject\Splitter\VCard as VCardList;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Avm command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\To
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\To\AvmCommand
 */
class AvmCommandTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test create reader command configuration.
     *
     * @return void
     */
    public function testCreateReaderCommandConfiguration(): void
    {
        $command = new AvmCommand();

        static::assertSame(AvmCommand::NAME, $command->getName());
        static::assertSame(AvmCommand::DESCRIPTION, $command->getDescription());


        $definition = $command->getDefinition();
        static::assertTrue($definition->hasArgument(AvmCommand::INPUT_FILE_ARGUMENT_NAME));
    }

    /**
     * Test create reader command initialization.
     *
     * @return void
     */
    public function testCreateReaderCommandInitialization(): void
    {
        $serializerHelper = $this->prophesize(SerializerHelper::class);
        $serializerHelper->getAvmSerializer()
            ->shouldBeCalled()
            ->willReturn($this->createMock(SerializerInterface::class));

        $command = $this->createPartialMock(
            AvmCommand::class,
            [
                'getHelper',
            ]
        );

        $command->expects(static::once())
            ->method('getHelper')
            ->with(SerializerHelper::class)
            ->willReturn($serializerHelper->reveal());

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
        $input->getArgument(AvmCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $output = $this->prophesize(OutputInterface::class);
        $output->write('xml')
            ->shouldBeCalled();

        $vCardList = $this->prophesize(VCardList::class);
        $vCardList->getNext()
            ->shouldBeCalled()
            ->willReturn(
                $this->createMock(VCard::class),
                null
            );

        $phoneBook = $this->prophesize(Phonebook::class);
        $phoneBook->addToContact(Argument::type(Contact::class))
            ->shouldBeCalled();

        $phoneBooks = $this->prophesize(Phonebooks::class);
        $phoneBooks->getPhonebook()
            ->shouldBeCalled()
            ->willReturn($phoneBook->reveal());

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->serialize(Argument::type(Phonebooks::class), 'xml')
            ->shouldBeCalled()
            ->willReturn('xml');

        $command = $this->createPartialMock(
            AvmCommand::class,
            [
                'getVCardListByFile',
                'createPhoneBooks',
                'createContactFromVCard',
            ]
        );

        $command->expects(static::once())
            ->method('getVCardListByFile')
            ->with(static::isInstanceOf(SplFileInfo::class))
            ->willReturn($vCardList->reveal());

        $command->expects(static::once())
            ->method('createPhoneBooks')
            ->willReturn($phoneBooks->reveal());

        $command->expects(static::once())
            ->method('createContactFromVCard')
            ->willReturn($this->createMock(Contact::class));

        static::setPropertyValue($command, 'avmSerializer', $serializer->reveal());

        static::assertEquals(
            AvmCommand::EXIT_OK,
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
        $input->getArgument(AvmCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            AvmCommand::class,
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
            AvmCommand::EXIT_INPUT_ERROR,
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
        $input->getArgument(AvmCommand::INPUT_FILE_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $vCardList = $this->prophesize(VCardList::class);
        $vCardList->getNext()
            ->shouldBeCalled()
            ->willThrow(ParseException::class);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            AvmCommand::class,
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
            AvmCommand::EXIT_VCARD_ERROR,
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
     * Test create phonebooks.
     *
     * @return void
     */
    public function testCreatePhonebooks(): void
    {
        $command = $this->createPartialMock(AvmCommand::class, []);

        /** @var Phonebooks $result */
        $result = static::invokeMethod($command, 'createPhonebooks', []);

        static::assertInstanceOf(Phonebooks::class, $result);
        static::assertInstanceOf(Phonebook::class, $result->getPhonebook());
    }

    /**
     * Test create contact from vcard.
     *
     * @return void
     */
    public function testCreateContactFromVCard(): void
    {
        $command = $this->createPartialMock(
            AvmCommand::class,
            [
                'createPersonByVCard',
                'createNumberByUriElement',
            ]
        );

        $command->expects(static::once())
            ->method('createPersonByVCard')
            ->with(static::isInstanceOf(VCard::class))
            ->willReturn($this->createMock(Person::class));

        $command->expects(static::once())
            ->method('createNumberByUriElement')
            ->with(static::isInstanceOf(Property::class))
            ->willReturn($this->createMock(Number::class));

        /** @var Contact $result */
        $result = static::invokeMethod(
            $command,
            'createContactFromVCard',
            [
                new VCard(['TEL' => '+123456789']),
            ]
        );

        static::assertInstanceOf(Contact::class, $result);
        static::assertInstanceOf(Person::class, $result->getPerson());
        /** @noinspection PhpParamsInspection */
        static::assertContainsOnlyInstancesOf(Number::class, $result->getTelephony());
    }

    /**
     * Test create person by vcard.
     *
     * @return void
     */
    public function testCreatePersonByVCard(): void
    {
        $command = $this->createPartialMock(AvmCommand::class, []);

        /** @var Person $result */
        $result = static::invokeMethod(
            $command,
            'createPersonByVCard',
            [
                new VCard(['FN' => 'test']),
            ]
        );

        static::assertInstanceOf(Person::class, $result);
        static::assertSame('test', $result->getRealName());
    }

    /**
     * Test create number by uri element.
     *
     * @return void
     */
    public function testCreateNumberByUriElement(): void
    {
        $vCard = new VCard();
        $vCard->add(
            'TEL',
            '+123456789',
            [
                'type' => 'work',
            ]
        );

        $command = $this->createPartialMock(AvmCommand::class, []);

        /** @var Number $result */
        $result = static::invokeMethod(
            $command,
            'createNumberByUriElement',
            [
                $vCard->select('TEL')[0],
            ]
        );

        static::assertInstanceOf(Number::class, $result);
        static::assertSame('+123456789', $result->value());
        static::assertSame('work', $result->getType());
    }
}
