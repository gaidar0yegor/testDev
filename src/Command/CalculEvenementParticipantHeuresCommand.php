<?php

namespace App\Command;

use App\Entity\EvenementParticipant;
use App\Exception\TimesheetException;
use App\Service\EvenementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculEvenementParticipantHeuresCommand extends Command
{
    protected static $defaultName = 'app:calcul-evenement-participant-heures';
    protected static $defaultDescription = 'Calculer le nombre d\'heure par evenement par jour';

    private EntityManagerInterface $em;
    private EvenementService $evenementService;

    public function __construct(
        EntityManagerInterface $em,
        EvenementService $evenementService
    )
    {
        parent::__construct();

        $this->em = $em;
        $this->evenementService = $evenementService;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Loop all EvenementParticipant');

        $evenementParticipants = $this->em->getRepository(EvenementParticipant::class)->findAll();

        $count = 0;
        foreach ($evenementParticipants as $evenementParticipant) {
            if (
                null === $evenementParticipant->getProjet()
            ) continue;

            try {
                $heuresMonths = $this->evenementService->generateHeuresMonths($evenementParticipant);
            } catch (TimesheetException $e) {
            }
            $evenementParticipant->setHeures($heuresMonths);
            $this->em->persist($evenementParticipant);
            $count++;
        }

        $this->em->flush();

        $io->info($count . ' EvenementParticipant are updated');

        return Command::SUCCESS;
    }
}
