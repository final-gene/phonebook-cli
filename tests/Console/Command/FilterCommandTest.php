<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Filter command test class.
 *
 * @package FinalGene\PhoneBook\Console\Command
 *
 * @covers  \FinalGene\PhoneBook\Console\Command\FilterCommand
 */
class FilterCommandTest extends TestCase
{
    use TestHelperTrait;

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
        $output = $this->prophesize(OutputInterface::class);

        $command = $this->createPartialMock(
            FilterCommand::class,
            [
            ]
        );

        static::assertNotEquals(
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
}
