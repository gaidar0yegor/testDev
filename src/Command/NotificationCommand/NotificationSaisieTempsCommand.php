<?php

namespace App\Command\NotificationCommand;

use App\Notification\RappelSaisieTempsNotification;
use App\Service\NotificationSaisieTemps;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationSaisieTempsCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-saisie-temps';

    private NotificationSaisieTemps $notificationSaisieTemps;

    private EventDispatcherInterface $dispatcher;

    public function __construct(
        NotificationSaisieTemps $notificationSaisieTemps,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct();

        $this->notificationSaisieTemps = $notificationSaisieTemps;
        $this->dispatcher = $dispatcher;
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
        $societe = $this->findCommandSociete($input);

        $this->dispatcher->dispatch(new RappelSaisieTempsNotification($societe));

        $totalSent = $this
            ->notificationSaisieTemps
            ->sendNotificationSaisieTempsAllUsers($societe)
        ;

        $io->success("$totalSent notifications ont été envoyés !");

        return 0;
    }
}
