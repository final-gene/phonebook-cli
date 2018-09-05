<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console;

use FinalGene\PhoneBook\Console\Command\VCard;
use FinalGene\PhoneBook\Console\Command\From;
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

        // From commands
        $this->addCommands(
            [
                new From\CardDavCommand(),
                new From\CsvCommand(),
                new From\EwsCommand(),
            ]
        );

        // vCard command
        $this->addCommands(
            [
                new VCard\FilterCommand(),
            ]
        );
    }
}
