<?php

namespace App\Controller\FO;

use App\Form\TempsPassesType;
use App\Service\CraService;
use App\Service\DateMonthService;
use App\MultiSociete\UserContext;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function saisieTempsEnPourCent(
        Request $request,
        DateTime $month,
        CraService $craService,
        EntityManagerInterface $em,
        UserContext $userContext,
        DateMonthService $dateMonthService
    ) {
        if ($month > new \DateTime()) {
            throw $this->createNotFoundException('Impossible de saisir les temps passés dans le futur.');
        }

        $cra = $craService->loadCraForUser($userContext->getSocieteUser(), $month);
        $form = $this->createForm(TempsPassesType::class, $cra);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cra->setTempsPassesModifiedAt(new \DateTime());

            $em->persist($cra);
            $em->flush();

            $href = $this->generateUrl('app_fo_absences', [
                'year' => $month->format('Y'),
                'month' => $month->format('m'),
            ]);

            $this->addFlash('success', 'Temps passés mis à jour.');
            $this->addFlash(
                'warning',
                '<a href="'.$href.'" class="alert-link">Saisissez vos absences</a> si vous en avez pris ce mois ci.'
            );

            return $this->redirectToRoute('app_fo_temps', [
                'year' => $month->format('Y'),
                'month' => $month->format('m'),
            ]);
        }

        return $this->render('temps/temps_en_pour_cent.html.twig', [
            'mois' => $month,
            'moisEntree' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getDateEntree()),
            'moisSortie' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getDateSortie()),
            'form' => $form->createView(),
            'next' => $dateMonthService->getNextMonth($month),
            'prev' => $dateMonthService->getPrevMonth($month),
            'cra' => $cra,
        ]);
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
            'moisEntree' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getDateEntree()),
            'moisSortie' => $dateMonthService->normalizeOrNull($userContext->getSocieteUser()->getDateSortie()),
            'cra' => $craService->loadCraForUser($userContext->getSocieteUser(), $month),
        ]);
    }
}
