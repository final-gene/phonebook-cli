<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use FinalGene\PhoneBook\Console\Question\GetPasswordTrait;
use FinalGene\PhoneBook\Console\Command\VCardTrait;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Type\ContactItemType;
use jamesiarmes\PhpEws\Type\ContactsViewType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Type\PhoneNumberDictionaryEntryType;
use libphonenumber\NumberParseException;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function is_array;

/**
 * Exchange webservice command class.
 *
 * @package FinalGene\PhoneBook\Console\Command\Read
 */
class EwsCommand extends Command
{
    use GetPasswordTrait;
    use PhoneNumberTrait;
    use VCardTrait;

    /**
     * Command exit code if everything is ok
     */
    public const EXIT_OK = 0;

    /**
     * Command exit code if an error occurred during EWS communication
     */
    public const EXIT_EWS_ERROR = 1;

    /**
     * Command title
     */
    public const TITLE = 'Read from exchange web service';

    /**
     * Command name
     */
    public const NAME = 'read:ews';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Get contacts from a Microsoft Exchange WebService account';

    /**
     * Name of host option
     */
    public const HOST_OPTION_NAME = 'host';

    /**
     * Description of host option
     */
    public const HOST_OPTION_DESCRIPTION = 'Microsoft Exchange WebService host name';

    /**
     * Name of user option
     */
    public const USER_OPTION_NAME = 'user';

    /**
     * Description of user option
     */
    public const USER_OPTION_DESCRIPTION = 'User login name';

    /**
     * Name of password option
     */
    public const ASK_PASSWORD_OPTION_NAME = 'password';

    /**
     * Description of password option
     */
    public const ASK_PASSWORD_OPTION_DESCRIPTION = 'Ask for users password';

    /**
     * Name of insecure option
     */
    public const INSECURE_OPTION_NAME = 'insecure';

    /**
     * Description of insecure option
     */
    public const INSECURE_OPTION_DESCRIPTION = 'Don\'t validate ssl certificates';

    /**
     * Name of password argument
     */
    public const PASSWORD_INTERACT_ARGUMENT_NAME = 'password';

    /**
     * Name of password environment
     */
    public const PASSWORD_ENVIRONMENT_NAME = 'EXCHANGE_PASSWORD';

    /**
     * Name of version option
     */
    public const VERSION_OPTION_NAME = 'exchange-version';

