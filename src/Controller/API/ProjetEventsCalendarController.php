<?php

namespace App\Controller\API;

use App\Entity\Projet;
use App\Entity\ProjetEvent;
use App\Entity\ProjetEventParticipant;
use App\Entity\ProjetParticipant;
use App\MultiSociete\UserContext;
use App\Service\ProjetEventService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/projet/{projetId}/events")
 */
class ProjetEventsCalendarController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected TranslatorInterface $translator;
    protected ValidatorInterface $validator;
    protected ProjetEventService $projetEventService;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        ProjetEventService $projetEventService
    )
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->projetEventService = $projetEventService;
    }

    /**
     * @Route("", methods={"GET"}, name="api_get_projet_events_list" )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function getEvents(Projet $projet)
    {
        $response = [];
        foreach ($projet->getProjetEvents() as $projetEvent){
            $response['data'][] = [
                'id' => $projetEvent->getId(),
                'text' => $projetEvent->getText(),
                'description' => $projetEvent->getDescription(),
                'start_date' => $projetEvent->getStartDate()->format('Y-m-d H:i'),
                'end_date' => $projetEvent->getEndDate()->format('Y-m-d H:i'),
                'eventType' => $projetEvent->getType(),
                'participant_id' => implode(",",$projetEvent->getProjetEventParticipants()->map(function (ProjetEventParticipant $projetEventParticipant){
                    return $projetEventParticipant->getParticipant()->getId();
                })->toArray())
            ];
        }

        $response = self::setResponseCollections($response, $projet, $this->translator);

        return new JsonResponse($response);
    }

    /**
     * @Route("", methods={"POST"}, name="app_fo_projet_events_post")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function save(Request $request, Projet $projet)
    {
        $projetEvent = $this->projetEventService->saveProjetEventFromRequest($request, $projet);

        if ($errorResponse = self::validateProjetEvent($projetEvent, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($projetEvent);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "tid" => $projetEvent->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/{eventId}",
     *      methods={"PUT"},
     *      name="app_fo_projet_events_update"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetEvent", options={"id" = "eventId"})
     */
    public function update(Request $request, Projet $projet, ProjetEvent $projetEvent)
    {
        $projetEvent = $this->projetEventService->saveProjetEventFromRequest($request, $projet, $projetEvent);

        if ($errorResponse = self::validateProjetEvent($projetEvent, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($projetEvent);
        $this->em->flush();

        return new JsonResponse([
            "action" => "updated",
        ]);
    }

    /**
     * @Route(
     *      "/{eventId}",
     *      methods={"DELETE"},
     *      name="app_fo_projet_events_delete"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("projetEvent", options={"id" = "eventId"})
     */
    public function delete(Request $request, Projet $projet, ProjetEvent $projetEvent)
    {
        $this->em->remove($projetEvent);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted"
        ]);
    }

    private static function validateProjetEvent(ProjetEvent $projetEvent, ValidatorInterface $validator): ?JsonResponse
    {
        $violations = $validator->validate($projetEvent);

        if (0 === count($violations)) {
            return null;
        }

        return new JsonResponse([
            'message' => join(' ; ', array_map(function (ConstraintViolationInterface $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations))),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    private static function setResponseCollections(array $responseData, Projet $projet, TranslatorInterface $translator): array
    {
        $responseData['collections']['participants'] = $projet->getProjetParticipants()->map(function (ProjetParticipant $participant){
            return ['value' => $participant->getId(), 'label' => " " . $participant->getSocieteUser()->getUser()->getFullname()];
        })->toArray();

        foreach (ProjetEvent::EVENT_TYPES as $type){
            $responseData['collections']['eventTypes'][] = ['value' => $type, 'label' => $translator->trans($type)];
        }

        return $responseData;
    }
}
