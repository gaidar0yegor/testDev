<?php

namespace App\Command;

use App\Service\RdiScore\ScoreUpdater;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateProjetsRdiScoreCommand extends Command
{
    protected static $defaultName = 'app:update-projets-rdi-score';

    private ScoreUpdater $scoreUpdater;

    public function __construct(ScoreUpdater $scoreUpdater)
    {
        parent::__construct();

        $this->scoreUpdater = $scoreUpdater;
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

        $this->scoreUpdater
            ->setLogger(new ConsoleLogger($output, $verbosityLevelMap))
            ->updateAllProjetScore()
        ;

        $io->success('All projets RDI score have been re-calculated.');

        return Command::SUCCESS;
    }
}
