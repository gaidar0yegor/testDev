<?php

namespace App\Command\NotificationCommand;

use App\Notification\Event\LatestFaitMarquantsNotification;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NotificationLatestFaitMarquantCommand extends AbstractNotificationCommand
{
    public static $defaultName = 'app:notifie-derniers-faits-marquants';

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
            ->setDescription('Envoie une notification aux utilisateurs pour leur afficher les faits marquants dernièrement créés sur leurs projets.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dispatcher->dispatch(new LatestFaitMarquantsNotification($this->findCommandSociete($input)));

        return 0;
    }
}
