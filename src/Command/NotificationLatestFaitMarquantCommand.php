<?php

namespace App\Command;

use App\Service\NotificationFaitMarquants;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationLatestFaitMarquantCommand extends Command
{
    protected static $defaultName = 'app:notifie-derniers-faits-marquants';

    private NotificationFaitMarquants $notificationFaitMarquants;

    public function __construct(
        NotificationFaitMarquants $notificationFaitMarquants
    ) {
        parent::__construct();

        $this->notificationFaitMarquants = $notificationFaitMarquants;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoie une notification aux utilisateurs pour leur afficher les faits marquants dernièrement créés sur leurs projets.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $totalSent = $this->notificationFaitMarquants->sendLatestFaitsMarquantsToAllUsers();

        $io->success("$totalSent emails de notification ont été envoyés !");

        return 0;
    }
}
