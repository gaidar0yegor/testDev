<?php

namespace App\Listener;

use App\Entity\EvenementParticipant;
use App\Exception\TimesheetException;
use App\Service\EvenementService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EvenementParticipantListener
{
    private EvenementService $evenementService;

    public function __construct(EvenementService $evenementService)
    {
        $this->evenementService = $evenementService;
    }

    public function prePersist(EvenementParticipant $evenementParticipant, LifecycleEventArgs $args): void
    {
        try {
            $heuresMonths = $this->evenementService->generateHeuresMonths($evenementParticipant);
        } catch (TimesheetException $e) {
        }
        $evenementParticipant->setHeures($heuresMonths);
    }

    public function postUpdate(EvenementParticipant $evenementParticipant, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        try {
            $heuresMonths = $this->evenementService->generateHeuresMonths($evenementParticipant);
        } catch (TimesheetException $e) {
        }
        $evenementParticipant->setHeures($heuresMonths);
        $em->persist($evenementParticipant);
        $em->flush();
    }
}
