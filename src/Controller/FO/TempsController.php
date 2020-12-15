<?php

namespace App\Controller\FO;

use App\Exception\MonthOutOfRangeException;
use App\Form\TempsPassesType;
use App\Service\CraService;
use App\Service\DateMonthService;
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
     * name="app_fo_temps"
     * )
     */
    public function saisieTempsEnPourCent(
        Request $request,
        string $year = null,
        string $month = null,
        CraService $craService,
        EntityManagerInterface $em,
        DateMonthService $dateMonthService
    ) {
        if ($year !== null && $month === null) {
            return $this->redirectToRoute('app_fo_temps');
        }

        try {
            $mois = $dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        if ($mois > new \DateTime()) {
            throw $this->createNotFoundException('Impossible de saisir les temps passés dans le futur.');
        }

        $cra = $craService->loadCraForUser($this->getUser(), $mois);
        $form = $this->createForm(TempsPassesType::class, $cra);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cra->setTempsPassesModifiedAt(new \DateTime());

            $em->persist($cra);
            $em->flush();

            $href = $this->generateUrl('app_fo_absences', [
                'year' => $year,
                'month' => $month,
            ]);

            $this->addFlash('success', 'Temps passés mis à jour.');
            $this->addFlash(
                'warning',
                '<a href="'.$href.'" class="alert-link">Saisissez vos absences</a> si vous en avez pris ce mois ci.'
            );

            return $this->redirectToRoute('app_fo_temps', [
                'year' => $year,
                'month' => $month,
            ]);
        }

        return $this->render('temps/temps_en_pour_cent.html.twig', [
            'mois' => $mois,
            'form' => $form->createView(),
            'next' => $dateMonthService->getNextMonth($mois),
            'prev' => $dateMonthService->getPrevMonth($mois),
            'cra' => $cra,
        ]);
    }

    /**
     * @Route(
     *      "/absences/{year}/{month}",
     *      requirements={"year"="\d{4}", "month"="\d{2}"},
     *      name="app_fo_absences"
     * )
     */
    public function saisieAbsences(
        CraService $craService,
        string $year = null,
        string $month = null,
        DateMonthService $dateMonthService
    ) {
        if ((null === $year) xor (null === $month)) {
            throw $this->createNotFoundException('Year and month must be set.');
        }

        try {
            $date = $dateMonthService->getMonthFromYearAndMonth($year, $month);
        } catch (MonthOutOfRangeException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $this->render('temps/absences.html.twig', [
            'next' => $dateMonthService->getNextMonth($date),
            'prev' => $dateMonthService->getPrevMonth($date),
            'year' => $date->format('Y'),
            'month' => $date->format('m'),
            'cra' => $craService->loadCraForUser($this->getUser(), $date),
        ]);
    }
}
