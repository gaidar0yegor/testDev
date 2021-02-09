<?php

namespace App\Command;

use App\Repository\SocieteRepository;
use App\Slack\Slack;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestSlackCommand extends Command
{
    protected static $defaultName = 'app:test-slack';

    private Slack $slack;

    private SocieteRepository $societeRepository;

    public function __construct(
        Slack $slack,
        SocieteRepository $societeRepository
    ) {
        parent::__construct();

        $this->slack = $slack;
        $this->societeRepository = $societeRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoi une notification Slack de test pour tester la connection d\'une société à Slack.')
            ->addArgument('societe', null, InputOption::VALUE_REQUIRED, 'Id de la société à notifier sur Slack')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $societeId = $input->getArgument('societe');
        $societe = $this->societeRepository->find($societeId);

        $verbosityLevelMap = [LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL];
        $logger = new ConsoleLogger($output, $verbosityLevelMap);

        $this->slack->sendMessage($societe, 'Notification de test envoyée depuis RDI-Manager.', $logger);

        return 0;
    }
}
