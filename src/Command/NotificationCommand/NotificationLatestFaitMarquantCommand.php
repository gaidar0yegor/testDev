<?php

namespace App\Command\NotificationCommand;

use App\Service\NotificationFaitMarquants;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotificationLatestFaitMarquantCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-derniers-faits-marquants';

    private NotificationFaitMarquants $notificationFaitMarquants;

    public function __construct(
        NotificationFaitMarquants $notificationFaitMarquants
    ) {
        parent::__construct();

        $this->notificationFaitMarquants = $notificationFaitMarquants;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Envoie une notification aux utilisateurs pour leur afficher les faits marquants dernièrement créés sur leurs projets.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $totalSent = $this
            ->notificationFaitMarquants
            ->sendLatestFaitsMarquantsToAllUsers($this->findCommandSociete($input))
        ;

        $io->success("$totalSent emails de notification ont été envoyés !");

        return 0;
    }
}
