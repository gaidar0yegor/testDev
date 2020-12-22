<?php

namespace App\Command\NotificationCommand;

use App\Service\NotificationSaisieTemps;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationSaisieTempsCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-saisie-temps';

    private NotificationSaisieTemps $notificationSaisieTemps;

    public function __construct(
        NotificationSaisieTemps $notificationSaisieTemps
    ) {
        parent::__construct();

        $this->notificationSaisieTemps = $notificationSaisieTemps;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Envoie une notification aux utilisateurs pour les rappeller de saisir leur temps.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $totalSent = $this
            ->notificationSaisieTemps
            ->sendNotificationSaisieTempsAllUsers($this->findCommandSociete($input))
        ;

        $io->success("$totalSent notifications ont été envoyés !");

        return 0;
    }
}
