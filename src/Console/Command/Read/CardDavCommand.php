<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Console\Command\Read;

use BarnabyWalters\CardDAV\Client;
use Exception;
use FinalGene\PhoneBook\Console\Question\GetPasswordTrait;
use FinalGene\PhoneBook\Console\Command\VCardTrait;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Document;
use Sabre\VObject\Reader;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function count;

/**
 * Card dav command class.
 *
 * @package FinalGene\PhoneBook\Command\Read
 */
class CardDavCommand extends Command
{
    use GetPasswordTrait;
    use VCardTrait;

    /**
     * Command exit code if everything is ok
     */
    public const EXIT_OK = 0;

    /**
     * Command exit code if an error occurred during dav communication
     */
    public const EXIT_DAV_ERROR = 1;

    /**
     * Command title
     */
    public const TITLE = 'Read from CardDav';

    /**
     * Command name
     */
    public const NAME = 'read:card-dav';

    /**
     * Command description
     */
    public const DESCRIPTION = 'Get contacts from a CardDav Server account';

    /**
     * Name of server option
     */
    public const SERVER_OPTION_NAME = 'server-url';

    /**
     * Description of server option
     */
    public const SERVER_OPTION_DESCRIPTION = 'CardDav Server query (host and query uri including the user account)';

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
     * Name of password argument
     */
    public const PASSWORD_INTERACT_ARGUMENT_NAME = 'password';

    /**
     * Name of password environment
     */
    public const PASSWORD_ENVIRONMENT_NAME = 'CARDDAV_PASSWORD';

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
            self::SERVER_OPTION_NAME,
            's',
            InputOption::VALUE_REQUIRED,
            self::SERVER_OPTION_DESCRIPTION
        );
        $this->addOption(
            self::USER_OPTION_NAME,
            'u',
            InputOption::VALUE_REQUIRED,
            self::USER_OPTION_DESCRIPTION
        );
        $this->addOption(
            self::ASK_PASSWORD_OPTION_NAME,
            'p',
            InputOption::VALUE_NONE,
            self::ASK_PASSWORD_OPTION_DESCRIPTION
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $server = $input->getOption(self::SERVER_OPTION_NAME);
        $user = $input->getOption(self::USER_OPTION_NAME);

        $password = getenv(self::PASSWORD_ENVIRONMENT_NAME) ?: '';
        if (true === $input->hasArgument(self::PASSWORD_INTERACT_ARGUMENT_NAME)) {
            $password = $input->getArgument(self::PASSWORD_INTERACT_ARGUMENT_NAME);
        }

        $this->io->text('query dav server...');
        $client = $this->createClient($server, $user, $password);
        if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
            $client->enable_debug();
        }

        try {
            $vCardIdList = $this->getVCardIdListByClient($client);
        } catch (Exception $e) {
            if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
                $this->io->writeln(var_export($client->get_debug(), true));
            }
            $this->io->error($e->getMessage());

            return self::EXIT_DAV_ERROR;
        }

        $this->io->progressStart(count($vCardIdList));
        foreach ($vCardIdList as $vCardId) {
            try {
                $output->write(
                    $this->getVCardById($vCardId, $client)->serialize()
                );
                $this->io->progressAdvance();
            } catch (Exception $e) {
                $this->io->warning($e->getMessage());
                continue;
            }
        }
        $this->io->progressFinish();

        return self::EXIT_OK;
    }

    /**
     * Create card dav client.
     *
     * @param string $server
     * @param string $user
     * @param string $password
     *
     * @return Client
     */
    protected function createClient(string $server, string $user, string $password): Client
    {
        $client = new Client($server);
        $client->set_auth($user, $password);

        return $client;
    }

    /**
     * Get vCard id list by client.
     *
     * @param Client $client
     *
     * @return string[]
     * @throws Exception
     */
    protected function getVCardIdListByClient(Client $client): array
    {
        $response = new SimpleXMLElement($client->get(false));
        $idList = $response->xpath('/response/element/id');
        array_walk(
            $idList,
            function (&$item) {
                $item = (string)$item;
            }
        );

        return $idList;
    }

    /**
     * Get vCard by id.
     *
     * @param string $id
     * @param Client $client
     *
     * @return VCard
     * @throws Exception
     */
    protected function getVCardById(string $id, Client $client): VCard
    {
        /** @var VCard $vCard */
        $vCard = Reader::read($client->get_vcard($id));
        return $vCard->convert(Document::VCARD40);
    }
}
