<?php

namespace App\Command;

use App\Onboarding\ReminderNotification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendReminderNotificationsCommand extends Command
{
    protected static $defaultName = 'app:onboarding:send-notifications';

    private ReminderNotification $reminderNotification;

    public function __construct(ReminderNotification $reminderNotification)
    {
        parent::__construct();

        $this->reminderNotification = $reminderNotification;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoi les notifications de relance aux utilisateurs encore dans leur onboarding.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->reminderNotification->dispatchReminderNotifications();

        return 0;
    }
}
