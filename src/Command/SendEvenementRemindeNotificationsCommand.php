<?php

namespace App\Command;

use App\Entity\Evenement;
use App\Notification\Event\RemindeEvenementEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SendEvenementRemindeNotificationsCommand extends Command
{
    protected static $defaultName = 'app:evenement:send-reminde-notifications';

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
            ->setDescription('Envoi les notifications pour rappler les persennes invitées aux événements.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $evenements = $this->em->getRepository(Evenement::class)->findToReminder();
        $sumSendedNotif = 0;
        foreach ($evenements as $evenement){
            $this->dispatcher->dispatch(new RemindeEvenementEvent($evenement));
            $evenement->setIsReminded(true);
            $this->em->persist($evenement);
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
