<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console;

use FinalGene\PhoneBook\Console\Command\Read;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

/**
 * Application class.
 *
 * @package FinalGene\PhoneBook\Console
 */
class Application extends SymfonyConsoleApplication
{
    /**
     * The name.
     */
    public const NAME = 'PhoneBook CLI';

    /**
     * The version.
     */
    public const VERSION = '@package_version@';

    /**
     * Application constructor.
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        // Read commands
        $this->addCommands(
            [
                new Read\CardDavCommand(),
                new Read\CsvCommand(),
                new Read\EwsCommand(),
            ]
        );
    }
}
