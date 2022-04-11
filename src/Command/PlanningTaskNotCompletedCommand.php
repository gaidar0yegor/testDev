<?php

namespace App\Command;

use App\Entity\ProjetPlanningTask;
use App\Notification\Event\PlanningTaskNotCompletedNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlanningTaskNotCompletedCommand extends Command
{
    protected static $defaultName = 'app:notif-not-completed-planning-task';

    private EntityManagerInterface $em;
    private EventDispatcherInterface $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        parent::__construct();

        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setDescription('Envoyer des notification si progress d\'une tâche < 100 en j-3 du date fin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $retartedTasks = $this->em->getRepository(ProjetPlanningTask::class)->getForLateNotification();

        foreach ($retartedTasks as $retartedTask){
            $this->dispatcher->dispatch(new PlanningTaskNotCompletedNotification($retartedTask));
        }

        $io->success('Notifications est envoyées avec succés !');

        return 0;
    }
}
