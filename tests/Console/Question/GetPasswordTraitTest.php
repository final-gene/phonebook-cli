<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Question;

use FinalGene\PhoneBook\Utils\TestHelperTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Get password trait test class.
 *
 * @package FinalGene\PhoneBook\Console\Question
 *
 * @covers \FinalGene\PhoneBook\Console\Question\GetPasswordTrait
 */
class GetPasswordTraitTest extends TestCase
{
    use TestHelperTrait;

    /**
     * Test get password from user should return password
     *
     * @return void
     */
    public function testGetPasswordFromUserShouldReturnPassword(): void
    {
        $io = $this->prophesize(SymfonyStyle::class);
        $io->askHidden(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn('password');

        $trait = $this->getMockForTrait(GetPasswordTrait::class);

        $result = static::invokeMethod(
            $trait,
            'getPasswordFromUser',
            [
                $io->reveal(),
            ]
        );

        static::assertSame('password', $result);
    }
}
