<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\VCard;

use FinalGene\PhoneBook\Console\Command\VCardTrait;
use FinalGene\PhoneBook\Exception\ReadFileException;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\ParseException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Filter command class.
 *
 * @package FinalGene\PhoneBook\Console\Command
 */
class FilterCommand extends Command
{
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
     * Command exit code if an invalid vCard is given
     */
    public const EXIT_VCARD_ERROR = 2;

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
     * Name of note filter option
     */
    public const NOTE_FILTER_OPTION_NAME = 'filter-note';

    /**
     * Description of note filter option
     */
    public const NOTE_FILTER_OPTION_DESCRIPTION = 'Filter cards with matching note content';

    /**
     * Name of has telephone filter option
     */
    public const HAS_TELEPHONE_FILTER_OPTION_NAME = 'filter-has-telephone';

    /**
     * Description of has telephone filter option
     */
    public const HAS_TELEPHONE_FILTER_OPTION_DESCRIPTION = 'Filter cards with telephone information';

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

        $this->addOption(
            self::NOTE_FILTER_OPTION_NAME,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            self::NOTE_FILTER_OPTION_DESCRIPTION
        );

        $this->addOption(
            self::HAS_TELEPHONE_FILTER_OPTION_NAME,
            null,
            InputOption::VALUE_NONE,
            self::HAS_TELEPHONE_FILTER_OPTION_DESCRIPTION
        );
    }

    /**
     * Initialize.
     *
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
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
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
     *
     * @return int|null
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $filter = [
            self::NOTE_FILTER_OPTION_NAME => (array)$input->getOption(self::NOTE_FILTER_OPTION_NAME),
            self::HAS_TELEPHONE_FILTER_OPTION_NAME => $input->getOption(self::HAS_TELEPHONE_FILTER_OPTION_NAME),
        ];

        try {
            $vCardList = $this->getVCardListByFile(
                new SplFileInfo(
                    $input->getArgument(self::INPUT_FILE_ARGUMENT_NAME)
                )
            );
        } catch (ReadFileException $e) {
            $this->io->error($e->getMessage());

            return self::EXIT_INPUT_ERROR;
        }

        /** @var VCard $vCard */
        try {
            while (null !== ($vCard = $vCardList->getNext())) {
                if ($this->filter($vCard, $filter)) {
                    $output->write($vCard->serialize());
                }
            }
        } catch (ParseException $e) {
            $this->io->error($e->getMessage());

            return self::EXIT_VCARD_ERROR;
        }

        return self::EXIT_OK;
    }

    /**
     * Filter.
     *
     * @param VCard $vCard  vCard to filter
     * @param array $filter Filter settings
     *
     * @return bool
     */
    protected function filter(VCard $vCard, array $filter): bool
    {
        $success = true;
        foreach ($filter as $filterOptionName => $filterValues) {
            $result = $this->{self::getFilterFunctionNameByFilterOptionName($filterOptionName)}($vCard, $filterValues);
            $success = $success && $result;
        }

        return $success;
    }

    /**
     * Filter note.
     *
     * @param VCard $vCard           vCard to filter
     * @param array $filterValueList Filter value list
     *
     * @return bool
     */
    protected function filterByNote(VCard $vCard, array $filterValueList): bool
    {
        $filterValueList = array_filter($filterValueList);
        if (empty($filterValueList)) {
            return true;
        }

        if (isset($vCard->note)) {
            foreach ($filterValueList as $filterValue) {
                if (false !== strpos((string)$vCard->note, $filterValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Filter by has telephone.
     *
     * @param VCard     $vCard             vCard to filter
     * @param bool|null $mustHaveTelephone Test param
     *
     * @return bool
     */
    protected function filterByHasTelephone(VCard $vCard, ?bool $mustHaveTelephone): bool
    {
        if (null === $mustHaveTelephone) {
            return true;
        }

        return !empty($vCard->select('TEL'));
    }

    /**
     * Get filter name.
     *
     * @param string $filterOptionName Filter option name
     *
     * @return string
     */
    public static function getFilterFunctionNameByFilterOptionName(string $filterOptionName): string
    {
        return 'filterBy' . str_replace(
            '-',
            '',
            ucwords(
                str_replace(
                    'filter-',
                    '',
                    $filterOptionName
                ),
                '-'
            )
        );
    }
}
