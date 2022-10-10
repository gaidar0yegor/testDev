<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\Evenement;
use App\Exception\RdiException;
use App\Notification\Event\EvenementRemovedEvent;
use App\Service\Evenement\EvenementManager\ProjetEvenementService;
use App\Service\Evenement\IcsFileGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/api/projet/{projetId}/evenement")
 */
class ProjetEvenementController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private IcsFileGenerator $icsFileGenerator;
    private EventDispatcherInterface $dispatcher;
    private ProjetEvenementService $projetEvenementService;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        ProjetEvenementService $projetEvenementService,
        EventDispatcherInterface $dispatcher,
        IcsFileGenerator $icsFileGenerator
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->projetEvenementService = $projetEvenementService;
        $this->dispatcher = $dispatcher;
        $this->icsFileGenerator = $icsFileGenerator;
    }

    /**
     * @Route("", methods={"GET"}, name="api_get_projet_events_list" )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function getEvents(Projet $projet)
    {
        $datas = $this->projetEvenementService->serializeProjetEvenements($projet);

        return new JsonResponse($datas);
    }

    /**
     * @Route("", methods={"POST"}, name="corp_app_fo_projet_evenements_post")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function save(Request $request, Projet $projet)
    {
        $evenement = $this->projetEvenementService->saveEvenementFromRequest($request, $projet);

        if ($evenement instanceof JsonResponse){
            return $evenement;
        }

        if ($errorResponse = self::validateEvenement($evenement, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($evenement);
        $this->em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "tid" => $evenement->getId(),
        ]);
    }

    /**
     * @Route(
     *      "/check-overlap",
     *      methods={"POST"},
     *      name="corp_app_fo_projet_evenements_check_overlap"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function checkOverlap(Request $request, Projet $projet)
    {
        if (!$request->request->has('event')){
            return new JsonResponse(null, JsonResponse::HTTP_BAD_GATEWAY);
        }

        $event = json_decode($request->request->get('event'), true);
        $exceptEventId = $request->request->get('id') ? (int)$request->request->get('id') : null;

        $eventStartDate = new \DateTime(date('Y-m-d H:i', strtotime($event['start_date'])));
        $eventEndDate = new \DateTime(date('Y-m-d H:i', strtotime($event['end_date'])));
        $requiredParticipantsIds = explode(',', $event['required_participants_ids']);

        $evenementParticipants = $this->projetEvenementService->checkOverlapEventsParticipants($eventStartDate, $eventEndDate, $requiredParticipantsIds, $exceptEventId);
        $deniedParticipants = [];

        foreach ($evenementParticipants as $evenementParticipant){
            if (array_search($evenementParticipant->getSocieteUser()->getId(), array_column($deniedParticipants, 'id')) === false){
                $deniedParticipants[] = [
                    'id' => $evenementParticipant->getSocieteUser()->getId(),
                    'fullName' => $evenementParticipant->getSocieteUser()->getUser()->getFullnameOrEmail(),
                ];
            }
        }

        if (count($deniedParticipants) > 0){
            return new JsonResponse([
                "action" => "error",
                "context" => [
                    "title" => "overlap_Participants",
                    "data" => $deniedParticipants
                ],
            ]);
        }

        return new JsonResponse([
            "action" => "success",
        ]);
    }

    /**
     * @Route(
     *      "/{eventId}",
     *      methods={"PUT"},
     *      name="corp_app_fo_projet_evenements_update"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("evenement", options={"id" = "eventId"})
     * @throws RdiException
     */
    public function update(Request $request, Projet $projet, Evenement $evenement)
    {
        if ($projet !== $evenement->getProjet()){
            throw new RdiException('Un problème est survenu !!');
        }

        $evenement = $this->projetEvenementService->saveEvenementFromRequest($request, $projet, $evenement);

        if ($evenement instanceof JsonResponse){
            return $evenement;
        }

        if ($errorResponse = self::validateEvenement($evenement, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($evenement);
        $this->em->flush();

        return new JsonResponse([
            "action" => "updated",
        ]);
    }

    /**
     * @Route(
     *      "/{eventId}",
     *      methods={"DELETE"},
     *      name="corp_app_fo_projet_evenements_delete"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("evenement", options={"id" = "eventId"})
     * @throws RdiException
     */
    public function delete(Projet $projet, Evenement $evenement)
    {
        if ($projet !== $evenement->getProjet()){
            throw new RdiException('Un problème est survenu !!');
        }

        $this->dispatcher->dispatch(new EvenementRemovedEvent($evenement));

        $this->em->remove($evenement);
        $this->em->flush();

        return new JsonResponse([
            "action" => "deleted"
        ]);
    }

    /**
     * @Route(
     *      "/ics_calendar/{eventId}",
     *      methods={"GET"},
     *      name="corp_app_fo_projet_evenements_ics_calendar"
     * )
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     * @ParamConverter("evenement", options={"id" = "eventId"})
     * @throws RdiException
     */
    public function downloadIcsCalendar(Request $request, Projet $projet, Evenement $evenement)
    {
        if ($projet !== $evenement->getProjet()){
            throw new RdiException('Un problème est survenu !!');
        }

        $calendar = $this->icsFileGenerator->generateIcsCalendar($evenement);

        header("Content-type: application/ics; method=PUBLISH; charset=UTF-8");
        header("Content-Disposition: attachment; filename=rdi_manager_event.ics");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $calendar;
        exit;
    }

    private static function validateEvenement(Evenement $evenement, ValidatorInterface $validator): ?JsonResponse
    {
        $violations = $validator->validate($evenement);

        if (0 === count($violations)) {
            return null;
        }

        return new JsonResponse([
            'message' => join(' ; ', array_map(function (ConstraintViolationInterface $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations))),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
