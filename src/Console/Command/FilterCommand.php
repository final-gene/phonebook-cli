<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Filter command class.
 *
 * @package FinalGene\PhoneBook\Console\Command
 */
class FilterCommand extends Command
{
    /**
     * Command exit code if everything is ok
     */
    public const EXIT_OK = 0;

    /**
     * Command title
     */
    public const TITLE = 'Filter vCards';

    /**
     * Command name
     */
    public const NAME = 'filter';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Filter vCards by criteria';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * Configures the current command
     *
     * @return void
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription(self::DESCRIPTION);
    }

    /**
     * Initialize.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);
        $this->io = $style->getErrorStyle();
        $this->io->title(self::TITLE);
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return 255;
    }
}
