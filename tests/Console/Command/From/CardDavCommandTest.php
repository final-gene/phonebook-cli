<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\From;

use BarnabyWalters\CardDAV\Client;
use Exception;
use FinalGene\PhoneBook\Utils\TestHelperTrait;
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
 * Card dav command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\From\CardDavCommand
 */
class CardDavCommandTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test create reader command configuration.
     *
     * @return void
     */
    public function testCreateReaderCommandConfiguration(): void
    {
        $command = new CardDavCommand();

        static::assertSame(CardDavCommand::NAME, $command->getName());
        static::assertSame(CardDavCommand::DESCRIPTION, $command->getDescription());
        static::assertTrue($command->getDefinition()->hasOption(CardDavCommand::SERVER_OPTION_NAME));
        static::assertTrue($command->getDefinition()->hasOption(CardDavCommand::USER_OPTION_NAME));
        static::assertTrue($command->getDefinition()->hasOption(CardDavCommand::ASK_PASSWORD_OPTION_NAME));
    }

    /**
     * Test create reader command initialization.
     *
     * @return void
     */
    public function testCreateReaderCommandInitialization(): void
    {
        $command = $this->createPartialMock(CardDavCommand::class, []);

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
        $input->getOption(CardDavCommand::ASK_PASSWORD_OPTION_NAME)
            ->willReturn(false);

        $input->setArgument(
            CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME,
            Argument::type('string')
        )
            ->shouldNotBeCalled();

        $command = $this->createPartialMock(
            CardDavCommand::class,
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
        $input->getOption(CardDavCommand::ASK_PASSWORD_OPTION_NAME)
            ->willReturn(true);

        $input->setArgument(
            CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME,
            Argument::type('string')
        )
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            CardDavCommand::class,
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
     * Test command execution will stop on dav error.
     *
     * @return void
     */
    public function testCommandExecutionWillStopOnDavError(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(CardDavCommand::SERVER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(CardDavCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->hasArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(false);

        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()
            ->shouldBeCalled()
            ->willReturn(OutputInterface::VERBOSITY_DEBUG);

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->error(Argument::type('string'))
            ->shouldBeCalled();

        $io->writeln(Argument::type('string'))
            ->shouldBeCalled();

        $client = $this->prophesize(Client::class);
        $client->enable_debug()
            ->shouldBeCalled();

        $client->get_debug()
            ->shouldBeCalled();

        $command = $this->createPartialMock(
            CardDavCommand::class,
            [
                'createClient',
                'getVCardIdListByClient',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($client->reveal());

        $command->expects(static::once())
            ->method('getVCardIdListByClient')
            ->with(static::isInstanceOf(Client::class))
            ->willThrowException(new Exception());

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CardDavCommand::EXIT_DAV_ERROR,
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
     * Test command execution will show warning on missing vcard.
     *
     * @return void
     */
    public function testCommandExecutionWillShowWarningOnMissingVCard(): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(CardDavCommand::SERVER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(CardDavCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->hasArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
            ->shouldBeCalled()
            ->willReturn(false);

        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()
            ->shouldBeCalled()
            ->willReturn(0);

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

        $command = $this->createPartialMock(
            CardDavCommand::class,
            [
                'createClient',
                'getVCardIdListByClient',
                'getVCardById',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($this->createMock(Client::class));

        $command->expects(static::once())
            ->method('getVCardIdListByClient')
            ->with(static::isInstanceOf(Client::class))
            ->willReturn(
                [
                    '',
                ]
            );

        $command->expects(static::once())
            ->method('getVCardById')
            ->with(
                static::isType('string'),
                static::isInstanceOf(Client::class)
            )
            ->willThrowException(new Exception());

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CardDavCommand::EXIT_OK,
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
     * @param bool $passwordFromEnvironment Simulate password from environment
     *
     * @return void
     * @dataProvider dataForCommandExecutionWillSucceedTest
     */
    public function testCommandExecutionWillSucceed(bool $passwordFromEnvironment): void
    {
        $input = $this->prophesize(InputInterface::class);
        $input->getOption(CardDavCommand::SERVER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        $input->getOption(CardDavCommand::USER_OPTION_NAME)
            ->shouldBeCalled()
            ->willReturn('');

        if ($passwordFromEnvironment) {
            $input->hasArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn(false);

            $input->getArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldNotBeCalled();
        } else {
            $input->hasArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn(true);

            $input->getArgument(CardDavCommand::PASSWORD_INTERACT_ARGUMENT_NAME)
                ->shouldBeCalled()
                ->willReturn('');
        }

        $output = $this->prophesize(OutputInterface::class);
        $output->getVerbosity()
            ->shouldBeCalled()
            ->willReturn(0);

        $output->write('vCardContent')
            ->shouldBeCalled();

        $io = $this->prophesize(SymfonyStyle::class);
        $io->text(Argument::type('string'))
            ->shouldBeCalled();

        $io->progressStart(2)
            ->shouldBeCalled();

        $io->progressAdvance()
            ->shouldBeCalled();

        $io->progressFinish()
            ->shouldBeCalled();

        $vCard = $this->prophesize(VCard::class);
        $vCard->serialize()
            ->shouldBeCalled()
            ->willReturn('vCardContent');

        $command = $this->createPartialMock(
            CardDavCommand::class,
            [
                'createClient',
                'getVCardIdListByClient',
                'getVCardById',
            ]
        );

        $command->expects(static::once())
            ->method('createClient')
            ->with(
                static::isType('string'),
                static::isType('string'),
                static::isType('string')
            )
            ->willReturn($this->createMock(Client::class));

        $command->expects(static::once())
            ->method('getVCardIdListByClient')
            ->with(static::isInstanceOf(Client::class))
            ->willReturn(
                [
                    '',
                    '',
                ]
            );

        $command->expects(static::exactly(2))
            ->method('getVCardById')
            ->with(
                static::isType('string'),
                static::isInstanceOf(Client::class)
            )
            ->willReturn($vCard->reveal());

        static::setPropertyValue($command, 'io', $io->reveal());

        static::assertEquals(
            CardDavCommand::EXIT_OK,
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
        $command = $this->createPartialMock(CardDavCommand::class, []);

        static::assertInstanceOf(
            Client::class,
            static::invokeMethod(
                $command,
                'createClient',
                [
                    'server',
                    'user',
                    'password',
                ]
            )
        );
    }

    /**
     * Test get vcard id list by client.
     *
     * @return void
     */
    public function testGetVCardIdListByClient(): void
    {
        $client = $this->prophesize(Client::class);
        $client->get(false)
            ->shouldBeCalled()
            ->willReturn(
                <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response>
 <element>
  <id>01b6ac2c-4b81-4261-8d1d-75b2f86d70f6</id>
  <etag>gcs00000003</etag>
  <last_modified>Tue, 14 Aug 2018 17:20:16 +0200</last_modified>
 </element>
 <element>
  <id>02e14dfc-664b-4738-beb2-aa40a4ce6c88</id>
  <etag>gcs00000003</etag>
  <last_modified>Tue, 14 Aug 2018 16:26:07 +0200</last_modified>
 </element>
</response>
XML
            );

        $command = $this->createPartialMock(CardDavCommand::class, []);

        /** @var VCard $result */
        $result = static::invokeMethod(
            $command,
            'getVCardIdListByClient',
            [
                $client->reveal(),
            ]
        );

        static::assertNotEmpty($result);
        static::assertCount(2, $result);
        static::assertContains('01b6ac2c-4b81-4261-8d1d-75b2f86d70f6', $result);
        static::assertContains('02e14dfc-664b-4738-beb2-aa40a4ce6c88', $result);
    }

    /**
     * Test get vcard by id.
     *
     * @return void
     */
    public function testGetVCardById(): void
    {
        $client = $this->prophesize(Client::class);
        $client->get_vcard(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn(
                <<<VCARD
BEGIN:VCARD
VERSION:2.1
END:VCARD
VCARD
            );

        $command = $this->createPartialMock(CardDavCommand::class, []);

        /** @var VCard $result */
        $result = static::invokeMethod(
            $command,
            'getVCardById',
            [
                '',
                $client->reveal(),
            ]
        );

        static::assertInstanceOf(VCard::class, $result);
        static::assertEquals(Document::VCARD40, $result->getDocumentType());
    }
}
