<?php

namespace App\Controller\FO;

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
     * name="app_fo_temps"
     * )
     */
    public function saisieTempsEnPourCent(DateTime $month): Response
    {
        if ($month > new \DateTime()) {
            throw $this->createNotFoundException('Impossible de saisir les temps passés dans le futur.');
        }

        return $this->render('temps/temps_en_pour_cent.html.twig');
    }

    /**
     * @Route(
     *      "/absences/{year}/{month}",
     *      requirements={"year"="\d{4}", "month"="\d{2}"},
     *      defaults={"year"=null, "month"=null},
     *      name="app_fo_absences"
     * )
     */
    public function saisieAbsences(
        CraService $craService,
        DateTime $month,
        UserContext $userContext,
        DateMonthService $dateMonthService
    ) {
        return $this->render('temps/absences.html.twig', [
            'next' => $dateMonthService->getNextMonth($month),
            'prev' => $dateMonthService->getPrevMonth($month),
            'mois' => $month,
            'moisEntree' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getLastSocieteUserPeriod()->getDateEntry()),
            'moisSortie' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getLastSocieteUserPeriod()->getDateLeave()),
            'cra' => $craService->loadCraForUser($userContext->getSocieteUser(), $month),
        ]);
    }
}
