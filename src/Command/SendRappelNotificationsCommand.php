<?php

namespace App\Command;

use App\Entity\Rappel;
use App\Notification\Event\RemindeRappelEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SendRappelNotificationsCommand extends Command
{
    protected static $defaultName = 'app:rappel:send-notifications';

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
        $this
            ->setDescription('Envoi les notifications des rappels créés par les utilisateurs.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rappels = $this->em->getRepository(Rappel::class)->findToReminder();
        $sumSendedNotif = 0;
        foreach ($rappels as $rappel){
            $this->dispatcher->dispatch(new RemindeRappelEvent($rappel));
            $rappel->setIsReminded(true);
            $rappel->setAcknowledged(false);
            $this->em->persist($rappel);
            $sumSendedNotif++;
        }

        $this->em->flush();

        if ($sumSendedNotif === 0){
            $io->success('Aucun rappel est envoyé.');
        } else {
            $io->success($sumSendedNotif . ' rappel(s) est envoyé(s) avec succés !');
        }

        return 0;
    }
}
