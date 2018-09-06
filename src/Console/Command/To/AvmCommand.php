<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\To;

use FinalGene\PhoneBook\AVM\Contact;
use FinalGene\PhoneBook\AVM\Number;
use FinalGene\PhoneBook\AVM\Person;
use FinalGene\PhoneBook\AVM\Phonebook;
use FinalGene\PhoneBook\AVM\Phonebooks;
use FinalGene\PhoneBook\Console\Command\VCardTrait;
use FinalGene\PhoneBook\Console\Helper\SerializerHelper;
use FinalGene\PhoneBook\Exception\ReadFileException;
use JMS\Serializer\Serializer;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Parameter;
use Sabre\VObject\ParseException;
use Sabre\VObject\Property;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AVM command class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 */
class AvmCommand extends Command
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
    public const TITLE = 'Create AVM XML';

    /**
     * Command name
     */
    public const NAME = 'to:avm';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Convert vCard set to AVM XML';

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
     * @var Serializer
     */
    private $avmSerializer;

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
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $style = new SymfonyStyle($input, $output);
        $this->io = $style->getErrorStyle();
        $this->io->title(self::TITLE);

        $this->avmSerializer = $this->getHelper(SerializerHelper::class)->getAvmSerializer();
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

        $phoneBooks = $this->createPhoneBooks();

        /** @var VCard $vCard */
        try {
            while (null !== ($vCard = $vCardList->getNext())) {
                $phoneBooks->getPhonebook()->addToContact(
                    $this->createContactFromVCard($vCard)
                );
            }
        } catch (ParseException $e) {
            $this->io->error($e->getMessage());

            return self::EXIT_VCARD_ERROR;
        }

        $output->write(
            $this->avmSerializer->serialize($phoneBooks, 'xml')
        );

        return self::EXIT_OK;
    }

    /**
     * Create phone books.
     *
     * @return Phonebooks
     */
    protected function createPhoneBooks(): Phonebooks
    {
        $phoneBooks = new Phonebooks();
        $phoneBooks->setPhonebook(new Phonebook());

        return $phoneBooks;
    }

    /**
     * Create contact from vcard.
     *
     * @param VCard $vCard vCard
     *
     * @return Contact
     */
    protected function createContactFromVCard(VCard $vCard): Contact
    {
        $contact = new Contact();
        $contact->setPerson($this->createPersonByVCard($vCard));

        /** @var Property $element */
        foreach ($vCard->select('TEL') as $element) {
            $contact->addToTelephony($this->createNumberByUriElement($element));
        }

        return $contact;
    }

    /**
     * Create person by vCard.
     *
     * @param VCard $vCard vCard
     *
     * @return Person
     */
    protected function createPersonByVCard(VCard $vCard): Person
    {
        $person = new Person();
        $person->setRealName((string)$vCard->{'FN'});

        return $person;
    }

    /**
     * Create number by uri element.
     *
     * @param Property $element vCard property element
     *
     * @return Number
     */
    protected function createNumberByUriElement(Property $element): Number
    {
        $number = new Number($element->getValue());

        /** @var Parameter $parameter */
        foreach ($element->parameters() as $parameter) {
            if ('TYPE' === $parameter->name) {
                $number->setType($parameter->getParts()[0]);
                break;
            }
        }

        return $number;
    }
}
