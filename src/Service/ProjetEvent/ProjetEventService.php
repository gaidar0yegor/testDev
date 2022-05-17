<?php

namespace App\Service\ProjetEvent;

use App\Entity\Projet;
use App\Entity\ProjetEvent;
use App\Entity\ProjetEventParticipant;
use App\Entity\ProjetParticipant;
use App\MultiSociete\UserContext;
use App\Service\ParticipantService;
use Symfony\Component\HttpFoundation\Request;

class ProjetEventService
{
    private UserContext $userContext;
    private ParticipantService $participantService;

    public function __construct(UserContext $userContext, ParticipantService $participantService)
    {
        $this->userContext = $userContext;
        $this->participantService = $participantService;
    }

    public static function getProjetEventParticipant(ProjetEvent $projetEvent, ProjetParticipant $projetParticipant, bool $required = null): ?ProjetEventParticipant
    {
        foreach ($projetEvent->getProjetEventParticipants() as $projetEventParticipant) {
            if ($projetEventParticipant->getParticipant() === $projetParticipant) {
                if ( $required === null || $projetEventParticipant->getRequired() === $required ){
                    return $projetEventParticipant;
                }
            }
        }

        return null;
    }

    public function getProjetEventsData(Projet $projet): array
    {
        $response = [];
        foreach ($projet->getProjetEvents() as $projetEvent){
            $projetParticipant = $this->participantService->getProjetParticipant($this->userContext->getSocieteUser(), $projet);
            $is_invited = $projetParticipant ? (ProjetEventService::getProjetEventParticipant($projetEvent, $projetParticipant) !== null) : false;

            $response['data'][] = [
                'id' => $projetEvent->getId(),
                'text' => $projetEvent->getText(),
                'description' => $projetEvent->getDescription(),
                'location' => $projetEvent->getLocation(),
                'start_date' => $projetEvent->getStartDate()->format('Y-m-d H:i'),
                'end_date' => $projetEvent->getEndDate()->format('Y-m-d H:i'),
                'eventType' => $projetEvent->getType(),

                'required_participant_ids' => implode(",", $projetEvent->getProjetEventParticipants()->filter(function($projetEventParticipant) {
                    return $projetEventParticipant->getRequired() === true;
                })->map(function($projetEventParticipant){ return $projetEventParticipant->getParticipant()->getId(); })->getValues()),

                'optional_participant_ids' => implode(",", $projetEvent->getProjetEventParticipants()->filter(function($projetEventParticipants) {
                    return $projetEventParticipants->getRequired() === false;
                })->map(function($projetEventParticipant){ return $projetEventParticipant->getParticipant()->getId(); })->getValues()),

                'readonly' => $projetEvent->getCreatedBy() !== $this->userContext->getSocieteUser(),
                'createdByFullname' => $projetEvent->getCreatedBy()->getUser()->getFullname(),
                'is_invited' => $is_invited
            ];
        }
        return $response;
    }

    public function saveProjetEventFromRequest(Request $request, Projet $projet, ProjetEvent $projetEvent = null): ProjetEvent
    {
        if (null === $projetEvent){
            $projetEvent = new ProjetEvent();
        }

        $projetEvent->setProjet($projet);
        $projetEvent->setCreatedBy($this->userContext->getSocieteUser());

        $projetEvent->setText($request->request->get('text'));
        $projetEvent->setDescription($request->request->get('description'));
        $projetEvent->setLocation($request->request->get('location'));
        $projetEvent->setStartDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('start_date')));
        $projetEvent->setEndDate(\DateTime::createFromFormat('Y-m-d H:i', $request->request->get('end_date')));
        if ($request->request->has('eventType')) $projetEvent->setType($request->request->get('eventType'));

        $projetEvent = self::createProjetEventParticipants(
            $projetEvent,
            array_map('intval', explode(',', $request->request->get('required_participant_ids'))),
            array_map('intval', explode(',', $request->request->get('optional_participant_ids')))
        );

        return $projetEvent;
    }

    private static function createProjetEventParticipants(
        ProjetEvent $projetEvent,
        array $required_participant_ids,
        array $optional_participant_ids
    ) : ProjetEvent
    {
        foreach ($projetEvent->getProjet()->getProjetParticipants() as $participant){
            $oldRequiredParticipation = self::getProjetEventParticipant($projetEvent, $participant, true);
            $oldOptionalParticipation = self::getProjetEventParticipant($projetEvent, $participant, false);

            if (!in_array($participant->getId(), $required_participant_ids) && null !== $oldRequiredParticipation){
                $projetEvent->removeProjetEventParticipant($oldRequiredParticipation);
            }
            if (null !== $oldOptionalParticipation){
                if (in_array($participant->getId(), $required_participant_ids) ||
                    !in_array($participant->getId(), $optional_participant_ids))
                    $projetEvent->removeProjetEventParticipant($oldOptionalParticipation);
            }

            if (in_array($participant->getId(), $required_participant_ids)){
                if (null === $oldRequiredParticipation)
                    $projetEvent->addProjetEventParticipant(ProjetEventParticipant::create($projetEvent, $participant, true));
            }
            elseif (in_array($participant->getId(), $optional_participant_ids) && null === $oldOptionalParticipation){
                $projetEvent->addProjetEventParticipant(ProjetEventParticipant::create($projetEvent, $participant, false));
            }
        }

        return $projetEvent;
    }
}
