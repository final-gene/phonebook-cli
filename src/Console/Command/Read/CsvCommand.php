<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use FinalGene\PhoneBook\Console\Command\FileTrait;
use FinalGene\PhoneBook\Console\Command\VCardTrait;
use FinalGene\PhoneBook\Exception\ReadFileException;
use League\Csv\Exception;
use League\Csv\Reader;
use libphonenumber\NumberParseException;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use Sabre\VObject\Property\FlatText;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Csv command class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 */
class CsvCommand extends Command
{
    use EmailTrait;
    use FileTrait;
    use PhoneNumberTrait;
    use VCardTrait;

    /**
     * Command exit code if everything is ok
     */
    public const EXIT_OK = 0;

    /**
     * Command exit code if an error occurred during CSV processing
     */
    public const EXIT_CSV_ERROR = 1;

    /**
     * Command title
     */
    public const TITLE = 'Read from CSV';

    /**
     * Command name
     */
    public const NAME = 'read:csv';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Get contacts from a CSV file';

    /**
     * Name of input file argument
     */
    public const INPUT_FILE_ARGUMENT_NAME = 'input';

    /**
     * Description of input file argument
     */
    public const INPUT_FILE_ARGUMENT_DESCRIPTION = 'Input file path';

    /**
     * Name of delimiter option
     */
    public const DELIMITER_OPTION_NAME = 'delimiter';

    /**
     * Description of delimiter option
     */
    public const DELIMITER_OPTION_DESCRIPTION = 'Delimiter sign';

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

        $this->addOption(
            self::DELIMITER_OPTION_NAME,
            's',
            InputOption::VALUE_REQUIRED,
            self::DELIMITER_OPTION_DESCRIPTION
        );
        $this->addArgument(
            self::INPUT_FILE_ARGUMENT_NAME,
            InputOption::VALUE_REQUIRED,
            self::INPUT_FILE_ARGUMENT_DESCRIPTION,
            'php://stdin'
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
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $file = new SplFileInfo($input->getArgument(self::INPUT_FILE_ARGUMENT_NAME));
        $delimiter = $input->getOption(self::DELIMITER_OPTION_NAME);

        try {
            $reader = $this->createReader($file);
            $reader->setHeaderOffset(0);
            if (null !== $delimiter) {
                $reader->setDelimiter($delimiter);
            }
            $this->io->text('read file...');
        } catch (Exception|ReadFileException $e) {
            $this->io->error($e->getMessage());
            return self::EXIT_CSV_ERROR;
        }

        $this->io->progressStart($reader->count());
        foreach ($reader->getRecords() as $row) {
            try {
                $output->write(
                    $this->createVCardFromRow($row)->serialize()
                );
                $this->io->progressAdvance();
            } catch (NumberParseException $e) {
                $this->io->warning($e->getMessage());
            }
        }
        $this->io->progressFinish();

        return self::EXIT_OK;
    }

    /**
     * Create CSV reader.
     *
     * @param SplFileInfo $file Source file
     *
     * @return Reader
     * @throws \FinalGene\PhoneBook\Exception\ReadFileException
     */
    protected function createReader(SplFileInfo $file): Reader
    {
        return Reader::createFromStream($this->getResourceForFile($file));
    }

    /**
     * Create vcard from row.
     *
     * @param array $row CSV row data
     *
     * @return VCard
     * @throws \InvalidArgumentException
     * @throws \libphonenumber\NumberParseException
     */
    protected function createVCardFromRow(array $row): VCard
    {
        $vCard = $this->createVCard();
        $vCard->add('FN', '');

        foreach ($row as $field => $value) {
            if (preg_match('/[\s\d\+\(\)]/', $value)) {
                $vCard->add(
                    'TEL',
                    $this->normalizePhoneNumber($value),
                    [
                        'type' => $this->normalizePhoneNumberType($field),
                    ]
                );
                continue;
            }

            if (false !== filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $vCard->add(
                    'EMAIL',
                    $value,
                    [
                        'type' => $this->normalizeEmailType($field),
                    ]
                );
                continue;
            }

            /** @var FlatText $fullName */
            $fullName = $vCard->select('FN')[0];
            $fullName->setValue(
                trim($fullName->getValue() . ' ' . $value)
            );
        }

        return $vCard->convert(Document::VCARD40);
    }
}
