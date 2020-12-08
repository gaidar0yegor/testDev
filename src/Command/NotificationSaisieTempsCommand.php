<?php

namespace App\Command;

use App\Entity\User;
use App\Service\NotificationSaisieTemps;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationSaisieTempsCommand extends Command
{
    protected static $defaultName = 'app:notifie-saisie-temps';

    private NotificationSaisieTemps $notificationSaisieTemps;

    public function __construct(
        NotificationSaisieTemps $notificationSaisieTemps
    ) {
        parent::__construct();

        $this->notificationSaisieTemps = $notificationSaisieTemps;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoie une notification aux utilisateurs pour les rappeller de saisir leur temps.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $totalSent = $this->notificationSaisieTemps->sendNotificationSaisieTempsAllUsers();

        $io->success("$totalSent emails de notification ont été envoyés !");

        return 0;
    }
}
