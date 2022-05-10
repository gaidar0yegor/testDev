<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\ProjetEvent;
use App\Entity\ProjetEventParticipant;
use App\Entity\ProjetParticipant;
use Symfony\Component\HttpFoundation\Request;

class ProjetEventService
{
    public static function getProjetEventParticipant(ProjetEvent $projetEvent, ProjetParticipant $projetParticipant): ?ProjetEventParticipant
    {
        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant) {
            if ($projetEventParticipant->getParticipant() === $projetParticipant) {
                return $projetEventParticipant;
            }
        }

        return null;
    }

    public static function saveProjetEventFromRequest(Request $request, Projet $projet, ProjetEvent $projetEvent = null): ProjetEvent
    {
        if (null === $projetEvent){
            $projetEvent = new ProjetEvent();
        }

        $projetEvent->setProjet($projet);
        $projetEvent->setText($request->request->get('text'));
        $projetEvent->setDescription($request->request->get('description'));
        $projetEvent->setStartDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('start_date')));
        $projetEvent->setEndDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('end_date')));
        $projetEvent->setType($request->request->get('eventType'));

        $participant_new_ids = array_map('intval', explode(',', $request->request->get('participant_id')));
        foreach ($projet->getProjetParticipants() as $participant){
            $oldParticipation = self::getProjetEventParticipant($projetEvent, $participant);
            if (in_array($participant->getId(), $participant_new_ids) && null === $oldParticipation){
                $projetEvent->addProjetEventParticipant(ProjetEventParticipant::create($projetEvent, $participant));
            } elseif (!in_array($participant->getId(), $participant_new_ids) && null !== $oldParticipation){
                $projetEvent->removeProjetEventParticipant($oldParticipation);
            }
        }

        return $projetEvent;
    }
}
