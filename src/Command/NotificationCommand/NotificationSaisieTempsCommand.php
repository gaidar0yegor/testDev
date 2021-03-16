<?php

namespace App\Command\NotificationCommand;

use App\Notification\Event\RappelSaisieTempsNotification;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationSaisieTempsCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-saisie-temps';

    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        parent::__construct();

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
        $this->dispatcher->dispatch(new RappelSaisieTempsNotification($this->findCommandSociete($input)));

        return 0;
    }
}