    /**
     * Description of version option
     */
    public const VERSION_OPTION_DESCRIPTION = 'Version of the Microsoft Exchange Server';

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
            self::HOST_OPTION_NAME,
            's',
            InputOption::VALUE_REQUIRED,
            self::HOST_OPTION_DESCRIPTION
        );
        $this->addOption(
            self::USER_OPTION_NAME,
            'u',
            InputOption::VALUE_REQUIRED,
            self::USER_OPTION_DESCRIPTION
        );
        $this->addOption(
            self::VERSION_OPTION_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            self::VERSION_OPTION_DESCRIPTION,
            Client::VERSION_2007
        );
        $this->addOption(
            self::ASK_PASSWORD_OPTION_NAME,
            'p',
            InputOption::VALUE_NONE,
            self::ASK_PASSWORD_OPTION_DESCRIPTION
        );
        $this->addOption(
            self::INSECURE_OPTION_NAME,
            null,
            InputOption::VALUE_NONE,
            self::INSECURE_OPTION_DESCRIPTION
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
     * Interact.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (true === $input->getOption(self::ASK_PASSWORD_OPTION_NAME)) {
            $this->getDefinition()
                ->addArguments(
                    [
                        new InputArgument(self::PASSWORD_INTERACT_ARGUMENT_NAME),
                    ]
                );

            $input->setArgument(
                self::PASSWORD_INTERACT_ARGUMENT_NAME,
                $this->getPasswordFromUser($this->io)
            );
        }

    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $password = getenv(self::PASSWORD_ENVIRONMENT_NAME) ?: '';
        if (true === $input->hasArgument(self::PASSWORD_INTERACT_ARGUMENT_NAME)) {
            $password = $input->getArgument(self::PASSWORD_INTERACT_ARGUMENT_NAME);
        }

        $this->io->text('query ews server...');
        $client = $this->createClient(
            $input->getOption(self::HOST_OPTION_NAME),
            $input->getOption(self::USER_OPTION_NAME),
            $password,
            $input->getOption(self::VERSION_OPTION_NAME),
            true === $input->getOption(self::INSECURE_OPTION_NAME)
        );
        $response = $client->FindItem($this->createRequest());
        $responseMessageList = $response->ResponseMessages->FindItemResponseMessage;

        $vCard = $this->createVCard();
        foreach ($responseMessageList as $responseMessage) {
            if (ResponseClassType::SUCCESS !== $responseMessage->ResponseClass) {
                $this->io->error(
                    sprintf('%s: %s', $responseMessage->ResponseCode, $responseMessage->MessageText)
                );

                return self::EXIT_EWS_ERROR;
            }

            $this->io->progressStart($responseMessage->RootFolder->TotalItemsInView);

            foreach ($responseMessage->RootFolder->Items->Contact as $item) {
                try {
                    $vCard->add(
                        $this->createVCardFromEwsItem($item)
                    );
                    $this->io->progressAdvance();
                } catch (NumberParseException $e) {
                    $this->io->warning($e->getMessage());
                }
            }

            $this->io->progressFinish();
        }

        $output->write($vCard->serialize());

        return self::EXIT_OK;
    }

    /**
     * Create EWS client.
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $version
     * @param bool   $insecure
     *
     * @return Client
     */
    protected function createClient(
        string $host,
        string $user,
        string $password,
        string $version,
        bool $insecure
    ): Client {
        $client = new Client(
            $host,
            $user,
            $password,
            $version
        );

        if ($insecure) {
            /** @noinspection CurlSslServerSpoofingInspection */
            $client->setCurlOptions(
                [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]
            );
        }

        return $client;
    }

    /**
     * Create request.
     *
     * @return FindItemType
     */
    protected function createRequest(): FindItemType
    {
        $request = new FindItemType();

        $request->ItemShape = new ItemResponseShapeType();
        $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

        $request->ContactsView = new ContactsViewType();
        $request->ContactsView->InitialName = 'a';
        $request->ContactsView->FinalName = 'z';

        $distinguishedFolderId = new DistinguishedFolderIdType();
        $distinguishedFolderId->Id = DistinguishedFolderIdNameType::CONTACTS;

        $request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();
        $request->ParentFolderIds->DistinguishedFolderId = [
            $distinguishedFolderId,
        ];

        $request->Traversal = ItemQueryTraversalType::SHALLOW;

        return $request;
    }

    /**
     * Create vCard from ews item.
     *
     * @param ContactItemType $item
     *
     * @return VCard
     * @throws \InvalidArgumentException
     * @throws \libphonenumber\NumberParseException
     */
    protected function createVCardFromEwsItem(ContactItemType $item): VCard
    {
        $vCard = $this->createVCard();

        $vCard->add(
            'FN',
            $item->DisplayName
        );

        $vCard->add(
            'N',
            [
                $item->Surname,
                $item->GivenName,
                '',
                '',
                '',
            ]
        );

        $vCard->add(
            'NOTE',
            $item->Notes
        );


        if (null !== $item->PhoneNumbers) {
            if (is_array($item->PhoneNumbers->Entry)) {
                foreach ($item->PhoneNumbers->Entry as $entry) {
                    $this->addPhoneNumberToVCard($vCard, $entry);
                }
            } else {
                $this->addPhoneNumberToVCard($vCard, $item->PhoneNumbers->Entry);
            }
        }

        return $vCard->convert(Document::VCARD40);
    }

    /**
     * Add phone number to vCard.
     *
     * @param VCard                          $vCard
     * @param PhoneNumberDictionaryEntryType $entry
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \libphonenumber\NumberParseException
     */
    protected function addPhoneNumberToVCard(VCard $vCard, PhoneNumberDictionaryEntryType $entry): void
    {
        if (empty($entry->_)) {
            return;
        }

        $vCard->add(
            'TEL',
            $this->normalizePhoneNumber($entry->_),
            [
                'type' => $this->normalizePhoneNumberType($entry->Key),
            ]
        );
    }
}
