<?php

namespace App\Controller\CorpApp\FO;

use App\Service\CraService;
use App\Service\DateMonthService;
use App\MultiSociete\UserContext;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempsController extends AbstractController
{
    /**
     * @Route(
     * "/temps/{year}/{month}",
     * requirements={"year"="\d{4}", "month"="\d{2}"},
     * defaults={"year"=null, "month"=null},
     * name="corp_app_fo_temps"
     * )
     */
    public function saisieTempsEnPourCent(DateTime $month): Response
    {
        if ($month > new \DateTime()) {
            throw $this->createNotFoundException('Impossible de saisir les temps passés dans le futur.');
        }

        return $this->render('corp_app/temps/temps_en_pour_cent.html.twig');
    }

    /**
     * @Route(
     *      "/absences/{year}/{month}",
     *      requirements={"year"="\d{4}", "month"="\d{2}"},
     *      defaults={"year"=null, "month"=null},
     *      name="corp_app_fo_absences"
     * )
     */
    public function saisieAbsences(
        CraService $craService,
        DateTime $month,
        UserContext $userContext,
        DateMonthService $dateMonthService
    ) {
        return $this->render('corp_app/temps/absences.html.twig', [
            'next' => $dateMonthService->getNextMonth($month),
            'prev' => $dateMonthService->getPrevMonth($month),
            'mois' => $month,
            'isUserBelongingToSociete' => $dateMonthService->isUserBelongingToSocieteByDate($userContext->getSocieteUser(),$month),
            'cra' => $craService->loadCraForUser($userContext->getSocieteUser(), $month),
        ]);
    }

    /**
     * @Route("/mon-suivi", name="corp_app_fo_mon_suivi")
     */
    public function monSuivi(UserContext $userContext) {

        return $this->render('corp_app/temps/mon-suivi.html.twig');
    }
}
