<?php

namespace App\Controller\CorpApp\API;

use App\Entity\Projet;
use App\Entity\Evenement;
use App\Exception\RdiException;
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

/**
 * @Route("/api/projet/{projetId}/evenement")
 */
class ProjetEvenementController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private IcsFileGenerator $icsFileGenerator;
    private ProjetEvenementService $projetEvenementService;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        ProjetEvenementService $projetEvenementService,
        IcsFileGenerator $icsFileGenerator
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->projetEvenementService = $projetEvenementService;
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
     * @Route("", methods={"POST"}, name="app_fo_projet_evenements_post")
     *
     * @ParamConverter("projet", options={"id" = "projetId"})
     */
    public function save(Request $request, Projet $projet)
    {
        $evenement = $this->projetEvenementService->saveEvenementFromRequest($request, $projet);

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
     *      name="app_fo_projet_evenements_update"
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
     *      name="app_fo_projet_evenements_delete"
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
     *      name="app_fo_projet_evenements_ics_calendar"
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
