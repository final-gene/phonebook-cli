<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\VCard;

use FinalGene\PhoneBook\Console\Command\FileTrait;
use FinalGene\PhoneBook\Console\Command\VCardTrait;
use FinalGene\PhoneBook\Exception\EmptyFileException;
use FinalGene\PhoneBook\Exception\ReadFileException;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use Sabre\VObject\Reader;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
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
    use FileTrait;
    use VCardTrait;

    /**
     * Command exit code if everything is ok
     */
    public const EXIT_OK = 0;

    /**
     * Command exit code if the input could not be used
     */
    public const EXIT_INPUT_ERROR = 1;

    /**
     * Command title
     */
    public const TITLE = 'Filter vCards';

    /**
     * Command name
     */
    public const NAME = 'vcard:filter';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Filter vCards by criteria';

    /**
     * Name of input file argument
     */
    public const INPUT_FILE_ARGUMENT_NAME = 'input';

    /**
     * Description of input file argument
     */
    public const INPUT_FILE_ARGUMENT_DESCRIPTION = 'Input file path';

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

        $this->addArgument(
            self::INPUT_FILE_ARGUMENT_NAME,
            InputArgument::OPTIONAL,
            self::INPUT_FILE_ARGUMENT_DESCRIPTION,
            'php://stdin'
        );
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
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $vCard = $this->getVCardByFile(
                new SplFileInfo(
                    $input->getArgument(self::INPUT_FILE_ARGUMENT_NAME)
                )
            );
        } catch (EmptyFileException|ReadFileException $e) {
            $this->io->error($e->getMessage());
            return self::EXIT_INPUT_ERROR;
        }

        $output->write($vCard->serialize());

        return self::EXIT_OK;
    }

    /**
     * Get vCard by file.
     *
     * @param SplFileInfo $file
     *
     * @return VCard
     * @throws EmptyFileException
     * @throws ReadFileException
     */
    protected function getVCardByFile(SplFileInfo $file): VCard
    {
        /** @var VCard $vCard */
        $vCard = Reader::read($this->readFile($file));
        return $vCard->convert(Document::VCARD40);
    }
}
