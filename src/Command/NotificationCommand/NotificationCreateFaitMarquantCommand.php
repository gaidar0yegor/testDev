<?php

namespace App\Command\NotificationCommand;

use App\Notification\Event\RappelCreationFaitMarquantsNotification;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationCreateFaitMarquantCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-creer-faits-marquants';

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
            ->setDescription('Envoie une notification aux utilisateurs pour les rappeller de créer leurs faits marquants.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dispatcher->dispatch(new RappelCreationFaitMarquantsNotification($this->findCommandSociete($input)));

        return 0;
    }
}
