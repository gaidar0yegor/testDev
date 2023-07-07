<?php

namespace App\Command;

use App\Entity\ProjetPlanningTask;
use App\Notification\Event\PlanningTaskStartSoonNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlanningTaskStartSoonCommand extends Command
{
    protected static $defaultName = 'app:notif-start-soon-planning-task';

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
        $this->setDescription('Envoyer des notification de début de tache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $todayPlanningTasks = $this->em->getRepository(ProjetPlanningTask::class)->findBy(['startDate' => (new \DateTime())]);

        $sumSendedNotif = 0;
        foreach ($todayPlanningTasks as $todayPlanningTask){
            $sumSendedNotif++;
            $this->dispatcher->dispatch(new PlanningTaskStartSoonNotification($todayPlanningTask));
        }

        if ($sumSendedNotif === 0){
            $io->success('Aucune notification est envoyée.');
        } else {
            $io->success($sumSendedNotif . ' notification(s) est envoyée(s) avec succés !');
        }


        return 0;
    }
}
