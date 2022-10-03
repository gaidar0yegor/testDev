<?php

namespace App\Controller\CorpApp\API;

use App\DTO\EvenementUpdatesCra;
use App\Entity\Evenement;
use App\Entity\SocieteUser;
use App\Exception\RdiException;
use App\MultiSociete\UserContext;
use App\Notification\Event\EvenementRemovedEvent;
use App\Service\Evenement\EvenementManager\SocieteUserEvenementService;
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
 * @Route("/api/utilisateur/evenement")
 */
class SocieteUserEvenementController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private IcsFileGenerator $icsFileGenerator;
    private SocieteUserEvenementService $societeUserEvenementService;
    private EventDispatcherInterface $dispatcher;
    private UserContext $userContext;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SocieteUserEvenementService $societeUserEvenementService,
        IcsFileGenerator $icsFileGenerator,
        EventDispatcherInterface $dispatcher,
        UserContext $userContext
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->societeUserEvenementService = $societeUserEvenementService;
        $this->icsFileGenerator = $icsFileGenerator;
        $this->dispatcher = $dispatcher;
        $this->userContext = $userContext;
    }

    /**
     * @Route("", methods={"GET"}, name="api_get_users_events_list")
     */
    public function getEvents(Request $request)
    {
        if ($request->query->has('filter_user_event')){
            $filter_user_event = $request->query->get('filter_user_event');
            $societeUsers = $this->em->getRepository(SocieteUser::class)->findBy( array('id' => $filter_user_event['users']), array('id' => 'ASC') );
            $datas = $this->societeUserEvenementService->serializeSocieteUsersEvenements($societeUsers, $filter_user_event['eventTypes']);
        } else {
            $datas = $this->societeUserEvenementService->serializeSocieteUsersEvenements([$this->userContext->getSocieteUser()]);
        }

        return new JsonResponse($datas);
    }

    /**
     * @Route("", methods={"POST"}, name="corp_app_fo_user_evenements_post")
     */
    public function save(Request $request)
    {
        $evenement = $this->societeUserEvenementService->saveEvenementFromRequest($request);

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
     *      "/{eventId}",
     *      methods={"PUT"},
     *      name="corp_app_fo_user_evenements_update"
     * )
     *
     * @ParamConverter("evenement", options={"id" = "eventId"})
     * @throws RdiException
     */
    public function update(Request $request, Evenement $evenement)
    {
        $evenement = $this->societeUserEvenementService->saveEvenementFromRequest($request, null, $evenement);

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
     *      name="corp_app_fo_user_evenements_delete"
     * )
     *
     * @ParamConverter("evenement", options={"id" = "eventId"})
     */
    public function delete(Evenement $evenement)
    {
        if ($evenement->getAutoUpdateCra()){
            $evenementUpdatesCra = (new EvenementUpdatesCra())
                ->setOldEvenement($evenement);
            $this->societeUserEvenementService->updateSocieteUsersCra($evenementUpdatesCra);
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
     *      name="corp_app_fo_user_evenements_ics_calendar"
     * )
     *
     * @ParamConverter("evenement", options={"id" = "eventId"})
     * @throws RdiException
     */
    public function downloadIcsCalendar(Request $request, Evenement $evenement)
    {
        if (!$this->userContext->getSocieteUser()->isInvitedToEvenement($evenement)){
            throw new RdiException("Vous n'êtes pas invités à cet évènement");
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
