<?php

namespace App\Command;

use App\Service\RdiScore\ProjetScoreRdiUpdater;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateProjetsRdiScoreCommand extends Command
{
    protected static $defaultName = 'app:update-projets-rdi-score';

    private ProjetScoreRdiUpdater $projetScoreRdiUpdater;

    public function __construct(ProjetScoreRdiUpdater $projetScoreRdiUpdater)
    {
        parent::__construct();

        $this->projetScoreRdiUpdater = $projetScoreRdiUpdater;
    }

    protected function configure()
    {
        $this
            ->setDescription('Met à jour les scores RDI des projets')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Log also 'info' by default
        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        ];

        $logger = new ConsoleLogger($output, $verbosityLevelMap);

        $this->projetScoreRdiUpdater->updateProjetsScore($logger);

        $io->success('All projets RDI score have been re-calculated.');

        return Command::SUCCESS;
    }
}
