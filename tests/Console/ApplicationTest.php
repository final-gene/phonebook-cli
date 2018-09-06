<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console;

use FinalGene\PhoneBook\Console\Helper\SerializerHelper;
use PHPUnit\Framework\TestCase;

/**
 * Application test class.
 *
 * @package FinalGene\PhoneBook\Console
 *
 * @covers  \FinalGene\PhoneBook\Console\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * Test console application.
     *
     * @return void
     */
    public function testConsoleApplication(): void
    {
        $consoleApplication = new Application();

        static::assertSame(Application::NAME, $consoleApplication->getName());
        static::assertSame(Application::VERSION, $consoleApplication->getVersion());

        static::assertTrue($consoleApplication->getHelperSet()->has(SerializerHelper::class));
    }
}
